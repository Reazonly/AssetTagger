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
            // Cek terlebih dahulu apakah kolomnya belum ada
            if (!Schema::hasColumn('assets', 'tanggal_pembelian')) {
                // Jika belum ada, baru tambahkan kolomnya
                $table->date('tanggal_pembelian')->nullable()->after('thn_pembelian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Cek terlebih dahulu apakah kolomnya ada
            if (Schema::hasColumn('assets', 'tanggal_pembelian')) {
                // Jika ada, baru hapus kolomnya
                $table->dropColumn('tanggal_pembelian');
            }
        });
    }
};
