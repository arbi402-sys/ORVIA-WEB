<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->string('type', 20)->default('direct'); // direct, group, community
                $table->string('title', 120)->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('last_message_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('conversation_participants')) {
            Schema::create('conversation_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('last_read_at')->nullable();
                $table->timestamps();
                $table->unique(['conversation_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->string('attachment_path')->nullable();
                $table->foreignId('shared_event_id')->nullable()->constrained('events')->nullOnDelete();
                $table->foreignId('shared_collaboration_id')->nullable()->constrained('collaborations')->nullOnDelete();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
