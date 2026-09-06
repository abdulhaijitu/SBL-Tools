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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('location')->nullable();
            $table->string('profession_or_business')->nullable();
            $table->foreignId('lead_source_id')->constrained('lead_sources')->cascadeOnDelete();
            $table->string('lead_source_detail')->nullable();
            $table->json('interest_types')->nullable();
            $table->string('lead_tag')->nullable();
            $table->string('stage')->default('new')->index();
            $table->string('temperature')->default('cold')->index();
            $table->integer('score')->default(0);
            $table->boolean('is_manual_score')->default(false);
            $table->string('budget_range')->nullable();
            $table->string('decision_timeline')->nullable();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('next_action_type')->nullable();
            $table->dateTime('next_action_at')->nullable()->index();
            $table->dateTime('last_contact_at')->nullable()->index();
            $table->dateTime('converted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

