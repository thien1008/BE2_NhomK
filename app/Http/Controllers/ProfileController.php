<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
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
            return redirect()->route('profile')
                ->withErrors($validator)
                ->withInput();
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

            return redirect()->route('profile')
                ->with('success', 'Cập nhật thông tin thành công!');
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.']
                ], 500);
            }
            return redirect()->route('profile')
                ->withErrors(['general' => 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.'])
                ->withInput();
        }
    }
}