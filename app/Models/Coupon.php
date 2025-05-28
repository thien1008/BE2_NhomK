<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    protected $primaryKey = 'CouponID';

    protected $fillable = [
        'Code',
        'DiscountPercentage',
        'ValidFrom',
        'ValidTo',
        'UsageLimit',
        'UsedCount',
        'UserLimit',
        'version'
    ];

    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class, 'CouponID', 'CouponID');
    }
}