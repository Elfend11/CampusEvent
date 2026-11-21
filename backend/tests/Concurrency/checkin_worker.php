<?php

/**
 * Worker process for the check-in concurrency test.
 *
 * Usage: php checkin_worker.php <token> <officer_id>
 */

use App\Models\User;
use App\Services\CheckInService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

require __DIR__.'/../../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$token = (string) ($argv[1] ?? '');
$officerId = (int) ($argv[2] ?? 0);

try {
    $result = $app->make(CheckInService::class)->checkIn(
        User::findOrFail($officerId),
        $token,
    );

    if ($result['error'] === null) {
        echo 'OK:'.$result['status'];
    } else {
        echo 'REJECTED:'.$result['status'].':'.$result['error'];
    }
} catch (Throwable $exception) {
    echo 'ERROR:'.$exception::class.':'.$exception->getMessage();
}
