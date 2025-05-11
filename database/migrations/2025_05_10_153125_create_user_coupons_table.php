<?php
// database/migrations/2024_05_10_000009_create_user_coupons_table.php

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
        Schema::create('user_coupons', function (Blueprint $table) {
            $table->id('UserCouponID');
            $table->unsignedBigInteger('UserID');
            $table->unsignedBigInteger('CouponID');
            $table->datetime('UsedAt')->useCurrent();
            $table->timestamps();

            // Khoá ngoại
            $table->foreign('UserID')
                ->references('UserID')
                ->on('users')
                ->onDelete('cascade');
                
            $table->foreign('CouponID')
                ->references('CouponID')
                ->on('coupons')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_coupons');
    }
};