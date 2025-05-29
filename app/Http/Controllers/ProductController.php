<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::findByIdWithDiscount($id);
        if (!$product) {
            return response()->view('errors.404', ['message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $latestProducts = Product::getLatestProducts(3);
        $categoriesFromDB = Product::getCategoriesForDropdown();
        $isLoggedIn = Auth::check();
        $username = $isLoggedIn ? Auth::user()->name : '';
        $cartCount = Product::getCartCount();

        return view('details', compact(
            'product',
            'latestProducts',
            'categoriesFromDB',
            'isLoggedIn',
            'username',
            'cartCount'
        ));
    }

    public function search(Request $request)
    {
        $keyword = trim($request->query('search', ''));
        $searchResults = $keyword ? Product::searchWithDiscount($keyword) : [];

        return response()->json($searchResults);
    }

    // ProductController.php
    public function filterHome(Request $request)
    {
        try {
            $filter = $request->input('filter');
            $products = Product::getFilteredProducts(null, $filter);
            return view('partials.product-list', compact('products'))->render();
        } catch (\Exception $e) {
            Log::error('ProductController::filterHome failed: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $sort = $request->input('sort');
            $category = $request->input('category');

            if ($request->ajax() && $category) {
                $products = Product::getFilteredProducts($category, $sort);
                return response()->json([
                    'html' => view('partials.product-list', compact('products'))->render(),
                ]);
            }

            $macProducts = Product::getFilteredProducts('Mac', $sort);
            $iphoneProducts = Product::getFilteredProducts('iPhone', $sort);
            $watchProducts = Product::getFilteredProducts('Watch', $sort);
            $airpodsProducts = Product::getFilteredProducts('AirPods', $sort);
            $products = Product::getFilteredProducts(null, $sort);

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('partials.product-list', compact('products'))->render(),
                ]);
            }

            return view('products.index', compact('macProducts', 'iphoneProducts', 'watchProducts', 'airpodsProducts', 'products'));
        } catch (\Exception $e) {
            Log::error('ProductController::index failed: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Server error occurred'], 500);
            }
            throw $e;
        }
    }

    // ProductController.php
    public function filterProductsAjax(Request $request)
    {
        try {
            $sort = $request->input('sort');
            $category = $request->input('category');
            $page = $request->input('page', 1);

            // Chỉ kiểm tra danh mục nếu không phải 'all'
            if ($category && $category !== 'all' && !Category::findByName($category)) {
                Log::warning('Invalid category', ['category' => $category]);
                return response()->json([
                    'html' => view('partials.product-list', ['products' => collect([])])->render(),
                    'pagination' => '',
                ], 200);
            }

            $products = Product::getFilteredProducts($category, $sort, $page);

            Log::info('ProductController::filterProductsAjax', [
                'category' => $category,
                'sort' => $sort,
                'page' => $page,
                'product_count' => $products->count()
            ]);

            return response()->json([
                'html' => view('partials.product-list', compact('products'))->render(),
                'pagination' => $products->links('pagination::bootstrap-5')->toHtml(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('ProductController::filterProductsAjax failed', [
                'error' => $e->getMessage(),
                'category' => $category,
                'sort' => $sort,
                'page' => $page
            ]);
            return response()->json(['error' => 'Server error occurred: ' . $e->getMessage()], 500);
        }
    }
}