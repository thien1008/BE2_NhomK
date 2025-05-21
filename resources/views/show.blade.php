<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->ProductName }} - TPV E-COMMERCE</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="container">
        <h2>{{ $product->ProductName }}</h2>
        <img src="{{ asset('img/' . $product->ImageURL) }}" alt="{{ $product->ProductName }}" style="max-width: 300px;">
        <p>Giá: {{ number_format($product->Price, 0) }}₫</p>
        <p>Mô tả: {{ $product->Description }}</p>
        <p>Tồn kho: {{ $product->Stock }}</p>
        <a href="{{ route('home') }}">Quay lại trang chủ</a>
    </div>
</body>
</html>