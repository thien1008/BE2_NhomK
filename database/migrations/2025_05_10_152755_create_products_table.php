<?php
// database/migrations/2024_05_10_000003_create_products_table.php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id('ProductID');
            $table->string('ProductName', 255);
            $table->unsignedBigInteger('CategoryID');
            $table->decimal('Price', 10, 2);
            $table->integer('Stock');
            $table->text('Description')->nullable();
            $table->string('ImageURL', 255)->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamps();

            // Khoá ngoại
            $table->foreign('CategoryID')
                ->references('CategoryID')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};