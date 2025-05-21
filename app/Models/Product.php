<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'ProductID';
    protected $fillable = ['ProductName', 'Price', 'CategoryID', 'Description', 'Stock', 'ImageURL'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID');
    }

    public function discounts()
    {
        return $this->hasMany(ProductDiscount::class, 'ProductID');
    }
}