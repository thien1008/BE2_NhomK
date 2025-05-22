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

    /**
     * Find a product by ID with active discount
     *
     * @param int $id
     * @return Product|null
     */
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

    /**
     * Get latest products
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLatestProducts($limit = 3)
    {
        return self::latest('CreatedAt')->take($limit)->get();
    }

    /**
     * Get categories with their products for dropdown
     *
     * @return array
     */
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

    /**
     * Get cart item count for the authenticated user
     *
     * @return int
     */
    public static function getCartCount()
    {
        return Auth::check() ? Cart::where('UserID', Auth::id())->sum('Quantity') : 0;
    }

    /**
     * Search products by keyword
     *
     * @param string $keyword
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
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


    public static function getPaginatedWithDiscount($perPage = 12)
    {
        $currentDate = now();

        return self::with([
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
            })
            ->paginate($perPage)
            ->through(function ($product) {
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

}