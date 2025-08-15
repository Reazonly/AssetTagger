<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Hapus kolom yang tidak lagi digunakan
            $table->dropColumn('requires_merk');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Jika di-rollback, kembalikan kolomnya
            $table->boolean('requires_merk')->default(false);
        });
    }
};
