<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('collaborations')) {
            Schema::create('collaborations', function (Blueprint $table) {
                $table->id();
                $table->morphs('collaboratable');
                $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('collaborator_id')->constrained('users')->cascadeOnDelete();
                $table->string('status', 20)->default('pending'); // pending, accepted, declined
                $table->string('role_label', 60)->default('Kolaborator');
                $table->timestamps();

                $table->unique(
                    ['collaboratable_type', 'collaboratable_id', 'collaborator_id'],
                    'collab_unique_target_user'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborations');
    }
};
