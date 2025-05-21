<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Handle logout
        if ($request->has('logout')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login-register');
        }

        // Handle AJAX search
        if ($request->has('search')) {
            $keyword = trim($request->input('search'));
            $searchResults = $keyword
                ? Product::where('ProductName', 'like', "%$keyword%")
                    ->orWhere('Description', 'like', "%$keyword%")
                    ->take(10)
                    ->get(['ProductID', 'ProductName', 'ImageURL', 'Price'])
                    ->map(function ($product) {
                        $currentDate = now();
                        $discount = $product->discounts()
                            ->where('StartDate', '<=', $currentDate)
                            ->where('EndDate', '>=', $currentDate)
                            ->first();

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
                    })
                : [];
            return response()->json($searchResults);
        }

        // Get latest products
        $latestProducts = Product::latest('CreatedAt')->take(3)->get();

        // Paginate all products with discount
        $currentDate = now();
        $perPage = 8;
        $products = Product::paginate($perPage)->through(function ($product) use ($currentDate) {
            $discount = $product->discounts()
                ->where('StartDate', '<=', $currentDate)
                ->where('EndDate', '>=', $currentDate)
                ->first();

            $product->DiscountPercentage = $discount ? $discount->DiscountPercentage : null;
            $product->CurrentPrice = $discount
                ? $product->Price * (1 - $discount->DiscountPercentage / 100)
                : $product->Price;

            return $product;
        });

        // Get categories for dropdown
        $categories = Category::with('products')->get()->mapWithKeys(function ($category) {
            $normalizedName = strtolower($category->CategoryName);
            return [
                $normalizedName => $category->products->map(function ($product) {
                    return ['ProductID' => $product->ProductID, 'ProductName' => $product->ProductName];
                })
            ];
        })->toArray();

        // Cart count
        $cartCount = Auth::check() ? Cart::where('UserID', Auth::id())->sum('Quantity') : 0;

        // User info
        $user = Auth::user();

        return view('home', compact('latestProducts', 'products', 'categories', 'cartCount', 'user'));
    }

}