<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus kolom 'role' yang lama
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        // Jika di-rollback, kembalikan kolom 'role'
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'viewer', 'user', 'editor'])->default('viewer')->after('password');
        });
    }   
};