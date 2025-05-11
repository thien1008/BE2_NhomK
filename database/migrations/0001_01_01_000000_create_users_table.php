<?php
// database/migrations/2024_05_10_000001_create_users_table.php

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
        Schema::create('users', function (Blueprint $table) {
            $table->id('UserID');
            $table->string('FullName', 100);
            $table->string('Email', 255)->unique();
            $table->string('PasswordHash', 255);
            $table->string('Phone', 15)->unique()->nullable();
            $table->enum('UserType', ['Regular', 'VIP', 'Admin'])->default('Regular');
            $table->string('GoogleID', 255)->unique()->nullable();
            $table->datetime('CreatedAt')->useCurrent();
            $table->rememberToken(); // Laravel's remember_token for "Remember Me" functionality
            $table->timestamps(); // Laravel's created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};