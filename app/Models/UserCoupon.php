<?php
// app/Models/UserCoupon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'user_coupons';

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'UserCouponID';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'UserID',
        'CouponID',
        'UsedAt'
    ];

    /**
     * Quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    /**
     * Quan hệ với Coupon
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'CouponID', 'CouponID');
    }
}