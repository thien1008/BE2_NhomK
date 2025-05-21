<?php
// database/migrations/2024_05_10_000007_create_product_discounts_table.php

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
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id('DiscountID');
            $table->unsignedBigInteger('ProductID');
            $table->decimal('DiscountPercentage', 5, 2)->check('DiscountPercentage BETWEEN 0 AND 100');
            $table->datetime('StartDate');
            $table->datetime('EndDate');
            $table->timestamps();

            // Khoá ngoại
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
        Schema::dropIfExists('product_discounts');
    }
};