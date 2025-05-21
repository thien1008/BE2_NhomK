<?php
// database/migrations/2024_05_10_000008_create_coupons_table.php

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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id('CouponID');
            $table->string('Code', 50)->unique();
            $table->decimal('DiscountPercentage', 5, 2)->check('DiscountPercentage BETWEEN 0 AND 100');
            $table->datetime('ValidFrom');
            $table->datetime('ValidTo');
            $table->integer('UsageLimit')->default(1);
            $table->integer('UsedCount')->default(0);
            $table->integer('UserLimit')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};