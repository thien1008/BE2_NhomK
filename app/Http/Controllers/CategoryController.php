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
        // Validate category
        $categoryModel = Category::where('CategoryName', $category)->first();
        if (!$categoryModel) {
            return response()->view('errors.404', ['message' => 'Danh mục không tồn tại.'], 404);
        }

        $sort = $request->query('sort', '');
        $products = Product::getByCategoryWithDiscount($category, $sort, 10);

        if ($request->ajax()) {
            return response()->json([
                'products' => $products->items(),
                'pagination' => $products->links()->toHtml(),
            ]);
        }

        $isLoggedIn = Auth::check();
        $username = $isLoggedIn ? Auth::user()->FullName : '';
        $cartCount = Product::getCartCount();
        $categoriesFromDB = Product::getCategoriesForDropdown();

        return view('home', [
            'macProducts' => $category === 'Mac' ? $products : Product::getByCategoryWithDiscount('Mac', '', 10),
            'iphoneProducts' => $category === 'iPhone' ? $products : Product::getByCategoryWithDiscount('iPhone', '', 10),
            'watchProducts' => $category === 'Watch' ? $products : Product::getByCategoryWithDiscount('Watch', '', 10),
            'airpodsProducts' => $category === 'AirPods' ? $products : Product::getByCategoryWithDiscount('AirPods', '', 10),
            'categoriesFromDB' => $categoriesFromDB,
            'isLoggedIn' => $isLoggedIn,
            'username' => $username,
            'cartCount' => $cartCount,
        ]);
    }
}