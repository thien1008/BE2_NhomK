<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Order;

class ProfileController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login-register');
        }
        $user = Auth::user();
        $orders = Order::getUserOrders($user->UserID);
        return view('profile', compact('user', 'orders'));
    }

    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login-register');
        }
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'FullName' => 'required|string|max:100',
            'Phone' => 'required|string|size:10|regex:/^[0-9]+$/|unique:users,Phone,' . $user->UserID . ',UserID',
        ], [
            'FullName.required' => 'Vui lòng nhập họ tên',
            'FullName.max' => 'Họ tên không được vượt quá 100 ký tự',
            'Phone.required' => 'Vui lòng nhập số điện thoại',
            'Phone.size' => 'Số điện thoại phải có đúng 10 số',
            'Phone.regex' => 'Số điện thoại chỉ được chứa số',
            'Phone.unique' => 'Số điện thoại đã được sử dụng',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $user->update([
                'FullName' => $request->FullName,
                'Phone' => $request->Phone,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công!'
                ]);
            }
            return back()->with('success', 'Cập nhật thông tin thành công!');
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.']
                ], 500);
            }
            return back()->withErrors(['general' => 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.'])->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login-register');
        }
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['current_password' => 'Mật khẩu hiện tại không đúng.']
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])->withInput();
        }

        try {
            $user->password = Hash::make($request->new_password);
            $user->save();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thay đổi mật khẩu thành công!'
                ]);
            }
            return back()->with('success', 'Thay đổi mật khẩu thành công!');
        } catch (\Exception $e) {
            \Log::error('Error updating password: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => 'Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.']
                ], 500);
            }
            return back()->withErrors(['general' => 'Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.'])->withInput();
        }
    }
}