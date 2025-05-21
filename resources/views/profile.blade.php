<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - TPV E-COMMERCE</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="container">
        <h2>Thông tin cá nhân</h2>
        <p>Họ và tên: {{ $user->name }}</p>
        <p>Email: {{ $user->email }}</p>
        <a href="{{ route('home') }}">Quay lại trang chủ</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Đăng xuất</button>
        </form>
    </div>
</body>
</html>