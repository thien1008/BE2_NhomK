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
}