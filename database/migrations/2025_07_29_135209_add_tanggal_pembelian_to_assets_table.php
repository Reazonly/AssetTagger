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
            // Menambahkan kolom 'tanggal_pembelian' dengan tipe data DATE
            // Kolom ini bisa kosong (nullable) dan diletakkan setelah kolom 'thn_pembelian'
            $table->date('tanggal_pembelian')->nullable()->after('thn_pembelian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Perintah untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('tanggal_pembelian');
        });
    }
};
