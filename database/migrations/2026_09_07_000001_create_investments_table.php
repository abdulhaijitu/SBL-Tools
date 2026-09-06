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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('binary_node_id')->constrained('binary_nodes')->cascadeOnDelete();
            $table->foreignId('investment_plan_id')->nullable()->constrained('investment_plans')->nullOnDelete();
            $table->string('plan_name')->nullable();
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->decimal('point_value', 12, 2)->default(0.00); // BV / PV
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->date('investment_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['binary_node_id', 'status']);
        });

        // Migrate existing contributions from binary_nodes to investments
        $nodes = \App\Models\BinaryNode::all();
        foreach ($nodes as $node) {
            $contribs = $node->contributions ?: [];
            if (is_string($contribs)) {
                $contribs = json_decode($contribs, true) ?: [];
            }
            if (!empty($contribs) && is_array($contribs)) {
                foreach ($contribs as $c) {
                    \Illuminate\Support\Facades\DB::table('investments')->insert([
                        'binary_node_id' => $node->id,
                        'plan_name' => $c['note'] ?? $node->package_name ?? 'Initial Package',
                        'amount' => (float)($c['amount'] ?? $node->point_value ?? 0),
                        'point_value' => (float)($c['amount'] ?? $node->point_value ?? 0),
                        'status' => 'active',
                        'investment_date' => $c['date'] ?? ($node->created_at ? $node->created_at->toDateString() : now()->toDateString()),
                        'note' => $c['note'] ?? 'Migrated from contributions',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } elseif ((float)$node->point_value > 0) {
                \Illuminate\Support\Facades\DB::table('investments')->insert([
                    'binary_node_id' => $node->id,
                    'plan_name' => $node->package_name ?? 'Initial Package',
                    'amount' => (float)$node->point_value,
                    'point_value' => (float)$node->point_value,
                    'status' => 'active',
                    'investment_date' => $node->created_at ? $node->created_at->toDateString() : now()->toDateString(),
                    'note' => 'Initial Investment',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
