<?php

/**
 * Payment confirmation concurrency test.
 *
 * Verifies that many simultaneous confirmations of the same payment issue
 * exactly one ticket and update the inventory exactly once.
 *
 * Run with:
 *   php tests/Concurrency/run_payment.php
 *
 * Expected: all attempts return OK, 1 ticket, sold_count = 1, reserved_count = 0.
 */

use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use App\Services\RegistrationService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

const DATABASE = 'campusevent_concurrency_payment';
const ATTEMPTS = 10;
const PRICE = 25000;

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
    'name' => 'Payment Concurrency Organization',
    'type' => 'BEM',
    'status' => 'ACTIVE',
]);

$event = Event::create([
    'organization_id' => $organization->id,
    'title' => 'Payment Concurrency Event',
    'description' => 'Event for the payment concurrency test.',
    'location' => 'Test Hall',
    'category' => 'Seminar',
    'registration_start' => now()->subHour(),
    'registration_end' => now()->addHour(),
    'event_start' => now()->addDay(),
    'event_end' => now()->addDay()->addHours(2),
    'capacity' => 5,
    'reserved_count' => 0,
    'sold_count' => 0,
    'ticket_price' => PRICE,
    'status' => EventStatus::PUBLISHED,
]);

$student = User::create([
    'name' => 'Payment Student',
    'email' => 'payment.student@campusevent.test',
    'password' => bcrypt('password'),
]);

$registration = $app->make(RegistrationService::class)
    ->register($student, $event)['registration'];

$payment = $registration->payment;

echo 'Starting '.ATTEMPTS." simultaneous confirmations of payment {$payment->id}...\n";

$worker = __DIR__.'/payment_worker.php';
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
        [PHP_BINARY, $worker, (string) $payment->id],
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
$unexpected = [];

foreach ($results as $result) {
    if (str_starts_with($result, 'OK:')) {
        $success++;
    } else {
        $unexpected[] = $result;
    }
}

$event->refresh();
$ticketCount = Ticket::count();
$paymentStatus = Payment::findOrFail($payment->id)->status;
$confirmed = Registration::findOrFail($registration->id)->status;

$failures = [];

if ($success !== ATTEMPTS) {
    $failures[] = 'Expected '.ATTEMPTS." idempotent successes, got {$success}.";
}

if ($unexpected !== []) {
    $failures[] = 'Unexpected worker output: '.implode(' | ', array_slice($unexpected, 0, 5));
}

if ($ticketCount !== 1) {
    $failures[] = "Expected exactly 1 ticket, got {$ticketCount}.";
}

if ($event->sold_count !== 1) {
    $failures[] = "Expected sold_count 1, got {$event->sold_count}.";
}

if ($event->reserved_count !== 0) {
    $failures[] = "Expected reserved_count 0, got {$event->reserved_count}.";
}

if ($paymentStatus !== PaymentStatus::PAID) {
    $failures[] = "Expected payment status PAID, got {$paymentStatus->value}.";
}

if ((int) $event->sold_count + (int) $event->reserved_count > (int) $event->capacity) {
    $failures[] = 'Inventory invariant violated after concurrent payment.';
}

printf("Attempts: %d | OK: %d | Elapsed: %.2fs\n", ATTEMPTS, $success, $elapsed);
printf("Payment: %s | Registration: %s | tickets: %d | sold=%d reserved=%d\n", $paymentStatus->value, $confirmed->value, $ticketCount, $event->sold_count, $event->reserved_count);

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

echo "\nPASS: concurrent payment confirmation issues exactly one ticket.\n";
