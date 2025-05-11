<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'UserID';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'FullName',
        'Email',
        'Phone',
        'password',
        'UserType',
        'GoogleID',
        'CreatedAt'
    ];

    /**
     * Các thuộc tính nên ẩn
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'PasswordHash',
        'remember_token',
    ];

    /**
     * Chỉ định cột mật khẩu
     *
     * @var string
     */
    protected $password = 'PasswordHash';

    /**
     * Mutator để hash mật khẩu
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            \Log::info('setPasswordAttribute called with value: ' . $value);
            $this->attributes['PasswordHash'] = bcrypt($value);
        }
    }

    /**
     * Lấy giá trị cột mật khẩu
     */
    public function getAuthPassword()
    {
        return $this->PasswordHash;
    }

    /**
     * Quan hệ với Orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'UserID', 'UserID');
    }

    /**
     * Quan hệ với Cart
     */
    public function cart()
    {
        return $this->hasMany(Cart::class, 'UserID', 'UserID');
    }

    /**
     * Quan hệ với UserCoupons
     */
    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class, 'UserID', 'UserID');
    }
}