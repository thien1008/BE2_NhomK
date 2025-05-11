<?php
// app/Models/Cart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'cart';

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'CartID';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'UserID',
        'ProductID',
        'Quantity',
        'AddedAt'
    ];

    /**
     * Quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    /**
     * Quan hệ với Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }
}