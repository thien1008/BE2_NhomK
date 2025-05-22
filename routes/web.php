<?php
// routes/web.php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// admin
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductDiscountController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Auth routes
Route::get('/login-register', [AuthController::class, 'showLoginRegisterForm'])->name('login-register');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Tạm thời để get logout để test
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Các route yêu cầu đăng nhập
// Route::middleware(['auth'])->group(function () {
//     Route::get('/home', [HomeController::class, 'index'])->name('home');
// });

// Route::get('/admin', function () {
//     return view('admin.layouts.app');
// });



// Admin
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    })->name('admin');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('categories', CategoryController::class)->names('admin.categories');
    Route::resource('products', ProductController::class)->names('admin.products');
    Route::resource('orders', OrderController::class)->names('admin.orders');
    Route::resource('users', UserController::class)->names('admin.users');
    Route::resource('coupons', CouponController::class)->names('admin.coupons');
    Route::resource('product-discounts', ProductDiscountController::class)->names('admin.product_discounts');
});

// require __DIR__.'/auth.php';


// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
