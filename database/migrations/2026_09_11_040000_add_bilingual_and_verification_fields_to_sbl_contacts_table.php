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
        Schema::table('sbl_contacts', function (Blueprint $table) {
            $table->string('department_en')->nullable()->after('department');
            $table->string('department_bn')->nullable()->after('department_en');
            $table->string('service_label_en')->nullable()->after('badge');
            $table->string('service_label_bn')->nullable()->after('service_label_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_bn')->nullable()->after('description_en');
            $table->string('hours_en')->nullable()->after('available_hours');
            $table->string('hours_bn')->nullable()->after('hours_en');
            $table->string('days')->nullable()->after('hours_bn');
            $table->string('category')->default('customer_care')->after('days');
            $table->integer('priority')->default(0)->after('category');
            $table->string('verification_status')->default('needs_review')->after('priority');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->boolean('is_official')->default(false)->after('verified_at');
            $table->boolean('is_active')->default(true)->after('is_official');
            $table->boolean('is_public')->default(true)->after('is_active');
            $table->string('open_time')->nullable()->after('is_public');
            $table->string('close_time')->nullable()->after('open_time');
            $table->boolean('is_24_hours')->default(false)->after('close_time');
            $table->text('notes')->nullable()->after('is_24_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sbl_contacts', function (Blueprint $table) {
            $table->dropColumn([
                'department_en',
                'department_bn',
                'service_label_en',
                'service_label_bn',
                'description_en',
                'description_bn',
                'hours_en',
                'hours_bn',
                'days',
                'category',
                'priority',
                'verification_status',
                'verified_at',
                'is_official',
                'is_active',
                'is_public',
                'open_time',
                'close_time',
                'is_24_hours',
                'notes',
            ]);
        });
    }
};

