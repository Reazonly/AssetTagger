<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {

            $table->dropColumn('merk_type');
            $table->dropColumn('satuan');

            $table->string('merk')->nullable()->after('nama_barang');
            $table->string('tipe')->nullable()->after('merk');
            

            $table->foreignId('category_id')->nullable()->after('tipe')->constrained('categories')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->after('category_id')->constrained('companies')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('merk_type')->nullable();
            $table->string('satuan')->default('UNIT');

            $table->dropForeign(['category_id']);
            $table->dropForeign(['company_id']);

            $table->dropColumn(['merk', 'tipe', 'category_id', 'company_id']);
        });
    }
};
