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
        Schema::table('assets', function (Blueprint $table) {
            // Perintah ini akan menghapus index unique dari kolom serial_number
            $table->dropUnique('assets_assets_serial_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // (Opsional) Perintah ini untuk mengembalikan jika terjadi kesalahan
            $table->unique('serial_number');
        });
    }
};