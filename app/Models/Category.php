<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'CategoryID';
    protected $fillable = ['CategoryName'];

    public function products()
    {
        return $this->hasMany(Product::class, 'CategoryID');
    }

    public static function findByName($categoryName)
    {
        return self::where('CategoryName', $categoryName)->first();
    }

    public static function getCategoriesForDropdown()
    {
        return self::with([
            'products' => function ($query) {
                $query->whereNotNull('ProductID');
            }
        ])->get()->mapWithKeys(function ($category) {
            $normalizedName = strtolower($category->CategoryName);
            return [
                $normalizedName => $category->products->filter(function ($product) {
                    return !is_null($product->ProductID) && Product::where('ProductID', $product->ProductID)->exists();
                })->map(function ($product) {
                    return ['ProductID' => $product->ProductID, 'ProductName' => $product->ProductName];
                })
            ];
        })->toArray();
    }
}