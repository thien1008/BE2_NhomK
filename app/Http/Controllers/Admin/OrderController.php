<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $orders = Order::with('user')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('FullName', 'like', "%{$search}%");
            })
            ->paginate(10);

        return view('admin.orders.index', compact('orders', 'search'));
    }

    // Deprecated: Using modal instead
    /*
    public function create()
    {
        $users = User::all();
        return view('admin.orders.create', compact('users'));
    }
    */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'UserID' => 'required|exists:users,UserID',
                'TotalPrice' => 'required|numeric|min:0',
                'Status' => 'required|in:Pending,Completed,Cancelled',
            ]);

            $data = $request->only(['UserID', 'TotalPrice', 'Status']);
            $data['version'] = 1;

            Order::create($data);

            return response()->json(['success' => 'Đơn hàng đã được tạo thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo đơn hàng.'], 500);
        }
    }

    // Deprecated: Using modal instead
    /*
    public function edit(Order $order)
    {
        $users = User::all();
        return view('admin.orders.edit', compact('order', 'users'));
    }
    */

    public function update(Request $request, $id)
    {
        try {
            $order = Order::find($id);
            if (!$order) {
                return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
            }

            $validated = $request->validate([
                'UserID' => 'required|exists:users,UserID',
                'TotalPrice' => 'required|numeric|min:0',
                'Status' => 'required|in:Pending,Completed,Cancelled',
                'version' => 'required|integer|min:1',
            ]);

            if ($order->version != $request->version) {
                return response()->json(['error' => 'Dữ liệu đơn hàng đã được thay đổi bởi người khác. Vui lòng làm mới trang và thử lại.'], 409);
            }

            $data = $request->only(['UserID', 'TotalPrice', 'Status']);
            $data['version'] = $order->version + 1;

            $order->update($data);

            return response()->json(['success' => 'Đơn hàng đã được cập nhật thành công.'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi cập nhật đơn hàng.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $order = Order::find($id);
            if (!$order) {
                return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
            }

            $order->delete();

            return response()->json(['success' => 'Đơn hàng đã được xóa thành công.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xóa đơn hàng.'], 500);
        }
    }
}