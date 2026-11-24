<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                if (!Schema::hasColumn('articles', 'subtitle')) {
                    $table->string('subtitle', 300)->nullable()->after('title');
                }
                if (!Schema::hasColumn('articles', 'slug')) {
                    $table->string('slug', 220)->nullable()->index()->after('subtitle');
                }
                if (!Schema::hasColumn('articles', 'status')) {
                    $table->string('status', 20)->default('published')->after('image_path');
                }
                if (!Schema::hasColumn('articles', 'views_count')) {
                    $table->unsignedInteger('views_count')->default(0)->after('status');
                }
                if (!Schema::hasColumn('articles', 'share_count')) {
                    $table->unsignedInteger('share_count')->default(0)->after('views_count');
                }
                if (!Schema::hasColumn('articles', 'topic_id')) {
                    $table->foreignId('topic_id')->nullable()->after('category_id')->constrained('topics')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                if (Schema::hasColumn('articles', 'topic_id')) {
                    $table->dropForeign(['topic_id']);
                }
                $cols = ['subtitle', 'slug', 'status', 'views_count', 'share_count', 'topic_id'];
                $existing = array_filter($cols, fn ($c) => Schema::hasColumn('articles', $c));
                if (!empty($existing)) {
                    $table->dropColumn($existing);
                }
            });
        }
    }
};
