<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    protected $primaryKey = 'DiscountID';
    protected $table = 'product_discounts';
    protected $fillable = [
        'ProductID',
        'DiscountPercentage',
        'StartDate',
        'EndDate',
        'version'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }
}