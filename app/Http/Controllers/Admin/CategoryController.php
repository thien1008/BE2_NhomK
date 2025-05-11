<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $categories = Category::where('CategoryName', 'like', "%{$search}%")
            ->paginate(10);

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'CategoryName' => 'required|string|max:255',
            'Description' => 'nullable|string',
        ]);

        Category::create($request->only(['CategoryName', 'Description']));

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'CategoryName' => 'required|string|max:255',
            'Description' => 'nullable|string',
        ]);

        $category->update($request->only(['CategoryName', 'Description']));

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}