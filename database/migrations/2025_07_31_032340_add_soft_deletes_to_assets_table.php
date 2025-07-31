<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Menambahkan kolom 'deleted_at' ke tabel 'assets'
        Schema::table('assets', function (Blueprint $table) {
            $table->softDeletes(); // Ini akan membuat kolom 'deleted_at'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Menghapus kolom 'deleted_at' jika migration di-rollback
        Schema::table('assets', function (Blueprint $table) {
            $table->dropSoftDeletes(); // [FIX] Menambahkan titik koma yang hilang
        });
    }
};