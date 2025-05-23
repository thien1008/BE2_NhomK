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
        // Lấy giỏ hàng của người dùng từ Cart Model
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
        $shippingFee = 30000; // Phí vận chuyển cố định
        $discount = 0; // Giảm giá (có thể thêm logic xử lý mã giảm giá)
        $total = $subtotal + $shippingFee - $discount;

        // Danh sách tỉnh/thành phố
        $provinces = [
            'Hà Nội',
            'TP. Hồ Chí Minh',
            'Đà Nẵng',
            'Hải Phòng'
        ];

        return view('checkout', compact('cartItems', 'subtotal', 'shippingFee', 'discount', 'total', 'provinces'));
    }

    public function store(Request $request)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'province' => 'required|string',
            'district' => 'required|string',
            'payment_method' => 'required|in:cod,bank_transfer,momo',
            'notes' => 'nullable|string',
        ]);

        // Tạo đơn hàng
        $order = Order::createOrder([
            'user_id' => Auth::id(),
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'province' => $validated['province'],
            'district' => $validated['district'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
            'total' => $this->calculateTotal(),
        ]);

        // Lưu các mục trong giỏ hàng vào OrderItem
        $cartItems = Cart::getUserCartItems();
        foreach ($cartItems as $item) {
            OrderItem::createOrderItem([
                'order_id' => $order->id,
                'product_id' => $item->ProductID,
                'quantity' => $item->Quantity,
                'price' => $item->product->Price,
            ]);
        }

        // Xóa giỏ hàng sau khi đặt hàng
        Cart::clearCart(Auth::id());

        return redirect()->route('checkout.complete')->with('success', 'Đặt hàng thành công!');
    }

    public function complete()
    {
        // Lấy đơn hàng cuối cùng của người dùng
        $order = Order::getLatestOrder(Auth::id());
        
        return view('checkout_complete', compact('order'));
    }

    private function calculateTotal()
    {
        $subtotal = Cart::getUserCartItems()->sum(function ($item) {
            return $item->Quantity * $item->product->Price;
        });
        $shippingFee = 30000;
        $discount = 0; // Có thể thêm logic mã giảm giá
        
        return $subtotal + $shippingFee - $discount;
    }
}