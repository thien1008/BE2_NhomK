@extends('layouts.app')

@section('title', 'Thanh toán - ' . config('app.name'))

@section('content')
    <div class="container">
        <h1>Thanh toán</h1>
        <p>Chức năng thanh toán đang được phát triển.</p>
        <a href="{{ route('cart.index') }}">Quay lại giỏ hàng</a>
    </div>
@endsection