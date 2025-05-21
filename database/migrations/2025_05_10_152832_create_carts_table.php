<?php
// database/migrations/2024_05_10_000004_create_carts_table.php

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
        Schema::create('cart', function (Blueprint $table) {
            $table->id('CartID');
            $table->unsignedBigInteger('UserID');
            $table->unsignedBigInteger('ProductID');
            $table->integer('Quantity')->check('Quantity > 0');
            $table->datetime('AddedAt')->useCurrent();
            $table->timestamps();

            // Khoá ngoại
            $table->foreign('UserID')
                ->references('UserID')
                ->on('users')
                ->onDelete('cascade');
                
            $table->foreign('ProductID')
                ->references('ProductID')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
    }
};