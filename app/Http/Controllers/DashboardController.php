<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductDiscount;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'categories' => Category::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'users' => User::count(),
            'coupons' => Coupon::count(),
            'product_discounts' => ProductDiscount::count(),
        ];

        $recentOrders = Order::with('user')
            ->orderBy('CreatedAt', 'desc')
            ->take(7)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}