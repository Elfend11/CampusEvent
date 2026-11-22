<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) env('SEED_SUPER_ADMIN_NAME');
        $email = (string) env('SEED_SUPER_ADMIN_EMAIL');
        $password = (string) env('SEED_SUPER_ADMIN_PASSWORD');

        if ($name === '' || $email === '' || $password === '') {
            throw new RuntimeException(
                'SEED_SUPER_ADMIN_NAME, SEED_SUPER_ADMIN_EMAIL, and SEED_SUPER_ADMIN_PASSWORD must be configured.',
            );
        }

        $admin = User::where('email', $email)->first();

        if ($admin === null) {
            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
        } else {
            $admin->update([
                'name' => $name,
                'email_verified_at' => now(),
            ]);
        }

        UserRoleAssignment::updateOrCreate([
            'user_id' => $admin->id,
            'role' => UserRole::SUPER_ADMIN->value,
        ]);

        $admin->roles()->where('role', '!=', UserRole::SUPER_ADMIN->value)->delete();
    }
}
