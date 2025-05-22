<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Thêm thẻ meta CSRF -->
    <title>TPV E-COMMERCE</title>
    @stack('head') <!-- Đảm bảo render các thẻ meta và tài nguyên từ home.blade.php, cart.blade.php -->
    @vite(['resources/css/styles-home.css', 'resources/js/scripts-home.js', 'resources/js/cart-shared.js'])
</head>
<body>
    @yield('content')
</body>
</html>