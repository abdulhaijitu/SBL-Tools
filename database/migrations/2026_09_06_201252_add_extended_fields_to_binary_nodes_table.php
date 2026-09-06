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
            $table->string('password_plain')->nullable()->after('email');
            $table->string('tpin')->nullable()->default('1234')->after('password_plain');
            $table->integer('left_target_count')->default(0)->after('left_count');
            $table->integer('right_target_count')->default(0)->after('right_count');
            $table->json('contributions')->nullable()->after('point_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('binary_nodes', function (Blueprint $table) {
            $table->dropColumn([
                'password_plain',
                'tpin',
                'left_target_count',
                'right_target_count',
                'contributions',
            ]);
        });
    }
};
