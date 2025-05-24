<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';
    protected $primaryKey = 'OrderDetailID';
    public $timestamps = true;

    protected $fillable = [
        'OrderID',
        'ProductID',
        'Quantity',
        'Price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public static function createOrderDetail(array $data)
    {
        return self::create([
            'OrderID' => $data['order_id'],
            'ProductID' => $data['product_id'],
            'Quantity' => $data['quantity'],
            'Price' => $data['price'],
        ]);
    }

    public static function getItemsByOrder($orderId)
    {
        return self::where('OrderID', $orderId)
            ->with('product')
            ->get();
    }
}