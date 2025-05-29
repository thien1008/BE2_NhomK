<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductDiscount;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductDiscountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $productDiscounts = ProductDiscount::with('product')
            ->whereHas('product', function ($query) use ($search) {
                $query->where('ProductName', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.product_discounts.index', compact('productDiscounts', 'search'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ProductID' => 'required|exists:products,ProductID',
                'DiscountPercentage' => 'required|numeric|min:0|max:100',
                'StartDate' => 'required|date',
                'EndDate' => 'required|date|after_or_equal:StartDate',
            ]);

            $data = $request->only([
                'ProductID',
                'DiscountPercentage',
                'StartDate',
                'EndDate'
            ]);
            $data['version'] = 1;

            ProductDiscount::create($data);

            return response()->json(['success' => 'Giảm giá sản phẩm đã được tạo thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating product discount: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo giảm giá sản phẩm.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $productDiscount = ProductDiscount::find($id);
            if (!$productDiscount) {
                return response()->json(['error' => 'Giảm giá sản phẩm không tồn tại.'], 404);
            }

            $validated = $request->validate([
                'ProductID' => 'required|exists:products,ProductID',
                'DiscountPercentage' => 'required|numeric|min:0|max:100',
                'StartDate' => 'required|date',
                'EndDate' => 'required|date|after_or_equal:StartDate',
                'version' => 'required|integer|min:1',
            ]);

            if ($productDiscount->version != $request->version) {
                return response()->json(['error' => 'Dữ liệu giảm giá sản phẩm đã được thay đổi bởi người khác. Vui lòng làm mới trang và thử lại.'], 409);
            }

            $data = $request->only([
                'ProductID',
                'DiscountPercentage',
                'StartDate',
                'EndDate'
            ]);
            $data['version'] = $productDiscount->version + 1;

            $productDiscount->update($data);

            return response()->json(['success' => 'Giảm giá sản phẩm đã được cập nhật thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating product discount: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi cập nhật giảm giá sản phẩm.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $productDiscount = ProductDiscount::find($id);
            if (!$productDiscount) {
                return response()->json(['error' => 'Giảm giá sản phẩm không tồn tại.'], 404);
            }

            $productDiscount->delete();

            return response()->json(['success' => 'Giảm giá sản phẩm đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting product discount: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa giảm giá sản phẩm.'], 500);
        }
    }
}