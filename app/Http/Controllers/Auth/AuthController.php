<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginRegisterForm()
    {
        return view('auth.login-register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,Email',
            'phone' => 'required|string|size:10|regex:/^[0-9]+$/|unique:users,Phone',
            'password' => 'required|string|min:8', // Updated to min:8
        ], [
            'name.required' => 'Vui lòng nhập họ tên',
            'name.max' => 'Họ tên không được vượt quá 100 ký tự',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã được sử dụng',
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.size' => 'Số điện thoại phải có đúng 10 số',
            'phone.regex' => 'Số điện thoại chỉ được chứa số',
            'phone.unique' => 'Số điện thoại đã được sử dụng',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }
            return redirect()->route('login-register')
                ->withErrors($validator, 'register')
                ->withInput()
                ->with('active_tab', 'register');
        }

        try {
            User::create([
                'FullName' => $request->name,
                'Email' => $request->email,
                'Phone' => $request->phone,
                'password' => $request->password,
                'UserType' => 'Regular',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating user: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.']
                ], 500);
            }
            return redirect()->route('login-register')
                ->withErrors(['register_error' => 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.'])
                ->withInput()
                ->with('active_tab', 'register');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công!'
            ]);
        }

        return redirect()->route('login-register')
            ->with('register_success', 'Đăng ký thành công!')
            ->with('active_tab', 'login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_input' => 'required|string',
            'password' => 'required|string',
        ], [
            'login_input.required' => 'Vui lòng nhập email hoặc số điện thoại',
            'password.required' => 'Vui lòng nhập mật khẩu',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login-register')
                ->withErrors($validator, 'login')
                ->withInput();
        }

        $loginField = filter_var($request->login_input, FILTER_VALIDATE_EMAIL) ? 'Email' : 'Phone';
        
        $credentials = [
            $loginField => $request->login_input,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials, $request->has('checkbox'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            if ($user->UserType === 'Admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        return redirect()->route('login-register')
            ->withErrors([
                'login_error' => 'Sai thông tin đăng nhập hoặc mật khẩu!',
            ])
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login-register');
    }
}