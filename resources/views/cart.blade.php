@extends('layouts.app')

@section('title', 'Giỏ hàng - ' . config('app.name'))

@section('content')
    <div class="container">
        <h1>Giỏ hàng</h1>
        @if($cartItems->isEmpty())
            <p>Giỏ hàng của bạn đang trống.</p>
        @else
            @foreach($cartItems as $item)
                <div class="cart-item" data-product-id="{{ $item->ProductID }}">
                    <img src="{{ asset('img/' . $item->product->ImageURL) }}" alt="{{ htmlspecialchars($item->product->ProductName) }}" class="cart-item-image">
                    <div class="cart-item-details">
                        <div class="cart-item-name">{{ htmlspecialchars($item->product->ProductName) }}</div>
                        <div class="cart-item-price">
                            @php
                                $discount = $item->product->discounts()->where('StartDate', '<=', now())->where('EndDate', '>=', now())->first();
                                $price = $discount ? $item->product->Price * (1 - $discount->DiscountPercentage / 100) : $item->product->Price;
                            @endphp
                            {{ number_format($price * $item->Quantity, 0) }}₫
                        </div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn decrease" data-product-id="{{ $item->ProductID }}">-</button>
                            <input type="number" class="quantity-input" value="{{ $item->Quantity }}" data-product-id="{{ $item->ProductID }}" min="1">
                            <button class="quantity-btn increase" data-product-id="{{ $item->ProductID }}">+</button>
                            <a href="#" class="cart-item-remove" data-product-id="{{ $item->ProductID }}">Xóa</a>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="cart-total">
                <span>Tổng cộng:</span>
                <span>{{ number_format($total, 0) }}₫</span>
            </div>
            <a href="{{ route('cart.checkout') }}" class="cart-checkout-btn">Thanh toán</a>
        @endif
        <a href="{{ route('home') }}">Quay lại</a>
    </div>
@endsection