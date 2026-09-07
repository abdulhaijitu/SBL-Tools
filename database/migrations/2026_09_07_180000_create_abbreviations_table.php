<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::create('abbreviations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('category');
            $table->string('category_slug');
            $table->text('meaning_bn');
            $table->text('description_bn');
            $table->string('icon', 20)->default('📖');
            $table->string('tag', 100)->default('');
            $table->timestamps();
        });
        foreach (config('abbreviations') as $term) {
            DB::table('abbreviations')->insert($term + ['created_at' => now(), 'updated_at' => now()]);
        }
    }
    public function down(): void { Schema::dropIfExists('abbreviations'); }
};
