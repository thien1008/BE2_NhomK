<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        try {
            $validated = $request->validate([
                'CategoryName' => 'required|string|max:255|unique:categories,CategoryName',
                'Description' => 'nullable|string',
            ]);

            Category::create(array_merge($validated, ['version' => 1]));

            return response()->json(['success' => 'Danh mục đã được tạo thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo danh mục.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['error' => 'Danh mục không tồn tại.'], 404);
            }

            $validated = $request->validate([
                'CategoryName' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'CategoryName')->ignore($id, 'CategoryID'),
                ],
                'Description' => 'nullable|string',
                'version' => 'required|integer|min:1',
            ]);

            if ($category->version != $request->version) {
                return response()->json(['error' => 'Dữ liệu danh mục đã được thay đổi bởi người khác. Vui lòng làm mới trang và thử lại.'], 409);
            }

            $category->update(array_merge(
                $validated,
                ['version' => $category->version + 1]
            ));

            return response()->json(['success' => 'Danh mục đã được cập nhật thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi cập nhật danh mục.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['error' => 'Danh mục không tồn tại.'], 404);
            }

            $category->delete();

            return response()->json(['success' => 'Danh mục đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa danh mục.'], 500);
        }
    }
}