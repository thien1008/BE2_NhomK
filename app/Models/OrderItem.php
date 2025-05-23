<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Phương thức tạo mục đơn hàng
    public static function createOrderItem(array $data)
    {
        return self::create([
            'order_id' => $data['order_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'price' => $data['price'],
        ]);
    }

    // Truy vấn các mục đơn hàng theo order_id
    public static function getItemsByOrder($orderId)
    {
        return self::where('order_id', $orderId)
            ->with('product')
            ->get();
    }
}