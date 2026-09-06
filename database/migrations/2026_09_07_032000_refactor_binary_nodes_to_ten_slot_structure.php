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
        Schema::table('binary_nodes', function (Blueprint $table) {
            $table->foreignId('tree_owner_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->string('branch', 10)->nullable()->after('sponsor_name'); // 'LEFT' or 'RIGHT'
            $table->unsignedTinyInteger('slot_number')->default(1)->after('branch'); // 1, 2, 3, 4, 5
            $table->boolean('is_target')->default(false)->after('is_active');
            $table->date('target_date')->nullable()->after('is_target');
            $table->text('target_notes')->nullable()->after('target_date');
        });

        // Migrate existing rows: position 'left' -> branch 'LEFT', slot_number 1; position 'right' -> branch 'RIGHT', slot_number 1
        DB::table('binary_nodes')->where('position', 'left')->update([
            'branch' => 'LEFT',
            'slot_number' => 1,
        ]);

        DB::table('binary_nodes')->where('position', 'right')->update([
            'branch' => 'RIGHT',
            'slot_number' => 1,
        ]);

        // Default tree_owner_id to user_id or first user
        $firstUserId = DB::table('users')->value('id') ?? 1;
        DB::table('binary_nodes')->whereNull('tree_owner_id')->update([
            'tree_owner_id' => DB::raw("COALESCE(user_id, {$firstUserId})"),
        ]);

        // Create index on parent_id, branch, slot_number
        Schema::table('binary_nodes', function (Blueprint $table) {
            $table->index(['parent_id', 'branch', 'slot_number'], 'idx_parent_branch_slot');
            $table->index('tree_owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('binary_nodes', function (Blueprint $table) {
            $table->dropIndex('idx_parent_branch_slot');
            $table->dropIndex(['tree_owner_id']);
            $table->dropColumn([
                'tree_owner_id',
                'branch',
                'slot_number',
                'is_target',
                'target_date',
                'target_notes',
            ]);
        });
    }
};
