<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Require authentication for all cart actions
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        if (!$product || $product->Stock < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc không đủ hàng.'], 400);
        }

        $cartItem = Cart::where('UserID', Auth::id())
            ->where('ProductID', $request->product_id)
            ->first();

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
    }

    public function get()
    {
        $cartItems = Cart::where('UserID', Auth::id())
            ->with('product')
            ->get()
            ->map(function ($item) {
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

        $cartItem = Cart::where('UserID', Auth::id())
            ->where('ProductID', $request->product_id)
            ->first();

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ hàng.'], 404);
        }

        if ($request->quantity == 0) {
            $cartItem->delete();
            return response()->json(['success' => true]);
        }

        $product = Product::find($request->product_id);
        if (!$product || $product->Stock < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc không đủ hàng.'], 400);
        }

        $cartItem->update(['Quantity' => $request->quantity]);

        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('UserID', Auth::id())
            ->where('ProductID', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ hàng.'], 404);
    }
}