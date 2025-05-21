<?php
// database/migrations/2024_05_10_000005_create_orders_table.php

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('OrderID');
            $table->unsignedBigInteger('UserID');
            $table->decimal('TotalPrice', 18, 2);
            $table->enum('Status', ['Pending', 'Completed', 'Cancelled'])->default('Pending');
            $table->datetime('CreatedAt')->useCurrent();
            $table->timestamps();

            // Khoá ngoại
            $table->foreign('UserID')
                ->references('UserID')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};