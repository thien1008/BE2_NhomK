<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function showResetForm(Request $request, string $token)
    {
        $resetRecord = DB::table('password_reset_tokens')
                        ->where('token', $token)
                        ->first();
                        
        if (!$resetRecord || Carbon::parse($resetRecord->expires_at)->isPast()) {
            return redirect()->route('password.request')
                ->with('error', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $resetRecord->email]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $resetRecord = DB::table('password_reset_tokens')
                        ->where('token', $request->token)
                        ->where('email', $request->email)
                        ->first();

        if (!$resetRecord) {
            return back()->with('error', 'Token không hợp lệ.');
        }

        if (Carbon::parse($resetRecord->expires_at)->isPast()) {
            return redirect()->route('password.request')
                ->with('error', 'Token đã hết hạn. Vui lòng yêu cầu lại.');
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Không tìm thấy người dùng với email này.');
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Delete the used token
        DB::table('password_reset_tokens')->where('token', $request->token)->delete();

        return redirect()->route('login')
            ->with('status', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập bằng mật khẩu mới.');
    }
}