<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_code_counters', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->unique(); // cth: JHG/ELEC/2025
            $table->integer('last_number');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_code_counters');
    }
};