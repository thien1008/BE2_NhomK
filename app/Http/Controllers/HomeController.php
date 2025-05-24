<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\NewsletterSubscription;
use App\Mail\NewsletterSubscriptionConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
        if ($request->ajax() && $request->has('search')) {
            $keyword = trim($request->input('search'));

            if ($keyword) {
                $searchResults = Product::searchWithDiscount($keyword);
            } else {
                $searchResults = collect([]);
            }

            return response()->json($searchResults);
        }

        // Get latest products
        $latestProducts = Product::getLatestProducts();

        // Get sort parameter
        $sort = $request->query('sort', '');

        // Paginate all products with discount (10 products per page)
        $products = Product::getPaginatedWithDiscount(10, $sort);

        // Get products for each category
        $macProducts = Product::getByCategoryWithDiscount('Mac', $sort, 10);
        $iphoneProducts = Product::getByCategoryWithDiscount('iPhone', $sort, 10);
        $watchProducts = Product::getByCategoryWithDiscount('Watch', $sort, 10);
        $airpodsProducts = Product::getByCategoryWithDiscount('AirPods', $sort, 10);

        // Get categories for dropdown
        $categories = Category::with(['products' => function ($query) {
            $query->whereNotNull('ProductID');
        }])->get()->mapWithKeys(function ($category) {
            $normalizedName = strtolower($category->CategoryName);
            return [
                $normalizedName => $category->products->filter(function ($product) {
                    return !is_null($product->ProductID) && Product::where('ProductID', $product->ProductID)->exists();
                })->map(function ($product) {
                    return ['ProductID' => $product->ProductID, 'ProductName' => $product->ProductName];
                })
            ];
        })->toArray();

        // Cart count
        $cartCount = Auth::check() ? Cart::getUserCartCount() : 0;

        // User info
        $user = Auth::user();

        return view('home', compact(
            'latestProducts',
            'products',
            'macProducts',
            'iphoneProducts',
            'watchProducts',
            'airpodsProducts',
            'categories',
            'cartCount',
            'user'
        ));
    }

    public function subscribeNewsletter(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletter_subscriptions,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            $email = $request->input('email');

            // Save to database
            NewsletterSubscription::create([
                'email' => $email,
                'is_subscribed' => true
            ]);

            // Send confirmation email
            Mail::to($email)->send(new NewsletterSubscriptionConfirmation($email));

            return response()->json([
                'success' => 'Thank you for subscribing to our newsletter!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while subscribing. Please try again later.'
            ], 500);
        }
    }
}