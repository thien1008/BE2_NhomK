@extends('layouts.app')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Add SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.userInfo = {
            FullName: '{{ Auth::user()->FullName ?? '' }}',
            email: '{{ Auth::user()->email ?? '' }}',
            Phone: '{{ Auth::user()->Phone ?? '' }}'
        };
        window.isLoggedIn = @json(Auth::check());
    </script>
    @vite(['resources/css/styles-checkout.css', 'resources/js/scripts-checkout.js'])
@endpush

@section('content')
<div class="loading" id="loadingOverlay" style="display: none;">
    <div class="spinner"></div>
</div>

<div class="checkout-container">
    <div class="checkout-header">
        <h1><i class="fas fa-credit-card"></i> Thanh Toán</h1>
        <div class="progress-bar">
            <div class="progress-step completed">
                <i class="fas fa-shopping-cart"></i>
                <span>Giỏ hàng</span>
            </div>
            <div class="progress-step active">
                <i class="fas fa-credit-card"></i>
                <span>Thanh toán</span>
            </div>
            <div class="progress-step">
                <i class="fas fa-check-circle"></i>
                <span>Hoàn tất</span>
            </div>
        </div>
    </div>

    <div class="checkout-content">
        <div class="checkout-form">
            <form id="checkoutForm" method="POST">
                @csrf
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Thông tin giao hàng
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName">Họ và tên *</label>
                            <input type="text" id="fullName" name="fullName" required value="{{ old('fullName', Auth::user()->FullName ?? '') }}">
                            @error('fullName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại *</label>
                            <input type="tel" id="phone" name="phone" required value="{{ old('phone', Auth::user()->Phone ?? '') }}">
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required value="{{ old('email', Auth::user()->email ?? '') }}">
                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="address">Địa chỉ *</label>
                        <textarea id="address" name="address" rows="3" required placeholder="Nhập địa chỉ chi tiết...">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-credit-card"></i> Phương thức thanh toán
                    </h3>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="cod" name="paymentMethod" value="cod" {{ old('paymentMethod', 'cod') == 'cod' ? 'checked' : '' }}>
                            <label for="cod">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Thanh toán khi nhận hàng</span>
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="bank" name="paymentMethod" value="bank" {{ old('paymentMethod') == 'bank' ? 'checked' : '' }}>
                            <label for="bank">
                                <i class="fas fa-university"></i>
                                <span>Chuyển khoản ngân hàng</span>
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="momo" name="paymentMethod" value="momo" {{ old('paymentMethod') == 'momo' ? 'checked' : '' }}>
                            <label for="momo">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Ví điện tử MoMo</span>
                            </label>
                        </div>
                    </div>
                    @error('paymentMethod')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-sticky-note"></i> Ghi chú đơn hàng
                    </h3>
                    <div class="form-group">
                        <textarea id="orderNotes" name="orderNotes" rows="3" placeholder="Ghi chú thêm cho đơn hàng (không bắt buộc)...">{{ old('orderNotes') }}</textarea>
                    </div>
                </div>
            </form>
        </div>

        <div class="order-summary">
            <h3 class="summary-title">Đơn hàng của bạn</h3>
            <div id="orderItems">
                @if($cartItems->isEmpty())
                    <p>Giỏ hàng của bạn đang trống.</p>
                @else
                    @foreach($cartItems as $item)
                        @if($item->ProductID && \App\Models\Product::where('ProductID', $item->ProductID)->exists())
                            <div class="order-item" data-id="{{ $item->ProductID }}">
                                <img src="{{ asset('images/' . $item->ImageURL) }}" alt="{{ $item->ProductName }}" class="item-image">
                                <div class="item-details">
                                    <div class="item-name">{{ $item->ProductName }}</div>
                                    <div class="item-quantity">Số lượng: {{ $item->Quantity }}</div>
                                    @if($item->Color)
                                        <div class="item-variant">Màu: {{ $item->Color }}</div>
                                    @endif
                                    @if($item->Memory)
                                        <div class="item-variant">Bộ nhớ: {{ $item->Memory }}</div>
                                    @endif
                                </div>
                                <div class="item-price">{{ number_format($item->CurrentPrice * $item->Quantity, 0, ',', '.') }} ₫</div>
                            </div>
                        @else
                            <p class="no-product-id">Sản phẩm không hợp lệ (ID: {{ $item->ProductID ?? 'null' }}).</p>
                        @endif
                    @endforeach
                @endif
            </div>

            <div class="coupon-section">
                <h4 style="margin-bottom: 15px;">Mã giảm giá</h4>
                <div class="coupon-input">
                    <input type="text" id="couponCode" placeholder="Nhập mã giảm giá" value="{{ old('couponCode') }}">
                    <button type="button" class="btn btn-secondary" onclick="applyCoupon()">Áp dụng</button>
                </div>
            </div>

            <div class="summary-totals">
                <div class="total-row">
                    <span>Tạm tính:</span>
                    <span id="subtotal">{{ number_format($subtotal, 0, ',', '.') }} ₫</span>
                </div>
                <div class="total-row">
                    <span>Phí vận chuyển:</span>
                    <span id="shippingFee">{{ number_format($shippingFee, 0, ',', '.') }} ₫</span>
                </div>
                <div class="total-row" id="discountRow" style="display: {{ $discount > 0 ? 'flex' : 'none' }};">
                    <span>Giảm giá:</span>
                    <span id="discountAmount" style="color: #e74c3c;">-{{ number_format($discount, 0, ',', '.') }} ₫</span>
                </div>
                <div class="total-row final">
                    <span>Tổng cộng:</span>
                    <span id="totalAmount">{{ number_format($total, 0, ',', '.') }} ₫</span>
                </div>
            </div>
        </div>

        <div class="checkout-actions">
            <button type="button" class="btn-back" onclick="goBack()">
                <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
            </button>

            @if(!$cartItems->isEmpty())
                <button type="button" class="btn-checkout" id="checkout-btn">
                    <i class="fas fa-lock"></i> Đặt hàng ngay
                </button>
            @endif
        </div>
    </div>
</div>

<script>
    function goBack() {
        window.history.back();
    }
</script>

@endsection