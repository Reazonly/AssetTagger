<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            // Tambah kolom baru untuk menentukan input yang diperlukan
            // Opsi: 'none', 'merk', 'tipe', 'merk_dan_tipe'
            $table->string('input_type')->default('none')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn('input_type');
        });
    }
};
