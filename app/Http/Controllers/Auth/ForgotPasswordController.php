<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\MailService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    /**
     * Hiển thị form quên mật khẩu
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Gửi email đặt lại mật khẩu
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,Email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.exists' => 'Email không tồn tại trong hệ thống.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = $request->input('email');
        $token = Str::random(60);

        // Lưu token vào bảng password_reset_tokens
        try {
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Error saving password reset token: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Không thể lưu token đặt lại mật khẩu.']);
        }

        // Tạo nội dung email với URL tuyệt đối
        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $email], true));
        $body = <<<HTML
            <h1>Đặt Lại Mật Khẩu</h1>
            <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
            <p><a href="$resetUrl">Nhấn vào đây để đặt lại mật khẩu</a></p>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
        HTML;

        // Gửi email bằng PHPMailer
        $mailService = new MailService();
        $sent = $mailService->send($email, 'Đặt Lại Mật Khẩu', $body, true);

        if ($sent) {
            return back()->with('status', 'Link đặt lại mật khẩu đã được gửi đến email của bạn!');
        } else {
            return back()->withErrors(['email' => 'Không thể gửi email đặt lại mật khẩu.']);
        }
    }
}