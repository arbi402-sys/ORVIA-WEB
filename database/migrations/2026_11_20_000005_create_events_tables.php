<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title', 200);
                $table->string('slug', 220)->unique();
                $table->string('event_type', 60)->default('Pameran');
                $table->text('description');
                $table->string('poster_path')->nullable();
                $table->string('banner_path')->nullable();
                $table->string('location_name', 180)->nullable();
                $table->string('city', 120);
                $table->date('event_date');
                $table->date('registration_deadline')->nullable();
                $table->string('registration_status', 20)->default('open');
                $table->string('external_registration_url')->nullable();
                $table->json('schedules')->nullable();
                $table->json('gallery')->nullable();
                $table->unsignedInteger('share_count')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('event_registrations')) {
            Schema::create('event_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('registered');
                $table->timestamps();
                $table->unique(['event_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
    }
};
