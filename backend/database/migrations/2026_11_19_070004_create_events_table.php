<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->string('category');
            $table->dateTime('registration_start');
            $table->dateTime('registration_end');
            $table->dateTime('event_start');
            $table->dateTime('event_end');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('reserved_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->decimal('ticket_price', 12, 2)->default(0);
            $table->string('status')->default('DRAFT');
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
