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
        Schema::table('binary_nodes', function (Blueprint $table) {
            // Ensure unique placement slot: maximum 1 direct left and 1 direct right child per parent
            $table->unique(['parent_id', 'position'], 'unique_parent_placement_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('binary_nodes', function (Blueprint $table) {
            $table->dropUnique('unique_parent_placement_slot');
        });
    }
};
