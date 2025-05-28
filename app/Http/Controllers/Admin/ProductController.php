<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    // Deprecated: Using modal instead
    /*
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }
    */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ProductName' => 'required|string|max:255|unique:products,ProductName',
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
            $data['version'] = 1;

            Product::create($data);

            return response()->json(['success' => 'Sản phẩm đã được tạo thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo sản phẩm.'], 500);
        }
    }

    // Deprecated: Using modal instead
    /*
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }
    */

    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
            }

            $validated = $request->validate([
                'ProductName' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('products', 'ProductName')->ignore($id, 'ProductID'),
                ],
                'CategoryID' => 'required|exists:categories,CategoryID',
                'Price' => 'required|numeric|min:0',
                'Stock' => 'required|integer|min:0',
                'Description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
                'version' => 'required|integer|min:1',
            ]);

            if ($product->version != $request->version) {
                return response()->json(['error' => 'Dữ liệu sản phẩm đã được thay đổi bởi người khác. Vui lòng làm mới trang và thử lại.'], 409);
            }

            $data = $request->only(['ProductName', 'CategoryID', 'Price', 'Stock', 'Description']);
            if ($request->hasFile('image')) {
                if ($product->ImageURL && Storage::disk('public')->exists($product->ImageURL)) {
                    Storage::disk('public')->delete($product->ImageURL);
                }
                $data['ImageURL'] = $request->file('image')->store('img', 'public');
            }
            $data['version'] = $product->version + 1;

            $product->update($data);

            return response()->json(['success' => 'Sản phẩm đã được cập nhật thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi cập nhật sản phẩm.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
            }

            if ($product->ImageURL && Storage::disk('public')->exists($product->ImageURL)) {
                Storage::disk('public')->delete($product->ImageURL);
            }

            $product->delete();

            return response()->json(['success' => 'Sản phẩm đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa sản phẩm.'], 500);
        }
    }
}