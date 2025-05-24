<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
// use Brian2694\Toastr\Facades\Toastr;
use Laravel\Socialite\Facades\Socialite;

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

             Toastr::success('Đăng ký thành công!', 'Thông báo');
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
            \Log::info('Login successful for user: ' . $user->FullName);

            // Toastr::success('Đăng nhập thành công!', 'Thông báo');

            if ($user->UserType === 'Admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công!');
            }
            return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
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

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('GoogleID', $googleUser->id)->orWhere('Email', $googleUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'FullName' => $googleUser->name,
                    'Email' => $googleUser->email,
                    'GoogleID' => $googleUser->id,
                    'UserType' => 'Regular',
                    'Phone' => null, // Phone is nullable in your schema
                    'password' => bcrypt(str_random(16)), // Generate a random password
                ]);
            } else {
                // Update GoogleID if the user exists via email but doesn't have GoogleID
                if (!$user->GoogleID) {
                    $user->update(['GoogleID' => $googleUser->id]);
                }
            }

            Auth::login($user, true);
            
            if ($user->UserType === 'Admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');

        } catch (\Exception $e) {
            \Log::error('Google login error: ' . $e->getMessage());
            return redirect()->route('login-register')
                ->withErrors(['login_error' => 'Có lỗi xảy ra khi đăng nhập bằng Google. Vui lòng thử lại.'])
                ->with('active_tab', 'login');
        }
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            
            $user = User::where('FacebookID', $facebookUser->id)->orWhere('Email', $facebookUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'FullName' => $facebookUser->name,
                    'Email' => $facebookUser->email,
                    'FacebookID' => $facebookUser->id,
                    'UserType' => 'Regular',
                    'Phone' => null,
                    'password' => bcrypt(str_random(16)),
                ]);
            } else {
                if (!$user->FacebookID) {
                    $user->update(['FacebookID' => $facebookUser->id]);
                }
            }

            Auth::login($user, true);
            
            if ($user->UserType === 'Admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');

        } catch (\Exception $e) {
            \Log::error('Facebook login error: ' . $e->getMessage());
            return redirect()->route('login-register')
                ->withErrors(['login_error' => 'Có lỗi xảy ra khi đăng nhập bằng Facebook. Vui lòng thử lại.'])
                ->with('active_tab', 'login');
        }
    }
}