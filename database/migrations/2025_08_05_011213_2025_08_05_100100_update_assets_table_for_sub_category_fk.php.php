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
            if (Schema::hasColumn('assets', 'sub_category')) {
                $table->dropColumn('sub_category');
            }

            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('sub_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'sub_category_id')) {
                $table->dropForeign(['sub_category_id']);
                $table->dropColumn('sub_category_id');
            }
            
            $table->string('sub_category')->nullable()->after('category_id');
        });
    }
};