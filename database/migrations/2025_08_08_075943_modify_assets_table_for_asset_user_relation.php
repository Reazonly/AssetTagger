<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->foreignId('asset_user_id')->nullable()->after('serial_number')->constrained('asset_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Rollback jika diperlukan
            $table->dropForeign(['asset_user_id']);
            $table->dropColumn('asset_user_id');
            
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }
};