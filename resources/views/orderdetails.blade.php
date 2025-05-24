@extends('layouts.app')

@section('content')
<div class="order-details-container">
    <h1>Chi tiết đơn hàng #{{ $order->id }}</h1>
    <div class="order-info">
        <h3>Thông tin khách hàng</h3>
        <p><strong>Họ và tên:</strong> {{ $order->full_name }}</p>
        <p><strong>Số điện thoại:</strong> {{ $order->phone }}</p>
        <p><strong>Email:</strong> {{ $order->email }}</p>
        <p><strong>Địa chỉ:</strong> {{ $order->address }}, {{ $order->district }}, {{ $order->province }}</p>
        <p><strong>Phương thức thanh toán:</strong> 
            @switch($order->payment_method)
                @case('cod')
                    Thanh toán khi nhận hàng
                    @break
                @case('bank')
                    Chuyển khoản ngân hàng
                    @break
                @case('momo')
                    Ví điện tử MoMo
                    @break
                @default
                    Không xác định
            @endswitch
        </p>
        @if($order->notes)
            <p><strong>Ghi chú:</strong> {{ $order->notes }}</p>
        @endif
        <p><strong>Trạng thái:</strong> 
            @switch($order->status)
                @case('pending')
                    Đang xử lý
                    @break
                @default
                    {{ $order->status }}
            @endswitch
        </p>
    </div>

    <div class="order-items">
        <h3>Sản phẩm</h3>
        @if($order->items->isEmpty())
            <p>Không có sản phẩm trong đơn hàng.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->ProductName ?? 'Sản phẩm không tồn tại' }}</td>
                            <td>{{ $item->Quantity }}</td>
                            <td>{{ number_format($item->Price, 0, ',', '.') }} ₫</td>
                            <td>{{ number_format($item->Price * $item->Quantity, 0, ',', '.') }} ₫</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="order-summary">
        <h3>Tổng cộng</h3>
        <p><strong>Tạm tính:</strong> {{ number_format($order->total, 0, ',', '.') }} ₫</p>
        <!-- Nếu có phí vận chuyển hoặc giảm giá, bạn có thể thêm ở đây -->
    </div>

    <div class="order-actions">
        <a href="{{ route('home') }}" class="btn btn-primary">Quay lại trang chủ</a>
        <a href="{{ route('cart') }}" class="btn btn-secondary">Xem giỏ hàng</a>
    </div>
</div>

<style>
.order-details-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.order-info, .order-items, .order-summary {
    margin-bottom: 20px;
}

.order-info p, .order-summary p {
    margin: 5px 0;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.table th {
    background: #f8f8f8;
}

.order-actions {
    text-align: right;
}

.btn {
    padding: 10px 20px;
    margin-left: 10px;
    text-decoration: none;
    border-radius: 5px;
}

.btn-primary {
    background: #007bff;
    color: #fff;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
}
</style>
@endsection