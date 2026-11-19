<?php

/**
 * Worker process for the registration concurrency test.
 *
 * Usage: php registration_worker.php <event_id> <user_id>
 */

use App\Models\Event;
use App\Models\User;
use App\Services\RegistrationService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$eventId = (int) ($argv[1] ?? 0);
$userId = (int) ($argv[2] ?? 0);

try {
    $result = $app->make(RegistrationService::class)->register(
        User::findOrFail($userId),
        Event::findOrFail($eventId),
    );

    echo 'OK:'.$result['registration']->status->value;
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    echo 'REJECTED:'.$exception->getStatusCode().':'.$exception->getMessage();
} catch (\Throwable $exception) {
    echo 'ERROR:'.$exception::class.':'.$exception->getMessage();
}
