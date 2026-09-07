<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop the legacy 2-slot unique index that restricted 1 child per position
        DB::statement('DROP INDEX IF EXISTS unique_parent_placement_slot');

        // 2. Add notes column if it doesn't exist
        if (! Schema::hasColumn('binary_nodes', 'notes')) {
            Schema::table('binary_nodes', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('target_notes');
            });
        }

        // 3. Drop existing non-unique index if present, and create proper unique index for 10-slot placement
        DB::statement('DROP INDEX IF EXISTS idx_parent_branch_slot');
        DB::statement('DROP INDEX IF EXISTS idx_unique_parent_branch_slot');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_parent_branch_slot ON binary_nodes (parent_id, branch, slot_number)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_unique_parent_branch_slot');

        if (Schema::hasColumn('binary_nodes', 'notes')) {
            Schema::table('binary_nodes', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
