<?php
// app/Models/Coupon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'coupons';

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'CouponID';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Code',
        'DiscountPercentage',
        'ValidFrom',
        'ValidTo',
        'UsageLimit',
        'UsedCount',
        'UserLimit'
    ];

    /**
     * Quan hệ với UserCoupons
     */
    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class, 'CouponID', 'CouponID');
    }
}