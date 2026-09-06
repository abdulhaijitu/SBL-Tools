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
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('platform')->index(); // Facebook Profile, Facebook Page, Facebook Story, Reel, Messenger, WhatsApp, TikTok, YouTube, Offline, Other
            $table->string('content_type')->nullable(); // Post, Video, Reel, Carousel, Story, Live, Article, Script
            $table->string('topic')->nullable();
            $table->text('caption')->nullable();
            $table->string('creative_path')->nullable();
            $table->dateTime('scheduled_at')->index();
            $table->dateTime('published_at')->nullable();
            $table->string('status')->default('Planned')->index(); // Idea, Planned, Design, Ready, Published, Cancelled
            $table->string('cta')->nullable();
            $table->unsignedInteger('reach')->nullable();
            $table->unsignedInteger('engagement')->nullable();
            $table->unsignedInteger('inbox_count')->nullable();
            $table->unsignedInteger('leads_generated')->nullable();
            $table->unsignedInteger('conversions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};

