<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;

class ForgotPasswordController extends Controller
{
    /**
     * Show the form to request a password reset link.
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the user's email.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('status', 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được email hướng dẫn đặt lại mật khẩu.');
        }

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Create token
        $token = Str::random(64);
        $expires_at = Carbon::now()->addHour();

        // Store token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'expires_at' => $expires_at,
            'created_at' => Carbon::now()
        ]);

        // Send email with reset link
        $user->notify(new ResetPasswordNotification($token));

        return back()->with('status', 'Vui lòng kiểm tra email của bạn để nhận hướng dẫn đặt lại mật khẩu.');
    }
}