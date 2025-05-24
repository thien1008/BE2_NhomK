<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $primaryKey = 'ProductID';
    protected $fillable = ['ProductName', 'Price', 'OriginalPrice', 'ImageURL', 'Stock', 'Color', 'Memory', 'CategoryID', 'Description', 'CreatedAt'];
    public $timestamps = false;

    public function discounts()
    {
        return $this->hasMany(ProductDiscount::class, 'ProductID', 'ProductID');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }

    public static function findByIdWithDiscount($id)
    {
        $product = self::find($id);
        if (!$product) {
            return null;
        }

        $currentDate = now()->toDateTimeString();
        $discount = $product->discounts()
            ->where('StartDate', '<=', $currentDate)
            ->where('EndDate', '>=', $currentDate)
            ->first();

        $product->DiscountPercentage = $discount ? $discount->DiscountPercentage : null;
        $product->CurrentPrice = $discount
            ? $product->Price * (1 - $discount->DiscountPercentage / 100)
            : $product->Price;

        return $product;
    }

    public static function getLatestProducts($limit = 3)
    {
        return self::latest('CreatedAt')->take($limit)->get();
    }

    public static function getCategoriesForDropdown()
    {
        $categoriesFromDB = [];
        $allProducts = self::with('category')->get();
        foreach ($allProducts as $p) {
            $categoryName = strtolower($p->category->CategoryName);
            $categoriesFromDB[$categoryName] = $categoriesFromDB[$categoryName] ?? [];
            $categoriesFromDB[$categoryName][] = [
                'ProductID' => $p->ProductID,
                'ProductName' => $p->ProductName
            ];
        }
        return $categoriesFromDB;
    }

    public static function getCartCount()
    {
        return Auth::check() ? Cart::where('UserID', Auth::id())->sum('Quantity') : 0;
    }

    public static function searchWithDiscount($keyword, $limit = 10)
    {
        $currentDate = now();

        return self::with([
            'discounts' => function ($query) use ($currentDate) {
                $query->where('StartDate', '<=', $currentDate)
                    ->where('EndDate', '>=', $currentDate);
            }
        ])
            ->where(function ($q) use ($keyword) {
                $q->where('ProductName', 'like', "%$keyword%")
                    ->orWhere('Description', 'like', "%$keyword%");
            })
            ->limit($limit)
            ->get([
                'ProductID',
                'ProductName',
                'ImageURL',
                'Price',
                'CategoryID',
                'Description',
                'CreatedAt',
            ])
            ->map(function ($product) {
                $discount = $product->discounts->first();

                return [
                    'ProductID' => $product->ProductID,
                    'ProductName' => $product->ProductName,
                    'ImageURL' => $product->ImageURL,
                    'Price' => $product->Price,
                    'CurrentPrice' => $discount
                        ? $product->Price * (1 - $discount->DiscountPercentage / 100)
                        : $product->Price,
                    'DiscountPercentage' => $discount ? $discount->DiscountPercentage : null,
                ];
            });
    }

    public static function getPaginatedWithDiscount($perPage = 10, $sort = '')
    {
        $currentDate = now();

        $query = self::with([
            'discounts' => function ($query) use ($currentDate) {
                $query->where('StartDate', '<=', $currentDate)
                    ->where('EndDate', '>=', $currentDate);
            }
        ])
            ->whereNotNull('ProductID')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('products')
                    ->whereColumn('products.ProductID', 'ProductID');
            });

        if ($sort === 'price-asc') {
            $query->orderBy('Price', 'asc');
        } elseif ($sort === 'price-desc') {
            $query->orderBy('Price', 'desc');
        }

        return $query->paginate($perPage)->through(function ($product) {
            $discount = $product->discounts->first();

            $product->CurrentPrice = $discount
                ? $product->Price * (1 - $discount->DiscountPercentage / 100)
                : $product->Price;

            $product->DiscountPercentage = $discount ? $discount->DiscountPercentage : null;

            return $product;
        });
    }

    public static function getRandomProducts($limit = 4)
    {
        return self::inRandomOrder()->take($limit)->get();
    }

    public static function getByCategoryWithDiscount($categoryName, $sort = '', $perPage = 10)
    {
        $currentDate = now();

        $query = self::with([
            'discounts' => function ($query) use ($currentDate) {
                $query->where('StartDate', '<=', $currentDate)
                    ->where('EndDate', '>=', $currentDate);
            }
        ])
            ->whereHas('category', function ($query) use ($categoryName) {
                $query->where('CategoryName', $categoryName);
            });

        if ($sort === 'price-asc') {
            $query->orderBy('Price', 'asc');
        } elseif ($sort === 'price-desc') {
            $query->orderBy('Price', 'desc');
        }

        return $query->paginate($perPage)->through(function ($product) {
            $discount = $product->discounts->first();

            $product->CurrentPrice = $discount
                ? $product->Price * (1 - $discount->DiscountPercentage / 100)
                : $product->Price;

            $product->DiscountPercentage = $discount ? $discount->DiscountPercentage : null;

            return $product;
        });
    }
}