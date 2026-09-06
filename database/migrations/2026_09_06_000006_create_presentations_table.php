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
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('date_time')->index();
            $table->string('type'); // Online, Offline, Group, 1-to-1
            $table->string('topic')->nullable();
            $table->string('interest_focus')->nullable();
            $table->text('questions')->nullable();
            $table->text('objections')->nullable();
            $table->string('outcome')->nullable()->index(); // Hot, Warm, Cold, Converted, Not Interested
            $table->dateTime('next_follow_up_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};

