<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

// Admin Controllers
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductDiscountController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home.redirect'); // Optional redirect to home
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::get('/products/search', [ProductController::class, 'search'])->name('product.search');
Route::get('/home/filter-products', [ProductController::class, 'filterHome'])->name('home.filter-products');
Route::get('/products/filter', [ProductController::class, 'filterProductsAjax'])->name('products.filter');
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('category.show');

// Authentication Routes
Route::get('/login-register', [AuthController::class, 'showLoginRegisterForm'])->name('login-register');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Social Auth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

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

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/get', [CartController::class, 'get'])->name('cart.get');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/order/details/{order_id}', [CheckoutController::class, 'showOrderDetails'])->name('order.details');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.change');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('categories', AdminCategoryController::class)->names('admin.categories');
    Route::resource('products', AdminProductController::class)->names('admin.products');
    Route::resource('orders', OrderController::class)->names('admin.orders');
    Route::resource('users', UserController::class)->names('admin.users');
    Route::resource('coupons', CouponController::class)->names('admin.coupons');
    Route::resource('product-discounts', ProductDiscountController::class)->names('admin.product_discounts');
});

Route::get('/product/{productId}/stock', [CartController::class, 'checkStock'])->middleware('auth');