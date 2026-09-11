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
        Schema::table('marketing_resources', function (Blueprint $table) {
            $table->string('short_title')->nullable()->after('title');
            $table->string('resource_type')->default('leaflet')->after('category'); // leaflet, presentation, guide, policy, legal_doc, brand_asset, training
            $table->string('thumbnail_url')->nullable()->after('file_url');
            $table->string('version')->default('v1.0')->after('file_size');
            $table->string('source')->nullable()->after('version');
            $table->boolean('is_official')->default(false)->after('source');
            $table->string('verification_status')->default('needs_verification')->after('is_official'); // official_verified, internal_marketing, sbl_provided, needs_verification, verified_document, government_document, needs_review, archived, expired
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->string('verified_by')->nullable()->after('verified_at');
            $table->date('issue_date')->nullable()->after('verified_by');
            $table->date('expiry_date')->nullable()->after('issue_date');
            $table->string('issued_by')->nullable()->after('expiry_date');
            $table->text('tags')->nullable()->after('issued_by');
            $table->string('language')->default('bilingual')->after('tags'); // bangla, english, bilingual
            $table->boolean('is_featured')->default(false)->after('language');
            $table->boolean('is_counseling_toolkit')->default(false)->after('is_featured');
            $table->boolean('is_public')->default(true)->after('is_counseling_toolkit');
            $table->boolean('is_downloadable')->default(true)->after('is_public');
            $table->boolean('is_shareable')->default(true)->after('is_downloadable');
            $table->string('status')->default('current')->after('is_shareable'); // current, review_recommended, expired, archived
            $table->text('notes')->nullable()->after('status');
            $table->unsignedInteger('download_count')->default(0)->after('notes');
            $table->unsignedInteger('view_count')->default(0)->after('download_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_resources', function (Blueprint $table) {
            $table->dropColumn([
                'short_title',
                'resource_type',
                'thumbnail_url',
                'version',
                'source',
                'is_official',
                'verification_status',
                'verified_at',
                'verified_by',
                'issue_date',
                'expiry_date',
                'issued_by',
                'tags',
                'language',
                'is_featured',
                'is_counseling_toolkit',
                'is_public',
                'is_downloadable',
                'is_shareable',
                'status',
                'notes',
                'download_count',
                'view_count',
            ]);
        });
    }
};
