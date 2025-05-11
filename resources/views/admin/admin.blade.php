<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Admin Dashboard</title>
    
    <!-- Có thể sử dụng Vite với Laravel để quản lý CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        nav {
            width: 220px;
            background-color: #2c3e50;
            color: white;
            padding-top: 30px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0; /* Ngăn sidebar co lại */
        }

        nav a {
            background: none;
            border: none; 
            color: white;
            padding: 15px 20px;
            text-align: left;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            display: block;
        }

        nav a:hover, nav a.active {
            background-color: #34495e;
        }

        header {
            position: absolute;
            top: 0;
            left: 220px;
            width: calc(100% - 220px);
            height: 60px;
            background-color: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            font-size: 20px;
            font-weight: bold;
            z-index: 10;
        }

        .content {
            position: absolute;
            top: 60px;
            left: 220px;
            width: calc(100% - 220px);
            height: calc(100% - 60px);
            overflow: auto;
            padding: 20px;
        }
        
        .user-info {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
    
    @stack('styles')
</head>
<body>

    <nav>
        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Quản lý danh mục</a>
        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Quản lý sản phẩm</a>
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Quản lý đơn hàng</a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Quản lý người dùng</a>
    </nav>

    <header>
        <div>Trang Admin</div>
        <div class="user-info">
            <span>Xin chào, {{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Đăng xuất</button>
            </form>
        </div>
    </header>

    <div class="content">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>