<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'registrations:expire';

    protected $description = 'Expire pending registrations whose reservation window has passed.';

    public function handle(RegistrationService $registrations): int
    {
        $expired = 0;

        Registration::query()
            ->expiredReservations()
            ->orderBy('id')
            ->chunkById(100, function ($pending) use ($registrations, &$expired) {
                foreach ($pending as $registration) {
                    if ($registrations->expire($registration)) {
                        $expired++;
                    }
                }
            });

        $this->info("Expired reservations: {$expired}");

        return self::SUCCESS;
    }
}
