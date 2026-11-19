<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventOfficer;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Super Admin', 'email' => 'superadmin@campusevent.test', 'role' => UserRole::SUPER_ADMIN],
            ['name' => 'Admin Organisasi', 'email' => 'admin@campusevent.test', 'role' => UserRole::ORGANIZATION_ADMIN],
            ['name' => 'Petugas Check-in', 'email' => 'officer@campusevent.test', 'role' => UserRole::CHECK_IN_OFFICER],
            ['name' => 'Mahasiswa', 'email' => 'student@campusevent.test', 'role' => UserRole::STUDENT],
        ];

        $users = [];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => 'password'],
            );

            UserRoleAssignment::firstOrCreate([
                'user_id' => $user->id,
                'role' => $account['role']->value,
            ]);

            $users[$account['role']->value] = $user;
        }

        $organization = Organization::firstOrCreate(
            ['name' => 'BEM Universitas Contoh'],
            ['type' => 'BEM', 'status' => Organization::STATUS_ACTIVE],
        );

        OrganizationMember::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $users[UserRole::ORGANIZATION_ADMIN->value]->id,
            ],
            ['role' => 'ADMIN'],
        );

        $event = Event::firstOrCreate(
            ['title' => 'Seminar Teknologi Kampus'],
            [
                'organization_id' => $organization->id,
                'description' => 'Seminar teknologi untuk mahasiswa.',
                'location' => 'Auditorium Kampus',
                'category' => 'Seminar',
                'registration_start' => now()->subDay(),
                'registration_end' => now()->addDays(7),
                'event_start' => now()->addDays(10),
                'event_end' => now()->addDays(10)->addHours(4),
                'capacity' => 100,
                'reserved_count' => 0,
                'sold_count' => 0,
                'ticket_price' => 0,
                'status' => EventStatus::PUBLISHED,
            ],
        );

        EventOfficer::firstOrCreate([
            'event_id' => $event->id,
            'user_id' => $users[UserRole::CHECK_IN_OFFICER->value]->id,
        ]);
    }
}
