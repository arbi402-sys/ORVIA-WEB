<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->string('verification_status', 30)->default('none')->after('role');
            }
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('verification_status');
            }
            if (!Schema::hasColumn('users', 'institution_access_code')) {
                $table->string('institution_access_code', 64)->nullable()->unique()->after('is_verified');
            }
            if (!Schema::hasColumn('users', 'access_code_used_at')) {
                $table->timestamp('access_code_used_at')->nullable()->after('institution_access_code');
            }
            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('access_code_used_at');
            }
            if (!Schema::hasColumn('users', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('users', 'theme_preference')) {
                $table->string('theme_preference', 20)->default('dark')->after('rejection_reason');
            }
            if (!Schema::hasColumn('users', 'language_preference')) {
                $table->string('language_preference', 10)->default('id')->after('theme_preference');
            }
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('language_preference');
            }
            if (!Schema::hasColumn('users', 'social_instagram')) {
                $table->string('social_instagram')->nullable()->after('two_factor_enabled');
            }
            if (!Schema::hasColumn('users', 'social_whatsapp')) {
                $table->string('social_whatsapp')->nullable()->after('social_instagram');
            }
            if (!Schema::hasColumn('users', 'social_x')) {
                $table->string('social_x')->nullable()->after('social_whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'verified_by')) {
                $table->dropForeign(['verified_by']);
            }
            $columns = [
                'verification_status',
                'is_verified',
                'institution_access_code',
                'access_code_used_at',
                'verified_at',
                'verified_by',
                'rejection_reason',
                'theme_preference',
                'language_preference',
                'two_factor_enabled',
                'social_instagram',
                'social_whatsapp',
                'social_x',
            ];
            $existing = array_filter($columns, fn ($col) => Schema::hasColumn('users', $col));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
