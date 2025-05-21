<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    protected $primaryKey = 'DiscountID';
    protected $fillable = ['ProductID', 'DiscountPercentage', 'StartDate', 'EndDate'];
}