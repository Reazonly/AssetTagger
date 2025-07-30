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
            // Kolom untuk menentukan tipe input: 'detailed' atau 'manual'
            $table->string('spec_input_type')->default('detailed')->after('lcd');
            // Kolom untuk menyimpan spesifikasi jika tipenya 'manual'
            $table->text('spesifikasi_manual')->nullable()->after('spec_input_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['spec_input_type', 'spesifikasi_manual']);
        });
    }
};
