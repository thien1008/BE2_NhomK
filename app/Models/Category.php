<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'CategoryID';
    protected $fillable = ['CategoryName', 'Description', 'version'];

    public function products()
    {
        return $this->hasMany(Product::class, 'CategoryID');
    }
}