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
        Schema::create('sbl_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->string('contact_person')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('available_hours')->default('10:00 AM - 08:00 PM');
            $table->text('description')->nullable();
            $table->string('icon')->default('📞');
            $table->string('badge')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbl_contacts');
    }
};
