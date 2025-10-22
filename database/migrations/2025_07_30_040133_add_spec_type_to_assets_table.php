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
            if (!Schema::hasColumn('assets', 'spec_input_type')) {
                $table->string('spec_input_type')->default('detailed')->after('lcd');
            }
            if (!Schema::hasColumn('assets', 'spesifikasi_manual')) {
                $table->text('spesifikasi_manual')->nullable()->after('spec_input_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'spec_input_type') && Schema::hasColumn('assets', 'spesifikasi_manual')) {
                $table->dropColumn(['spec_input_type', 'spesifikasi_manual']);
            }
        });
    }
};
