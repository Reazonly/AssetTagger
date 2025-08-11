<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_histories', function (Blueprint $table) {
            // Hapus foreign key dan kolom user_id yang lama
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // Tambahkan foreign key dan kolom asset_user_id yang baru
            $table->foreignId('asset_user_id')->nullable()->after('asset_id')->constrained('asset_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('asset_histories', function (Blueprint $table) {
            // Rollback jika diperlukan
            $table->dropForeign(['asset_user_id']);
            $table->dropColumn('asset_user_id');
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
    }
};
