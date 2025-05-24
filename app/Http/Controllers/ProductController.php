<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $searchResults = $keyword ? Product::searchByKeyword($keyword) : [];

        return response()->json($searchResults);
    }

    public function filterHome(Request $request)
    {
        $filter = $request->input('filter');

        $query = Product::with('discounts');

        if ($filter === 'price-asc') {
            $query->orderBy('Price', 'asc');
        } elseif ($filter === 'price-desc') {
            $query->orderBy('Price', 'desc');
        }

        $products = $query->get();

        $currentDate = now();

        // Tính lại CurrentPrice cho từng product
        $products->each(function ($product) use ($currentDate) {
            $discount = $product->discounts->firstWhere(function ($discount) use ($currentDate) {
                return $discount->StartDate <= $currentDate && $discount->EndDate >= $currentDate;
            });

            $product->CurrentPrice = $discount
                ? $product->Price * (1 - $discount->DiscountPercentage / 100)
                : $product->Price;

            $product->DiscountPercentage = $discount ? $discount->DiscountPercentage : null;
        });

        return view('partials.product-list', compact('products'))->render();
    }

    public function index(Request $request)
    {
        $sort = $request->input('sort');

        $currentDate = now();

        // Hàm để lấy query sản phẩm theo category và sắp xếp giá
        $getProductsByCategory = function ($category) use ($sort, $currentDate) {
            $query = Product::with('discounts')->where('Category', $category);

            if ($sort === 'price_asc') {
                $query->orderBy('Price', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('Price', 'desc');
            } else {
                $query->orderBy('ProductName', 'asc');
            }

            $products = $query->paginate(12)->withQueryString();

            // Tính CurrentPrice và DiscountPercentage cho từng sản phẩm trong trang
            $products->getCollection()->transform(function ($product) use ($currentDate) {
                $discount = $product->discounts->firstWhere(function ($discount) use ($currentDate) {
                    return $discount->StartDate <= $currentDate && $discount->EndDate >= $currentDate;
                });

                $product->CurrentPrice = $discount
                    ? $product->Price * (1 - $discount->DiscountPercentage / 100)
                    : $product->Price;

                $product->DiscountPercentage = $discount ? $discount->DiscountPercentage : null;

                return $product;
            });

            return $products;
        };

        // Lấy dữ liệu 4 nhóm sản phẩm
        $macProducts = $getProductsByCategory('Mac');
        $iphoneProducts = $getProductsByCategory('iPhone');
        $watchProducts = $getProductsByCategory('Watch');
        $airpodsProducts = $getProductsByCategory('AirPods');

        return view('products.index', compact('macProducts', 'iphoneProducts', 'watchProducts', 'airpodsProducts'));
    }

    public function filterProductsAjax(Request $request)
    {
        $sort = $request->input('sort');
        $category = $request->input('category');

        $currentDate = now();

        $query = Product::with('discounts');

        if ($category) {
            // Lọc theo tên category thông qua quan hệ
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('CategoryName', $category);
            });
        }

        if ($sort === 'price_asc' || $sort === 'price-asc') {
            $query->orderBy('Price', 'asc');
        } elseif ($sort === 'price_desc' || $sort === 'price-desc') {
            $query->orderBy('Price', 'desc');
        } else {
            $query->orderBy('ProductName', 'asc');
        }

        $products = $query->paginate(12);

        $products->getCollection()->transform(function ($product) use ($currentDate) {
            $discount = $product->discounts->firstWhere(function ($discount) use ($currentDate) {
                return $discount->StartDate <= $currentDate && $discount->EndDate >= $currentDate;
            });

            $product->CurrentPrice = $discount
                ? $product->Price * (1 - $discount->DiscountPercentage / 100)
                : $product->Price;

            $product->DiscountPercentage = $discount ? $discount->DiscountPercentage : null;

            return $product;
        });

        return view('partials.product-list', compact('products'))->render();
    }

}