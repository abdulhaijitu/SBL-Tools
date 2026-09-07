<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('binary_nodes', function (Blueprint $table) {
            $table->text('password_plain')->nullable()->change();
            $table->text('tpin')->nullable()->change();
        });
        DB::transaction(function () {
            DB::table('binary_nodes')->orderBy('id')->chunkById(100, function ($nodes) {
                foreach ($nodes as $node) {
                    $changes = [];
                    foreach (['password_plain', 'tpin'] as $field) {
                        $value = $node->$field;
                        if ($value === null || $value === '') continue;
                        try { Crypt::decryptString($value); }
                        catch (\Illuminate\Contracts\Encryption\DecryptException) { $changes[$field] = Crypt::encryptString($value); }
                    }
                    if ($changes) DB::table('binary_nodes')->where('id', $node->id)->update($changes);
                }
            });
        });
    }

    public function down(): void
    {
        // Deliberately retain ciphertext on rollback; never restore plaintext storage.
    }
};
