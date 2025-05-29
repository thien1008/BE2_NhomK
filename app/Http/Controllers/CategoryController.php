<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function show($category, Request $request)
    {
        Log::info('CategoryController::show called', ['category' => $category, 'isAjax' => $request->ajax()]);

        $categoryModel = Category::findByName($category);
        if (!$categoryModel) {
            Log::warning('Category not found', ['category' => $category]);
            return response()->view('errors.404', ['message' => 'Danh mục không tồn tại.'], 404);
        }

        $sort = $request->query('sort', '');
        $page = $request->query('page', 1);
        $products = Product::getFilteredProducts($category, $sort, $page);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.product-list', compact('products'))->render(),
                'pagination' => $products->links('pagination::bootstrap-5')->toHtml(),
            ]);
        }

        $isLoggedIn = Auth::check();
        $username = $isLoggedIn ? Auth::user()->FullName : '';
        $cartCount = Product::getCartCount();
        $categoriesFromDB = Product::getCategoriesForDropdown();

        return view('home', [
            'macProducts' => $category === 'Mac' ? $products : Product::getFilteredProducts('Mac', ''),
            'iphoneProducts' => $category === 'iPhone' ? $products : Product::getFilteredProducts('iPhone', ''),
            'watchProducts' => $category === 'Watch' ? $products : Product::getFilteredProducts('Watch', ''),
            'airpodsProducts' => $category === 'AirPods' ? $products : Product::getFilteredProducts('AirPods', ''),
            'categoriesFromDB' => $categoriesFromDB,
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'cartCount' => $cartCount,
        ]);
    }
}