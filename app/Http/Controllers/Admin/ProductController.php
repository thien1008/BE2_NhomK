<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $products = Product::with('category')
            ->where('ProductName', 'like', "%{$search}%")
            ->paginate(10);

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ProductName' => 'required|string|max:255',
            'CategoryID' => 'required|exists:categories,CategoryID',
            'Price' => 'required|numeric|min:0',
            'Stock' => 'required|integer|min:0',
            'Description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = $request->only(['ProductName', 'CategoryID', 'Price', 'Stock', 'Description']);
        if ($request->hasFile('image')) {
            $data['ImageURL'] = $request->file('image')->store('img', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'ProductName' => 'required|string|max:255',
            'CategoryID' => 'required|exists:categories,CategoryID',
            'Price' => 'required|numeric|min:0',
            'Stock' => 'required|integer|min:0',
            'Description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = $request->only(['ProductName', 'CategoryID', 'Price', 'Stock', 'Description']);
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->ImageURL && Storage::disk('public')->exists($product->ImageURL)) {
                Storage::disk('public')->delete($product->ImageURL);
            }
            $data['ImageURL'] = $request->file('image')->store('img', 'public');
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete image if it exists
        if ($product->ImageURL && Storage::disk('public')->exists($product->ImageURL)) {
            Storage::disk('public')->delete($product->ImageURL);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}