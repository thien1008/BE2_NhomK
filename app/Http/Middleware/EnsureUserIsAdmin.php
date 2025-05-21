<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login-register')->with('error', 'Vui lòng đăng nhập để truy cập khu vực quản trị.');
        }

        if (Auth::user()->UserType !== 'Admin') {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập khu vực quản trị.');
        }

        return $next($request);
    }
}