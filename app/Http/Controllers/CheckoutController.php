<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $cartItems = Cart::getUserCartItems()->map(function ($item) {
            return (object) [
                'CartID' => $item->CartID,
                'ProductID' => $item->ProductID,
                'Quantity' => $item->Quantity,
                'ProductName' => $item->product->ProductName,
                'CurrentPrice' => (float) $item->product->Price,
                'ImageURL' => $item->product->ImageURL,
                'Stock' => $item->product->Stock,
                'Color' => $item->product->Color ?? null,
                'Memory' => $item->product->Memory ?? null,
            ];
        });

        $subtotal = $cartItems->sum(function ($item) {
            return $item->CurrentPrice * $item->Quantity;
        });
        $shippingFee = 30000;
        $discount = 0;
        $total = $subtotal + $shippingFee - $discount;

        return view('checkout', compact('cartItems', 'subtotal', 'shippingFee', 'discount', 'total'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $paymentMethod = $request->input('payment_method');
            $couponCode = $request->input('coupon_code');

            $cartItems = Cart::getUserCartItems();

            if ($cartItems->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Giỏ hàng trống!'], 400);
            }

            $total = 0;

            // Chuẩn bị mảng items để tạo order details
            $orderItems = [];

            foreach ($cartItems as $item) {
                // Lấy sản phẩm kèm giá áp dụng giảm giá
                $productWithDiscount = \App\Models\Product::findByIdWithDiscount($item->ProductID);

                if (!$productWithDiscount) {
                    return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại!'], 404);
                }

                $price = $productWithDiscount->CurrentPrice ?? $productWithDiscount->Price;

                $total += $item->Quantity * $price;

                $orderItems[] = [
                    'ProductID' => $item->ProductID,
                    'Quantity' => $item->Quantity,
                    'Price' => $price,
                ];
            }

            $discount = 0;
            if ($couponCode === 'GIAM10') {
                $discount = 0.1 * $total;
            }

            $finalAmount = $total + 30000 - $discount;

            $order = \App\Models\Order::create([
                'UserID' => $user->UserID,
                'TotalPrice' => $finalAmount,
                'Status' => 'Pending',
            ]);

            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            Cart::where('UserID', $user->UserID)->delete();

            return response()->json(['success' => true, 'order_id' => $order->OrderID]);
        } catch (\Exception $e) {
            \Log::error('Checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500);
        }
    }


    private function calculateDiscount($couponCode)
    {
        if ($couponCode === 'VALIDCODE') {
            return 100000;
        }
        return 0;
    }

    public function complete()
    {
        $order = Order::getLatestOrder(Auth::id());
        return view('checkout_complete', compact('order'));
    }

    public function applyCoupon(Request $request)
    {
        try {
            $validated = $request->validate([
                'coupon_code' => 'required|string',
            ]);

            $couponCode = $validated['coupon_code'];
            $discount = 0;
            if ($couponCode === 'VALIDCODE') {
                $discount = 100000;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ!'
                ], 400);
            }

            $subtotal = Cart::getUserCartItems()->sum(function ($item) {
                return $item->Quantity * $item->product->Price;
            });
            $shippingFee = 30000;
            $total = $subtotal + $shippingFee - $discount;

            return response()->json([
                'success' => true,
                'discount' => $discount,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            \Log::error('Coupon apply error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi áp dụng mã giảm giá!'
            ], 500);
        }
    }

    private function calculateTotal()
    {
        $subtotal = Cart::getUserCartItems()->sum(function ($item) {
            return $item->Quantity * $item->product->Price;
        });
        $shippingFee = 30000;
        $discount = 0;
        return $subtotal + $shippingFee - $discount;
    }

    public function showOrderDetails($order_id)
    {
        $order = Order::with('items.product')->findOrFail($order_id);
        return view('orderdetails', compact('order'));
    }
}