@extends('admin.layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Quản lý đơn hàng</h2>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Tìm theo tên khách hàng..." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="header-actions">
                <h5 class="mb-0">Danh sách đơn hàng</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                    <i class="bi bi-plus-circle"></i> Thêm đơn hàng
                </button>
            </div>
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
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
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
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                        data-id="{{ $order->OrderID }}"
                                        data-userid="{{ $order->UserID }}"
                                        data-username="{{ $order->user->FullName }}"
                                        data-price="{{ $order->TotalPrice }}"
                                        data-status="{{ $order->Status }}"
                                        data-bs-toggle="modal" data-bs-target="#editOrderModal">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng của khách hàng {{ $order->user->FullName }}?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addOrderModalLabel">Thêm đơn hàng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.orders.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-userid" class="form-label">Khách hàng</label>
                        <select class="form-select @error('UserID') is-invalid @enderror" id="add-userid" name="UserID" required>
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach (\App\Models\User::all() as $user)
                                <option value="{{ $user->UserID }}">{{ $user->FullName }}</option>
                            @endforeach
                        </select>
                        @error('UserID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-price" class="form-label">Tổng tiền</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('TotalPrice') is-invalid @enderror" id="add-price" name="TotalPrice" step="0.01" required>
                            <span class="input-group-text">VNĐ</span>
                            @error('TotalPrice')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-status" class="form-label">Trạng thái</label>
                        <select class="form-select @error('Status') is-invalid @enderror" id="add-status" name="Status" required>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        @error('Status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editOrderModalLabel">Cập nhật đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editOrderForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="OrderID" id="edit-orderid">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-userid" class="form-label">Khách hàng</label>
                        <select class="form-select" id="edit-userid" name="UserID" required>
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach (\App\Models\User::all() as $user)
                                <option value="{{ $user->UserID }}">{{ $user->FullName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-price" class="form-label">Tổng tiền</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="edit-price" name="TotalPrice" step="0.01" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-status" class="form-label">Trạng thái</label>
                        <select class="form-select" id="edit-status" name="Status" required>
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const userid = this.getAttribute('data-userid');
            const username = this.getAttribute('data-username');
            const price = this.getAttribute('data-price');
            const status = this.getAttribute('data-status');

            document.getElementById('edit-orderid').value = id;
            document.getElementById('edit-userid').value = userid;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-status').value = status;

            document.getElementById('editOrderForm').action = `/admin/orders/${id}`;
        });
    });
});
</script>
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
    .table-responsive {
        margin-bottom: 20px;
    }
    .actions {
        white-space: nowrap;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
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
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection