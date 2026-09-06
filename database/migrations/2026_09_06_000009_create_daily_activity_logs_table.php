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
        Schema::create('daily_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->unsignedInteger('calls')->default(0);
            $table->unsignedInteger('messenger_contacts')->default(0);
            $table->unsignedInteger('whatsapp_contacts')->default(0);
            $table->unsignedInteger('new_leads')->default(0);
            $table->unsignedInteger('follow_ups')->default(0);
            $table->unsignedInteger('presentations')->default(0);
            $table->unsignedInteger('meetings')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->unsignedInteger('product_sales_count')->default(0);
            $table->decimal('received_income', 18, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activity_logs');
    }
};

