<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\NewsletterSubscription;
use App\Mail\NewsletterSubscriptionConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax() && $request->has('search')) {
                $keyword = trim($request->input('search'));
                Log::info('Search keyword:', ['keyword' => $keyword]); // Thêm log
                if ($keyword) {
                    $searchResults = Product::searchWithDiscount($keyword);
                    Log::info('Search results:', ['results' => $searchResults->toArray()]); // Thêm log
                } else {
                    $searchResults = collect([]);
                }
                return response()->json($searchResults);
            }

            // Handle AJAX pagination and filtering
            if ($request->ajax()) {
                $sort = $request->query('sort', '');
                $page = $request->query('page', 1);
                $category = $request->query('category', null);

                $products = Product::getFilteredProducts($category, $sort, $page);
                return response()->json([
                    'html' => view('partials.product-list', compact('products'))->render(),
                    'pagination' => $products->links()->toHtml(),
                ]);
            }

            // Get latest products
            $latestProducts = Product::getLatestProducts();

            // Get sort parameter
            $sort = $request->query('sort', '');

            // Fetch all products with discount (with pagination)
            $products = Product::getFilteredProducts(null, $sort, 1);

            // Get products for each category
            $macProducts = Product::getFilteredProducts('Mac', $sort, 1);
            $iphoneProducts = Product::getFilteredProducts('iPhone', $sort, 1);
            $watchProducts = Product::getFilteredProducts('Watch', $sort, 1);
            $airpodsProducts = Product::getFilteredProducts('AirPods', $sort, 1);

            // Get categories for dropdown
            $categories = Category::getCategoriesForDropdown();

            // Cart count
            $cartCount = Product::getCartCount();

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
        } catch (\Exception $e) {
            Log::error('HomeController::index failed: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Server error occurred'], 500);
            }
            throw $e;
        }
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