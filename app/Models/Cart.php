<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cart extends Model
{
    protected $table = 'cart';
    protected $primaryKey = 'CartID';
    protected $fillable = ['UserID', 'ProductID', 'Quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public static function getUserCartItems()
    {
        return self::where('UserID', Auth::id())
            ->with('product')
            ->get();
    }

    public static function findByProductAndUser($productId)
    {
        return self::where('UserID', Auth::id())
            ->where('ProductID', $productId)
            ->first();
    }

    public static function getUserCartCount()
    {
        return self::where('UserID', Auth::id())->sum('Quantity');
    }

    public static function clearCart($userId)
    {
        return self::where('UserID', $userId)->delete();
    }
}