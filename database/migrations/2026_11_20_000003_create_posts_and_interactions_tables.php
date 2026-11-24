<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('title', 200);
                $table->text('body')->nullable();
                $table->string('media_path')->nullable();
                $table->string('media_type', 20)->nullable();
                $table->string('status', 20)->default('published');
                $table->unsignedInteger('views_count')->default(0);
                $table->unsignedInteger('share_count')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('post_likes')) {
            Schema::create('post_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'post_id']);
            });
        }

        if (!Schema::hasTable('article_likes')) {
            Schema::create('article_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'article_id']);
            });
        }

        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->morphs('commentable');
                $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('article_likes');
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('posts');
    }
};
