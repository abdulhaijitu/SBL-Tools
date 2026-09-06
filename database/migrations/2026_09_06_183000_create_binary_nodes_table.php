<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('binary_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('member_name');
            $table->string('member_code')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Binary Tree Structure
            $table->foreignId('parent_id')->nullable()->constrained('binary_nodes')->nullOnDelete();
            $table->foreignId('sponsor_id')->nullable()->constrained('binary_nodes')->nullOnDelete();
            $table->enum('position', ['left', 'right'])->nullable(); // Root has null position
            
            // Package & Point Volume (PV / BV)
            $table->string('package_name')->default('National 120k');
            $table->decimal('point_value', 12, 2)->default(100.00); // 100 BV for 120k, 500 BV for 550k
            
            // Tree Counters & Business Volume
            $table->integer('left_count')->default(0);
            $table->integer('right_count')->default(0);
            $table->decimal('left_bv', 14, 2)->default(0.00);
            $table->decimal('right_bv', 14, 2)->default(0.00);
            
            // Binary Pair Matching & Carry Over
            $table->decimal('carry_left', 14, 2)->default(0.00);
            $table->decimal('carry_right', 14, 2)->default(0.00);
            $table->integer('matched_pairs')->default(0);
            
            // Rank and Status
            $table->string('rank_name')->default('Member');
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['parent_id', 'position']);
            $table->index('member_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binary_nodes');
    }
};
