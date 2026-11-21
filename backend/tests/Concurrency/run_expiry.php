<?php

/**
 * Concurrent reservation expiration test.
 *
 * Verifies that several expiration processes running at the same time release
 * each reservation's quota exactly once and never produce negative inventory.
 *
 * Run with:
 *   php tests/Concurrency/run_expiry.php
 */

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

const DATABASE = 'campusevent_concurrency_expiry';
const PROCESSES = 10;
const RESERVATIONS = 5;

putenv('DB_DATABASE='.DATABASE);

require __DIR__.'/../../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$host = env('DB_HOST', '127.0.0.1');
$port = env('DB_PORT', '3306');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

$adminPdo = new PDO(
    "mysql:host={$host};port={$port}",
    $username,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$adminPdo->exec('DROP DATABASE IF EXISTS `'.DATABASE.'`');
$adminPdo->exec('CREATE DATABASE `'.DATABASE.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$adminPdo = null;

echo "Preparing database and fixtures...\n";

Artisan::call('migrate:fresh', ['--force' => true]);

$organization = Organization::create([
    'name' => 'Expiry Concurrency Organization',
    'type' => 'BEM',
    'status' => 'ACTIVE',
]);

$event = Event::create([
    'organization_id' => $organization->id,
    'title' => 'Expiry Concurrency Event',
    'description' => 'Event for the expiry concurrency test.',
    'location' => 'Test Hall',
    'category' => 'Seminar',
    'registration_start' => now()->subHour(),
    'registration_end' => now()->addHour(),
    'event_start' => now()->addDay(),
    'event_end' => now()->addDay()->addHours(2),
    'capacity' => 10,
    'reserved_count' => RESERVATIONS,
    'sold_count' => 0,
    'ticket_price' => 25000,
    'status' => EventStatus::PUBLISHED,
]);

for ($i = 1; $i <= RESERVATIONS; $i++) {
    $student = User::create([
        'name' => "Expiry Student {$i}",
        'email' => "expiry{$i}@campusevent.test",
        'password' => bcrypt('password'),
    ]);

    $registration = Registration::create([
        'user_id' => $student->id,
        'event_id' => $event->id,
        'status' => RegistrationStatus::PENDING,
        'expires_at' => now()->subMinute(),
    ]);

    $registration->payment()->create([
        'amount' => 25000,
        'status' => 'PENDING',
    ]);
}

echo 'Starting '.PROCESSES." simultaneous expiration processes...\n";

$environment = getenv();
$environment['DB_DATABASE'] = DATABASE;
$environment['APP_ENV'] = 'local';
$artisan = dirname(__DIR__, 2).'/artisan';

$startedAt = microtime(true);
$processes = [];

for ($i = 0; $i < PROCESSES; $i++) {
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open(
        [PHP_BINARY, $artisan, 'registrations:expire'],
        $descriptors,
        $pipes,
        dirname(__DIR__, 2),
        $environment,
    );

    if (! is_resource($process)) {
        fwrite(STDERR, "Failed to start process {$i}\n");
        exit(1);
    }

    $processes[$i] = ['process' => $process, 'pipes' => $pipes];
}

$outputs = [];
$exitCodes = [];

foreach ($processes as $i => $workerProcess) {
    $stdout = stream_get_contents($workerProcess['pipes'][1]);
    $stderr = stream_get_contents($workerProcess['pipes'][2]);

    fclose($workerProcess['pipes'][0]);
    fclose($workerProcess['pipes'][1]);
    fclose($workerProcess['pipes'][2]);

    $exitCodes[$i] = proc_close($workerProcess['process']);
    $outputs[$i] = trim($stdout).trim($stderr);
}

$elapsed = microtime(true) - $startedAt;

$event->refresh();
$expired = Registration::where('status', RegistrationStatus::EXPIRED->value)->count();
$tickets = DB::table('tickets')->count();

$failures = [];

foreach ($exitCodes as $i => $exitCode) {
    if ($exitCode !== 0) {
        $failures[] = "Process {$i} exited with code {$exitCode}: ".$outputs[$i];
    }
}

if ($expired !== RESERVATIONS) {
    $failures[] = 'Expected '.RESERVATIONS." expired registrations, got {$expired}.";
}

if ((int) $event->reserved_count !== 0) {
    $failures[] = "Expected reserved_count 0, got {$event->reserved_count}.";
}

if ((int) $event->reserved_count < 0) {
    $failures[] = 'reserved_count became negative.';
}

if ((int) $event->sold_count !== 0) {
    $failures[] = "Expected sold_count 0, got {$event->sold_count}.";
}

if ($tickets !== 0) {
    $failures[] = "Expected no tickets, got {$tickets}.";
}

$totalReleased = array_sum(array_map(
    fn (string $output) => (int) (preg_match('/Expired reservations:\s*(\d+)/', $output, $matches) ? $matches[1] : 0),
    $outputs,
));

if ($totalReleased !== RESERVATIONS) {
    $failures[] = 'Expected '.RESERVATIONS." total releases across all processes, got {$totalReleased}.";
}

printf("Processes: %d | Elapsed: %.2fs\n", PROCESSES, $elapsed);
printf("Expired: %d | total released: %d | reserved=%d sold=%d tickets=%d\n", $expired, $totalReleased, $event->reserved_count, $event->sold_count, $tickets);

DB::disconnect();

try {
    $cleanupPdo = new PDO(
        "mysql:host={$host};port={$port}",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );
    $cleanupPdo->exec('DROP DATABASE IF EXISTS `'.DATABASE.'`');
} catch (Throwable $exception) {
    fwrite(STDERR, 'Cleanup warning: '.$exception->getMessage()."\n");
}

if ($failures !== []) {
    fwrite(STDERR, "\nFAILED\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, " - {$failure}\n");
    }

    exit(1);
}

echo "\nPASS: concurrent expiration releases quota exactly once.\n";
