<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'ProductID';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ProductName',
        'CategoryID',
        'Price',
        'Stock',
        'Description',
        'ImageURL',
        'CreatedAt'
    ];

    /**
     * Quan hệ với Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }

    /**
     * Quan hệ với ProductDiscounts
     */
    public function discounts()
    {
        return $this->hasMany(ProductDiscount::class, 'ProductID', 'ProductID');
    }

    /**
     * Quan hệ với OrderDetails
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'ProductID', 'ProductID');
    }

    /**
     * Quan hệ với Cart
     */
    public function cartItems()
    {
        return $this->hasMany(Cart::class, 'ProductID', 'ProductID');
    }
}
