<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $primaryKey = 'ProductID';
    protected $fillable = ['ProductName', 'Price', 'ImageURL', 'Stock', 'CategoryID', 'Description', 'version'];
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

    public static function searchWithDiscount($keyword, $limit = 6)
    {
        $keyword = strtolower($keyword);
        return self::with('category')
            ->where(function ($q) use ($keyword) {
                $q->whereRaw('LOWER(ProductName) LIKE ?', ["%$keyword%"])
                    ->orWhereRaw('LOWER(Description) LIKE ?', ["%$keyword%"])
                    ->orWhereHas('category', function ($query) use ($keyword) {
                        $query->whereRaw('LOWER(CategoryName) LIKE ?', ["%$keyword%"]);
                    });
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
                return [
                    'ProductID' => $product->ProductID,
                    'ProductName' => $product->ProductName,
                    'ImageURL' => $product->ImageURL,
                    'Price' => $product->Price,
                    'CurrentPrice' => $product->Price,
                    'DiscountPercentage' => null,
                ];
            });
    }

    public static function getPaginatedWithDiscount($sort = '')
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

        return $query->get()->map(function ($product) {
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

    // Product.php
    // Product.php
    public static function getFilteredProducts($category = null, $sort = '', $page = 1, $perPage = 12)
    {
        $cacheKey = "products_{$category}_{$sort}_{$page}_{$perPage}";
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($category, $sort, $page, $perPage) {
            $currentDate = now();
            $query = self::with(['category']);

            if ($category && $category !== 'all') {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->whereRaw('LOWER(CategoryName) = ?', [strtolower($category)]);
                });
            }

            if ($sort === 'price-asc' || $sort === 'price_asc') {
                $query->orderBy('Price', 'asc');
            } elseif ($sort === 'price-desc' || $sort === 'price_desc') {
                $query->orderBy('Price', 'desc');
            } else {
                $query->orderBy('ProductName', 'asc');
            }

            $products = $query->paginate($perPage, ['*'], 'page', $page);
            if ($page > $products->lastPage()) {
                return $query->paginate($perPage, ['*'], 'page', 1);
            }

            return $products->through(function ($product) use ($currentDate) {
                // Bỏ logic giảm giá vì bảng discounts không tồn tại
                $product->CurrentPrice = $product->Price;
                $product->DiscountPercentage = null;
                return $product;
            });
        });
    }
}