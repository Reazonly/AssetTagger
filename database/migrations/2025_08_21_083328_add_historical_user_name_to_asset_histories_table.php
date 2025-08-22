<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_histories', function (Blueprint $table) {
            // Kolom baru untuk menyimpan nama pengguna pada saat itu
            $table->string('historical_user_name')->after('asset_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('asset_histories', function (Blueprint $table) {
            $table->dropColumn('historical_user_name');
        });
    }
};
