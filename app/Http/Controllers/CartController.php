<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class CartController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $cartItems = Cart::getUserCartItems()->map(function ($item) {
            return (object) [
                'ProductID' => $item->ProductID,
                'Quantity' => $item->Quantity,
                'ProductName' => $item->product->ProductName,
                'CurrentPrice' => (float) $item->product->Price,
                'OriginalPrice' => (float) ($item->product->OriginalPrice ?? $item->product->Price),
                'ImageURL' => $item->product->ImageURL,
                'Stock' => $item->product->Stock,
                'Color' => $item->product->Color ?? null,
                'Memory' => $item->product->Memory ?? null,
            ];
        });

        $cartTotal = $cartItems->sum(function ($item) {
            return $item->CurrentPrice * $item->Quantity;
        });
        $cartCount = $cartItems->sum('Quantity');

        $relatedProducts = Product::getRandomProducts()->map(function ($product) {
            return (object) [
                'ProductID' => $product->ProductID,
                'ProductName' => $product->ProductName,
                'ImageURL' => $product->ImageURL,
                'CurrentPrice' => (float) $product->Price,
                'Price' => (float) ($product->OriginalPrice ?? $product->Price),
                'DiscountPercentage' => $product->OriginalPrice && $product->Price < $product->OriginalPrice
                    ? round(($product->OriginalPrice - $product->Price) / $product->OriginalPrice * 100)
                    : 0,
            ];
        });

        // Thêm biến categories để hỗ trợ dropdown danh mục
        $categories = Category::with('products')->get()->mapWithKeys(function ($category) {
            $normalizedName = strtolower($category->CategoryName);
            return [
                $normalizedName => $category->products->map(function ($product) {
                    return ['ProductID' => $product->ProductID, 'ProductName' => $product->ProductName];
                })
            ];
        })->toArray();

        return view('cart', compact('cartItems', 'cartTotal', 'cartCount', 'relatedProducts', 'categories'));
    }

    // CartController.php
    public function add(Request $request)
    {
        try {
            \Log::info('Cart add request:', $request->all());

            $request->validate([
                'product_id' => 'required|integer|min:1',
                'quantity' => 'required|integer|min:1',
            ]);

            // Use find instead of findById
            $product = Product::find($request->product_id);
            if (!$product || $product->Stock < $request->quantity) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc không đủ hàng.'], 400);
            }

            $cartItem = Cart::findByProductAndUser($request->product_id);

            if ($cartItem) {
                $newQuantity = $cartItem->Quantity + $request->quantity;
                if ($newQuantity > $product->Stock) {
                    return response()->json(['success' => false, 'message' => 'Số lượng vượt quá tồn kho.'], 400);
                }
                $cartItem->update(['Quantity' => $newQuantity]);
            } else {
                Cart::create([
                    'UserID' => Auth::id(),
                    'ProductID' => $request->product_id,
                    'Quantity' => $request->quantity,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Cart add error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Lỗi máy chủ, vui lòng thử lại sau.'], 500);
        }
    }

    public function get()
    {
        $cartItems = Cart::getUserCartItems()->map(function ($item) {
            return [
                'ProductID' => $item->ProductID,
                'Quantity' => $item->Quantity,
                'ProductName' => $item->product->ProductName,
                'Price' => (float) $item->product->Price,
                'ImageURL' => $item->product->ImageURL,
                'Stock' => $item->product->Stock,
            ];
        });

        $total = $cartItems->sum(function ($item) {
            return $item['Price'] * $item['Quantity'];
        });
        $itemCount = $cartItems->sum('Quantity');

        return response()->json([
            'success' => true,
            'cart' => $cartItems,
            'total' => $total,
            'itemCount' => $itemCount,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:0',
        ]);

        $cartItem = Cart::findByProductAndUser($request->product_id);

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ hàng.'], 404);
        }

        if ($request->quantity == 0) {
            $cartItem->delete();
        } else {
            $product = Product::find($request->product_id);
            if (!$product || $product->Stock < $request->quantity) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc không đủ hàng.'], 400);
            }
            $cartItem->update(['Quantity' => $request->quantity]);
        }

        // Fetch updated cart data
        $cartItems = Cart::getUserCartItems()->map(function ($item) {
            if (!$item->product) {
                return null; // Skip invalid items
            }
            return [
                'ProductID' => (int) $item->ProductID, // Ensure integer
                'Quantity' => (int) $item->Quantity, // Ensure integer
                'ProductName' => $item->product->ProductName,
                'CurrentPrice' => (float) ($item->product->Price ?? 0), // Ensure number
                'ImageURL' => $item->product->ImageURL,
                'Stock' => (int) ($item->product->Stock ?? 0),
            ];
        })->filter()->values(); // Remove null items and reindex array

        $total = $cartItems->sum(function ($item) {
            return $item['CurrentPrice'] * $item['Quantity'];
        });
        $itemCount = $cartItems->sum('Quantity');

        return response()->json([
            'success' => true,
            'cart' => $cartItems,
            'total' => (float) $total,
            'itemCount' => (int) $itemCount,
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::findByProductAndUser($request->product_id);

        if ($cartItem) {
            $cartItem->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ hàng.'], 404);
    }
}