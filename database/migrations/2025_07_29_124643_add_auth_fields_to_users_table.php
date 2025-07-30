<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom email setelah nama_pengguna
            $table->string('email')->unique()->after('nama_pengguna')->nullable();
            // Tambahkan kolom password
            $table->string('password')->after('email')->nullable();
            // Tambahkan remember token untuk fitur "Ingat Saya"
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email', 'password', 'remember_token']);
        });
    }
};