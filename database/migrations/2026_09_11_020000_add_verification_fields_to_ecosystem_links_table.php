<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ecosystem_links', function (Blueprint $table) {
            if (!Schema::hasColumn('ecosystem_links', 'type')) {
                $table->string('type')->default('external')->after('category');
            }
            if (!Schema::hasColumn('ecosystem_links', 'is_official')) {
                $table->boolean('is_official')->default(false)->after('type');
            }
            if (!Schema::hasColumn('ecosystem_links', 'verification_status')) {
                $table->string('verification_status')->default('unverified')->after('is_official');
            }
            if (!Schema::hasColumn('ecosystem_links', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verification_status');
            }
            if (!Schema::hasColumn('ecosystem_links', 'verified_by')) {
                $table->string('verified_by')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('ecosystem_links', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('verified_by');
            }
            if (!Schema::hasColumn('ecosystem_links', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('is_featured');
            }
            if (!Schema::hasColumn('ecosystem_links', 'tags')) {
                $table->string('tags')->nullable()->after('is_public');
            }
            if (!Schema::hasColumn('ecosystem_links', 'notes')) {
                $table->text('notes')->nullable()->after('tags');
            }
            if (!Schema::hasColumn('ecosystem_links', 'health_status')) {
                $table->string('health_status')->default('not_checked')->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecosystem_links', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'is_official',
                'verification_status',
                'verified_at',
                'verified_by',
                'is_featured',
                'is_public',
                'tags',
                'notes',
                'health_status',
            ]);
        });
    }
};

