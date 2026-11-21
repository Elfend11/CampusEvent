<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->is($payment->registration->user)
            || $user->hasRole(UserRole::SUPER_ADMIN);
    }

    public function simulate(User $user, Payment $payment): bool
    {
        return $user->is($payment->registration->user);
    }
}
