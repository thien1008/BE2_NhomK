@extends('admin.layouts.app')

@section('title', 'Tổng quan')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Tổng quan</h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-tags-fill" style="font-size: 2rem; margin-right: 10px;"></i>
                        <div>
                            <h5 class="card-title">Danh mục</h5>
                            <h3 class="card-text">{{ $stats['categories'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-box-seam-fill" style="font-size: 2rem; margin-right: 10px;"></i>
                        <div>
                            <h5 class="card-title">Sản phẩm</h5>
                            <h3 class="card-text">{{ $stats['products'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cart-fill" style="font-size: 2rem; margin-right: 10px;"></i>
                        <div>
                            <h5 class="card-title">Đơn hàng</h5>
                            <h3 class="card-text">{{ $stats['orders'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-people-fill" style="font-size: 2rem; margin-right: 10px;"></i>
                        <div>
                            <h5 class="card-title">Người dùng</h5>
                            <h3 class="card-text">{{ $stats['users'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Đơn hàng gần đây</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->OrderID }}</td>
                            <td>{{ $order->user->FullName }}</td>
                            <td class="price">{{ number_format($order->TotalPrice, 0, ',', '.') }} đ</td>
                            <td>
                                @php
                                    $statusClass = match ($order->Status) {
                                        'Pending' => 'status-pending bg-warning',
                                        'Completed' => 'status-completed bg-success',
                                        'Cancelled' => 'status-cancelled bg-danger',
                                        default => '',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} status">{{ $order->Status }}</span>
                            </td>
                            <td>{{ $order->CreatedAt }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có đơn hàng nào gần đây.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .page-title {
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    .card {
        margin-bottom: 20px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-body {
        padding: 1.5rem;
    }
    .price {
        font-weight: bold;
        color: #e74c3c;
    }
    .status {
        font-weight: bold;
    }
    .status-pending {
        color: #f39c12;
    }
    .status-completed {
        color: #27ae60;
    }
    .status-cancelled {
        color: #e74c3c;
    }
</style>
@endsection