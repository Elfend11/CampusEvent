<?php

/**
 * Check-in concurrency test.
 *
 * Verifies that concurrent scans of the same QR token succeed exactly once.
 *
 * Run with:
 *   php tests/Concurrency/run_checkin.php
 *
 * Expected: 1 successful check-in, 19 rejected, 1 attendance row.
 */

use App\Enums\EventStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\RegistrationService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

const DATABASE = 'campusevent_concurrency_checkin';
const ATTEMPTS = 20;
const CAPACITY = 5;

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
    'name' => 'Check-in Concurrency Organization',
    'type' => 'BEM',
    'status' => 'ACTIVE',
]);

$event = Event::create([
    'organization_id' => $organization->id,
    'title' => 'Check-in Concurrency Event',
    'description' => 'Event for the check-in concurrency test.',
    'location' => 'Test Hall',
    'category' => 'Seminar',
    'registration_start' => now()->subHour(),
    'registration_end' => now()->addHour(),
    'event_start' => now()->addDay(),
    'event_end' => now()->addDay()->addHours(2),
    'capacity' => CAPACITY,
    'reserved_count' => 0,
    'sold_count' => 0,
    'ticket_price' => 0,
    'status' => EventStatus::PUBLISHED,
]);

$student = User::create([
    'name' => 'Check-in Student',
    'email' => 'checkin.student@campusevent.test',
    'password' => bcrypt('password'),
]);

$officer = User::create([
    'name' => 'Check-in Officer',
    'email' => 'checkin.officer@campusevent.test',
    'password' => bcrypt('password'),
]);

UserRoleAssignment::create(['user_id' => $officer->id, 'role' => UserRole::CHECK_IN_OFFICER->value]);
$event->officers()->attach($officer->id);

$registration = $app->make(RegistrationService::class)
    ->register($student, $event);

$token = $registration['ticket_token'];
$ticketId = $registration['registration']->ticket->id;

echo 'Starting '.ATTEMPTS." simultaneous check-ins for the same token...\n";

$worker = __DIR__.'/checkin_worker.php';
$environment = getenv();
$environment['DB_DATABASE'] = DATABASE;
$environment['APP_ENV'] = 'local';

$startedAt = microtime(true);
$processes = [];

for ($i = 0; $i < ATTEMPTS; $i++) {
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open(
        [PHP_BINARY, $worker, $token, (string) $officer->id],
        $descriptors,
        $pipes,
        __DIR__,
        $environment,
    );

    if (! is_resource($process)) {
        fwrite(STDERR, "Failed to start worker {$i}\n");
        exit(1);
    }

    $processes[$i] = ['process' => $process, 'pipes' => $pipes];
}

$results = [];

foreach ($processes as $i => $workerProcess) {
    $stdout = stream_get_contents($workerProcess['pipes'][1]);
    $stderr = stream_get_contents($workerProcess['pipes'][2]);

    fclose($workerProcess['pipes'][0]);
    fclose($workerProcess['pipes'][1]);
    fclose($workerProcess['pipes'][2]);
    proc_close($workerProcess['process']);

    $results[$i] = trim($stdout) !== '' ? trim($stdout) : 'EMPTY:'.trim($stderr);
}

$elapsed = microtime(true) - $startedAt;

$success = 0;
$rejected = 0;
$unexpected = [];

foreach ($results as $result) {
    if (str_starts_with($result, 'OK:')) {
        $success++;
    } elseif (str_starts_with($result, 'REJECTED:409:')) {
        $rejected++;
    } else {
        $unexpected[] = $result;
    }
}

$attendanceCount = DB::table('attendances')->where('ticket_id', $ticketId)->count();
$ticketStatus = Ticket::findOrFail($ticketId)->status;

$failures = [];

if ($success !== 1) {
    $failures[] = "Expected exactly 1 successful check-in, got {$success}.";
}

if ($rejected !== ATTEMPTS - 1) {
    $failures[] = 'Expected '.(ATTEMPTS - 1)." rejected check-ins, got {$rejected}.";
}

if ($unexpected !== []) {
    $failures[] = 'Unexpected worker output: '.implode(' | ', array_slice($unexpected, 0, 5));
}

if ($attendanceCount !== 1) {
    $failures[] = "Expected 1 attendance row, got {$attendanceCount}.";
}

if ($ticketStatus !== TicketStatus::USED) {
    $failures[] = "Expected ticket status USED, got {$ticketStatus->value}.";
}

printf("Attempts: %d | Success: %d | Rejected (409): %d | Elapsed: %.2fs\n", ATTEMPTS, $success, $rejected, $elapsed);
printf("Ticket status: %s | attendance rows: %d\n", $ticketStatus->value, $attendanceCount);

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

echo "\nPASS: concurrent check-in records attendance exactly once.\n";
