<?php

/**
 * Worker process for the payment confirmation concurrency test.
 *
 * Usage: php payment_worker.php <payment_id>
 */

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

require __DIR__.'/../../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$paymentId = (int) ($argv[1] ?? 0);

try {
    $result = $app->make(PaymentService::class)->simulate(
        Payment::findOrFail($paymentId),
        true,
    );

    if ($result['error'] === null) {
        echo 'OK:'.$result['status'];
    } else {
        echo 'REJECTED:'.$result['status'].':'.$result['error'];
    }
} catch (Throwable $exception) {
    echo 'ERROR:'.$exception::class.':'.$exception->getMessage();
}
