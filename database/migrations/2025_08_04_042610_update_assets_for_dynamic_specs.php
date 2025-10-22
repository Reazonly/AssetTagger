<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // 1. Tambah kolom baru
            $table->string('sub_category')->nullable()->after('category_id');
            $table->json('specifications')->nullable()->after('sub_category');

            $table->dropColumn([
                'processor',
                'memory_ram',
                'hdd_ssd',
                'graphics',
                'lcd',
                'spec_input_type',
                'spesifikasi_manual'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Perintah untuk mengembalikan jika migrasi di-rollback
            $table->dropColumn(['sub_category', 'specifications']);

            $table->string('processor')->nullable();
            $table->string('memory_ram')->nullable();
            $table->string('hdd_ssd')->nullable();
            $table->string('graphics')->nullable();
            $table->string('lcd')->nullable();
            $table->string('spec_input_type')->default('detailed');
            $table->text('spesifikasi_manual')->nullable();
        });
    }
};
