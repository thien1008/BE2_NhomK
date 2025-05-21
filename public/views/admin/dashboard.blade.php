<!-- resources/views/admin/dashboard.blade.php -->
@extends('admin.layouts.app')

@section('title', 'Tổng quan - Admin Dashboard')

@section('content')
    <div class="admin-container">
        <h1>Tổng quan</h1>
        <p>Chào mừng đến với bảng điều khiển quản trị của TPV E-COMMERCE.</p>
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Sản phẩm</h3>
                <p>{{ \App\Models\Product::count() }}</p>
            </div>
            <div class="stat-box">
                <h3>Danh mục</h3>
                <p>{{ \App\Models\Category::count() }}</p>
            </div>
            <div class="stat-box">
                <h3>Đơn hàng</h3>
                <p>{{ \App\Models\Order::count() }}</p>
            </div>
            <div class="stat-box">
                <h3>Người dùng</h3>
                <p>{{ \App\Models\User::count() }}</p>
            </div>
        </div>
    </div>
@endsection