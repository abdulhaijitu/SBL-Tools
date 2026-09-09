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
        Schema::create('marketing_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('Official Leaflets')->index();
            $table->string('file_type')->default('pdf'); // pdf, image, presentation, doc, link
            $table->string('file_url');
            $table->string('file_size')->nullable();
            $table->string('badge')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_resources');
    }
};
