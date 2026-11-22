<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Backfill is intentionally disabled.
     *
     * CampusEvent now creates only verified SUPER_ADMIN accounts through the
     * seeder and only unverified STUDENT accounts through public registration.
     * Updating every NULL value here would wrongly verify unverified students.
     */
    public function up(): void
    {
        // No data is modified.
    }

    /**
     * This data backfill is intentionally irreversible. Clearing verification
     * flags would lock existing users out and cannot be distinguished from
     * accounts that verified their email manually.
     */
    public function down(): void
    {
        //
    }
};
