<?php
// routes/web.php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

// admin
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;

// Auth routes
Route::get('/login-register', [AuthController::class, 'showLoginRegisterForm'])->name('login-register');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Các route yêu cầu đăng nhập
Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');
});

Route::get('/admin', function () {
    return view('admin.admin');
});

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('categories', CategoryController::class)->names('admin.categories');
    Route::resource('products', ProductController::class)->names('admin.products');
    Route::resource('orders', OrderController::class)->names('admin.orders');
    Route::resource('users', UserController::class)->names('admin.users');
});

require __DIR__.'/auth.php';


// // Các route dành cho Admin
// Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
//     // Dashboard
//     Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
//     // Categories
//     Route::resource('categories', CategoryController::class);
    
//     // Products
//     Route::resource('products', ProductController::class);
    
//     // Orders
//     Route::resource('orders', OrderController::class);
    
//     // Users
//     Route::resource('users', UserController::class);
// });

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