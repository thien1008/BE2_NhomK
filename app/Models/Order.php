<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'email',
        'address',
        'province',
        'district',
        'payment_method',
        'notes',
        'total',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    // Phương thức tạo đơn hàng
    public static function createOrder(array $data)
    {
        return self::create([
            'user_id' => $data['user_id'],
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address'],
            'province' => $data['province'],
            'district' => $data['district'],
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'],
            'total' => $data['total'],
            'status' => 'pending',
        ]);
    }

    // Truy vấn đơn hàng mới nhất của người dùng
    public static function getLatestOrder($userId)
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->with('items')
            ->first();
    }

    // Truy vấn tất cả đơn hàng của người dùng
    public static function getUserOrders($userId)
    {
        return self::where('user_id', $userId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}