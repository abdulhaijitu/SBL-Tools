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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->index(); // Call, Messenger, WhatsApp, Follow-up, Presentation, Meeting, Content, Product Follow-up, Payment Follow-up, Member Support, Training, Other
            $table->foreignId('related_lead_id')->nullable()->constrained('leads')->cascadeOnDelete();
            $table->unsignedBigInteger('related_customer_id')->nullable();
            $table->unsignedBigInteger('related_member_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('due_at')->index();
            $table->string('priority')->default('Medium')->index(); // High, Medium, Low
            $table->string('status')->default('Pending')->index(); // Pending, In Progress, Completed, Cancelled
            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('outcome')->nullable();
            $table->string('next_action')->nullable();
            $table->dateTime('next_action_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

