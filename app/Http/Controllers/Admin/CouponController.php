<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $coupons = Coupon::where('Code', 'like', "%{$search}%")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.coupons.index', compact('coupons', 'search'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'Code' => 'required|string|max:50|unique:coupons',
                'DiscountPercentage' => 'required|numeric|min:0|max:100',
                'ValidFrom' => 'required|date',
                'ValidTo' => 'required|date|after_or_equal:ValidFrom',
                'UsageLimit' => 'nullable|integer|min:0',
                'UserLimit' => 'nullable|integer|min:0',
            ]);

            $data = $request->only([
                'Code',
                'DiscountPercentage',
                'ValidFrom',
                'ValidTo',
                'UsageLimit',
                'UserLimit'
            ]);
            $data['version'] = 1;

            Coupon::create($data);

            return response()->json(['success' => 'Mã giảm giá đã được tạo thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating coupon: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo mã giảm giá.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $coupon = Coupon::find($id);
            if (!$coupon) {
                return response()->json(['error' => 'Mã giảm giá không tồn tại.'], 404);
            }

            $validated = $request->validate([
                'Code' => 'required|string|max:50|unique:coupons,Code,' . $coupon->CouponID . ',CouponID',
                'DiscountPercentage' => 'required|numeric|min:0|max:100',
                'ValidFrom' => 'required|date',
                'ValidTo' => 'required|date|after_or_equal:ValidFrom',
                'UsageLimit' => 'nullable|integer|min:0',
                'UserLimit' => 'nullable|integer|min:0',
                'version' => 'required|integer|min:1',
            ]);

            if ($coupon->version != $request->version) {
                return response()->json(['error' => 'Dữ liệu mã giảm giá đã được thay đổi bởi người khác. Vui lòng làm mới trang và thử lại.'], 409);
            }

            $data = $request->only([
                'Code',
                'DiscountPercentage',
                'ValidFrom',
                'ValidTo',
                'UsageLimit',
                'UserLimit'
            ]);
            $data['version'] = $coupon->version + 1;

            $coupon->update($data);

            return response()->json(['success' => 'Mã giảm giá đã được cập nhật thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating coupon: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi cập nhật mã giảm giá.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $coupon = Coupon::find($id);
            if (!$coupon) {
                return response()->json(['error' => 'Mã giảm giá không tồn tại.'], 404);
            }

            $coupon->delete();

            return response()->json(['success' => 'Mã giảm giá đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting coupon: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa mã giảm giá.'], 500);
        }
    }
}