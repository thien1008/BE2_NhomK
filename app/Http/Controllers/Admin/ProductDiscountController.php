<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductDiscount;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductDiscountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $productDiscounts = ProductDiscount::with('product')
            ->whereHas('product', function ($query) use ($search) {
                $query->where('ProductName', 'like', "%{$search}%");
            })
            ->paginate(10);

        return view('admin.product_discounts.index', compact('productDiscounts', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ProductID' => 'required|exists:products,ProductID',
            'DiscountPercentage' => 'required|numeric|min:0|max:100',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        ProductDiscount::create($request->only([
            'ProductID',
            'DiscountPercentage',
            'StartDate',
            'EndDate'
        ]));

        return redirect()->route('admin.product_discounts.index')->with('success', 'Product discount created successfully.');
    }

    public function update(Request $request, ProductDiscount $productDiscount)
    {
        $request->validate([
            'ProductID' => 'required|exists:products,ProductID',
            'DiscountPercentage' => 'required|numeric|min:0|max:100',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
        ]);

        $productDiscount->update($request->only([
            'ProductID',
            'DiscountPercentage',
            'StartDate',
            'EndDate'
        ]));

        return redirect()->route('admin.product_discounts.index')->with('success', 'Product discount updated successfully.');
    }

    public function destroy(ProductDiscount $productDiscount)
    {
        $productDiscount->delete();

        return redirect()->route('admin.product_discounts.index')->with('success', 'Product discount deleted successfully.');
    }
}