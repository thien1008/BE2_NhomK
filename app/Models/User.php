<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'UserID';

    protected $fillable = [
        'FullName',
        'Email',
        'Phone',
        'password',
        'UserType',
        'GoogleID',
        'FacebookID',
        'CreatedAt'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            \Log::info('setPasswordAttribute called for user ID: ' . ($this->UserID ?? 'new'), ['value' => '[hidden]']);
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'UserID', 'UserID');
    }

    public function cart()
    {
        return $this->hasMany(Cart::class, 'UserID', 'UserID');
    }

    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class, 'UserID', 'UserID');
    }
}
