<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cart extends Model
{
    protected $table = 'cart';
    protected $primaryKey = 'CartID'; // Specify the correct primary key
    protected $fillable = ['UserID', 'ProductID', 'Quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    /**
     * Lấy danh sách các mục trong giỏ hàng của người dùng hiện tại
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUserCartItems()
    {
        return self::where('UserID', Auth::id())
            ->with('product')
            ->get();
    }

    /**
     * Tìm mục giỏ hàng theo ProductID và UserID
     *
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findByProductAndUser($productId)
    {
        return self::where('UserID', Auth::id())
            ->where('ProductID', $productId)
            ->first();
    }

    /**
     * Tính tổng số lượng sản phẩm trong giỏ hàng của người dùng
     *
     * @return int
     */
    public static function getUserCartCount()
    {
        return self::where('UserID', Auth::id())->sum('Quantity');
    }
}