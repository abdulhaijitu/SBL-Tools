<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // National Package, International Package
            $table->decimal('min_amount', 18, 2);
            $table->decimal('max_amount', 18, 2)->nullable();
            $table->decimal('website_fee', 18, 2)->default(0);
            $table->decimal('weekly_return_percent', 5, 2); // 1.75%, 2.00%
            $table->integer('duration_weeks')->default(100);
            $table->decimal('crowdfunding_limit', 18, 2)->nullable();
            $table->string('lifetime_profit_sharing')->nullable(); // e.g. "5,000 - 20,000 Tk/month"
            $table->json('features')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_plans');
    }
};

