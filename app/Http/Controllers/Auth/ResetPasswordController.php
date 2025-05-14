<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    /**
     * Hiển thị form đặt lại mật khẩu
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Xử lý đặt lại mật khẩu
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,Email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.exists' => 'Email không tồn tại trong hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');

        // Kiểm tra token
        $resetToken = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$resetToken || !Hash::check($token, $resetToken->token)) {
            return back()->withErrors(['email' => 'Token không hợp lệ hoặc đã hết hạn.']);
        }

        // Cập nhật mật khẩu
        $user = User::where('Email', $email)->first();
        if ($user) {
            // Vì trong model User đã có mutator cho password, nên không cần phải mã hóa lại
            $user->password = $password;
            $user->save();

            // Xóa token sau khi đặt lại mật khẩu
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return redirect()->route('login-register')->with('status', 'Mật khẩu đã được đặt lại thành công!');
        }

        return back()->withErrors(['email' => 'Không thể đặt lại mật khẩu.']);
    }
}