<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('code_asset')->unique()->comment('Kode Aset Unik');
            $table->string('nama_barang');
            $table->string('merk_type')->nullable();
            $table->string('serial_number')->nullable()->unique();
            
            // Relasi ke pengguna
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Spesifikasi Teknis
            $table->string('processor')->nullable();
            $table->string('memory_ram')->nullable();
            $table->string('hdd_ssd')->nullable();
            $table->string('graphics')->nullable();
            $table->string('lcd')->nullable();
            
            // Informasi Pembelian
            $table->year('thn_pembelian')->nullable();
            $table->string('po_number')->nullable();
            $table->decimal('harga_total', 15, 2)->nullable();
            $table->string('sumber_dana')->nullable();
            $table->string('code_aktiva')->nullable()->comment('Kode Aktiva');

            // Informasi Fisik & Status
            $table->string('kondisi')->nullable();
            $table->string('lokasi')->nullable();
            $table->integer('jumlah')->default(1);
            $table->string('satuan')->default('UNIT');
            
            // Informasi Tambahan
            $table->string('nomor')->nullable()->comment('Nomor BAST');
            $table->text('include_items')->nullable();
            $table->text('peruntukan')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};