<?php
// app/Models/ProductDiscount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu
     *
     * @var string
     */
    protected $table = 'product_discounts';

    /**
     * Khóa chính của bảng
     *
     * @var string
     */
    protected $primaryKey = 'DiscountID';

    /**
     * Các thuộc tính có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ProductID',
        'DiscountPercentage',
        'StartDate',
        'EndDate'
    ];

    /**
     * Quan hệ với Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }
}