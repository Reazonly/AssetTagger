<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'admin', 'editor'
            $table->string('display_name')->nullable(); // e.g., 'Administrator', 'Content Editor'
            $table->timestamps();
        });

        
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'create-asset', 'delete-user'
            $table->string('display_name')->nullable(); // e.g., 'Create New Asset', 'Delete a User'
            $table->timestamps();
        });

        
        Schema::create('role_user', function (Blueprint $table) {
            $table->primary(['user_id', 'role_id']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
        });

        
        Schema::create('permission_role', function (Blueprint $table) {
            $table->primary(['permission_id', 'role_id']);
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};