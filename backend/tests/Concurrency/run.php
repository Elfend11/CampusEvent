<?php

/**
 * Registration concurrency test.
 *
 * Verifies the reservation invariant under 100 simultaneous registration
 * attempts for an event with only 5 available tickets.
 *
 * Two scenarios are executed:
 * - paid event: 5 tickets are reserved (reserved_count = 5, sold_count = 0);
 * - free event: 5 tickets are sold immediately (sold_count = 5, tickets = 5).
 *
 * Run with:
 *   php tests/Concurrency/run.php
 *
 * Expected for both: 5 successful registrations, 95 rejected requests,
 * 0 overselling.
 */

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Ticket;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

const DATABASE = 'campusevent_concurrency';
const ATTEMPTS = 100;
const CAPACITY = 5;
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

/**
 * @return array{event: Event, userIds: list<int>}
 */
function prepareScenario(bool $paid): array
{
    echo 'Preparing database and fixtures ('.($paid ? 'paid' : 'free')." event)...\n";

    Artisan::call('migrate:fresh', ['--force' => true]);

    $organization = Organization::create([
        'name' => 'Concurrency Test Organization',
        'type' => 'BEM',
        'status' => 'ACTIVE',
    ]);

    $event = Event::create([
        'organization_id' => $organization->id,
        'title' => 'Concurrency Test Event',
        'description' => 'Event for the concurrency test.',
        'location' => 'Test Hall',
        'category' => 'Seminar',
        'registration_start' => now()->subHour(),
        'registration_end' => now()->addHour(),
        'event_start' => now()->addDay(),
        'event_end' => now()->addDay()->addHours(2),
        'capacity' => CAPACITY,
        'reserved_count' => 0,
        'sold_count' => 0,
        'ticket_price' => $paid ? PRICE : 0,
        'status' => EventStatus::PUBLISHED,
    ]);

    $userIds = [];
    $passwordHash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 4]);
    $now = now();

    for ($i = 1; $i <= ATTEMPTS; $i++) {
        $userIds[] = DB::table('users')->insertGetId([
            'name' => "Concurrency Student {$i}",
            'email' => "concurrency{$i}@campusevent.test",
            'password' => $passwordHash,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    return ['event' => $event, 'userIds' => $userIds];
}

/**
 * @param  list<int>  $userIds
 * @return list<string>
 */
function runWorkers(int $eventId, array $userIds): array
{
    $worker = __DIR__.'/registration_worker.php';
    $environment = getenv();
    $environment['DB_DATABASE'] = DATABASE;
    $environment['APP_ENV'] = 'local';

    $processes = [];

    foreach ($userIds as $i => $userId) {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            [PHP_BINARY, $worker, (string) $eventId, (string) $userId],
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

    return $results;
}

/**
 * @return list<string>
 */
function verifyScenario(Event $event, array $results, bool $paid): array
{
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

    $event->refresh();

    $registrations = Registration::count();
    $tickets = Ticket::count();
    $payments = Payment::count();

    $failures = [];

    if ($success !== CAPACITY) {
        $failures[] = 'Expected '.CAPACITY." successful registrations, got {$success}.";
    }

    if ($rejected !== ATTEMPTS - CAPACITY) {
        $failures[] = 'Expected '.(ATTEMPTS - CAPACITY)." rejected requests, got {$rejected}.";
    }

    if ($unexpected !== []) {
        $failures[] = 'Unexpected worker output: '.implode(' | ', array_slice($unexpected, 0, 5));
    }

    if ($registrations !== CAPACITY) {
        $failures[] = 'Expected '.CAPACITY." registration rows, got {$registrations}.";
    }

    if ($paid) {
        if ($event->reserved_count !== CAPACITY) {
            $failures[] = 'Expected reserved_count '.CAPACITY.", got {$event->reserved_count}.";
        }

        if ($event->sold_count !== 0) {
            $failures[] = "Expected sold_count 0, got {$event->sold_count}.";
        }

        if ($tickets !== 0) {
            $failures[] = "Expected no tickets before payment, got {$tickets}.";
        }

        if ($payments !== CAPACITY) {
            $failures[] = 'Expected '.CAPACITY." pending payments, got {$payments}.";
        }
    } else {
        if ($event->sold_count !== CAPACITY) {
            $failures[] = 'Expected sold_count '.CAPACITY.", got {$event->sold_count}.";
        }

        if ($event->reserved_count !== 0) {
            $failures[] = "Expected reserved_count 0, got {$event->reserved_count}.";
        }

        if ($tickets !== CAPACITY) {
            $failures[] = 'Expected '.CAPACITY." tickets, got {$tickets}.";
        }

        if ($payments !== 0) {
            $failures[] = "Expected no payments for a free event, got {$payments}.";
        }
    }

    if ($event->sold_count + $event->reserved_count > $event->capacity) {
        $failures[] = 'Overselling detected: sold + reserved exceeds capacity.';
    }

    if ($event->sold_count < 0 || $event->reserved_count < 0) {
        $failures[] = 'Negative inventory detected.';
    }

    printf(
        "Mode: %s | Attempts: %d | Success: %d | Rejected (409): %d\n",
        $paid ? 'paid' : 'free',
        ATTEMPTS,
        $success,
        $rejected,
    );
    printf(
        "Event state: sold_count=%d reserved_count=%d capacity=%d | registrations=%d tickets=%d payments=%d\n",
        $event->sold_count,
        $event->reserved_count,
        $event->capacity,
        $registrations,
        $tickets,
        $payments,
    );

    return $failures;
}

$allFailures = [];

foreach ([true, false] as $paid) {
    ['event' => $event, 'userIds' => $userIds] = prepareScenario($paid);

    echo 'Starting '.ATTEMPTS.' simultaneous registration attempts for '.CAPACITY.' tickets...'."\n";

    $startedAt = microtime(true);
    $results = runWorkers($event->id, $userIds);
    $elapsed = microtime(true) - $startedAt;

    printf("Elapsed: %.2fs\n", $elapsed);

    foreach (verifyScenario($event, $results, $paid) as $failure) {
        $allFailures[] = ($paid ? '[paid] ' : '[free] ').$failure;
    }
}

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

if ($allFailures !== []) {
    fwrite(STDERR, "\nFAILED\n");
    foreach ($allFailures as $failure) {
        fwrite(STDERR, " - {$failure}\n");
    }

    exit(1);
}

echo "\nPASS: no overselling under concurrent registration (paid and free).\n";
