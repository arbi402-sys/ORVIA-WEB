<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('topics')) {
            Schema::create('topics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
                $table->string('name', 80);
                $table->string('slug', 100)->unique();
                $table->text('description')->nullable();
                $table->string('avatar')->nullable();
                $table->string('banner')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('topic_members')) {
            Schema::create('topic_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['topic_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_members');
        Schema::dropIfExists('topics');
    }
};
