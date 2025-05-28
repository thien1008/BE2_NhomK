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
                                        'Pending' => 'bg-warning',
                                        'Completed' => 'bg-success',
                                        'Cancelled' => 'bg-danger',
                                        default => '',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} status">{{ $order->Status }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($order->CreatedAt)->format('d/m/Y H:i') }}</td>
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                        data-id="{{ $order->OrderID }}"
                                        data-userid="{{ $order->UserID }}"
                                        data-username="{{ $order->user->FullName }}"
                                        data-price="{{ $order->TotalPrice }}"
                                        data-status="{{ $order->Status }}"
                                        data-version="{{ $order->version }}"
                                        data-bs-toggle="modal" data-bs-target="#editOrderModal">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                        data-id="{{ $order->OrderID }}"
                                        data-username="{{ $order->user->FullName }}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $orders->links('pagination::bootstrap-5') }}
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
            <form id="addOrderForm" action="{{ route('admin.orders.store') }}">
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
                    <button type="submit" class="btn btn-success" id="addOrderSubmit">Thêm mới</button>
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
            <form id="editOrderForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="OrderID" id="edit-orderid">
                <input type="hidden" name="version" id="edit-version">
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
                    <button type="submit" class="btn btn-warning" id="editOrderSubmit">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">Lỗi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="errorMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Thành công</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Order management script loaded');

    let isSubmitting = false;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('CSRF token not found');
    }

    // Edit button handler
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Edit button clicked');
            const id = this.getAttribute('data-id');
            const userid = this.getAttribute('data-userid');
            const price = this.getAttribute('data-price');
            const status = this.getAttribute('data-status');
            const version = this.getAttribute('data-version');

            document.getElementById('edit-orderid').value = id;
            document.getElementById('edit-userid').value = userid;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-status').value = status;
            document.getElementById('edit-version').value = version;

            document.getElementById('editOrderForm').action = `/admin/orders/${id}`;
        });
    });

    // Add form submission handler
    const addForm = document.getElementById('addOrderForm');
    const addSubmitBtn = document.getElementById('addOrderSubmit');
    const addModalEl = document.getElementById('addOrderModal');
    const addModal = bootstrap.Modal.getOrCreateInstance(addModalEl);

    if (addForm) {
        addForm.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            console.log('Add form submitted');

            if (isSubmitting) {
                console.log('Submission blocked: already in progress');
                return;
            }

            isSubmitting = true;
            addSubmitBtn.disabled = true;
            addSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                console.log('Add response status:', response.status);
                return response.json().then(data => ({ status: response.status, data }));
            })
            .then(({ status, data }) => {
                isSubmitting = false;
                addSubmitBtn.disabled = false;
                addSubmitBtn.innerHTML = 'Thêm mới';

                if (status === 200 && data.success) {
                    console.log('Add success:', data.success);
                    addForm.reset();
                    addModal.hide();

                    document.getElementById('successMessage').textContent = data.success;
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    document.getElementById('successModal').addEventListener('hidden.bs.modal', function() {
                        window.location.reload();
                    }, { once: true });
                } else if (status === 422 && data.errors) {
                    console.log('Add validation errors:', data.errors);
                    const errorMessages = Object.values(data.errors).flat().join('. ');
                    document.getElementById('errorMessage').textContent = errorMessages;
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                } else {
                    throw new Error('Unexpected response: ' + JSON.stringify(data));
                }
            })
            .catch(error => {
                isSubmitting = false;
                addSubmitBtn.disabled = false;
                addSubmitBtn.innerHTML = 'Thêm mới';
                console.error('Add error:', error);

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi thêm đơn hàng: ' + error.message;
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            });
        });
    } else {
        console.error('Add form not found');
    }

    // Edit form submission handler
    const editForm = document.getElementById('editOrderForm');
    const editSubmitBtn = document.getElementById('editOrderSubmit');
    const editModalEl = document.getElementById('editOrderModal');
    const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);

    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            console.log('Edit form submitted');

            if (isSubmitting) {
                console.log('Submission blocked: already in progress');
                return;
            }

            isSubmitting = true;
            editSubmitBtn.disabled = true;
            editSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                console.log('Edit response status:', response.status);
                return response.json().then(data => ({ status: response.status, data }));
            })
            .then(({ status, data }) => {
                isSubmitting = false;
                editSubmitBtn.disabled = false;
                editSubmitBtn.innerHTML = 'Cập nhật';

                if (status === 200 && data.success) {
                    console.log('Edit success:', data.success);
                    editForm.reset();
                    editModal.hide();

                    document.getElementById('successMessage').textContent = data.success;
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    document.getElementById('successModal').addEventListener('hidden.bs.modal', function() {
                        window.location.reload();
                    }, { once: true });
                } else if (status === 404 && data.error) {
                    console.log('Edit not found:', data.error);
                    editForm.reset();
                    editModal.hide();

                    document.getElementById('errorMessage').textContent = data.error;
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
                        window.location.reload();
                    }, { once: true });
                } else if (status === 409 && data.error) {
                    console.log('Edit conflict:', data.error);
                    editForm.reset();
                    editModal.hide();

                    document.getElementById('errorMessage').textContent = data.error;
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
                        window.location.reload();
                    }, { once: true });
                } else if (status === 422 && data.errors) {
                    console.log('Edit validation errors:', data.errors);
                    const errorMessages = Object.values(data.errors).flat().join('. ');
                    document.getElementById('errorMessage').textContent = errorMessages;
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                } else {
                    throw new Error('Unexpected response: ' + JSON.stringify(data));
                }
            })
            .catch(error => {
                isSubmitting = false;
                editSubmitBtn.disabled = false;
                editSubmitBtn.innerHTML = 'Cập nhật';
                console.error('Edit error:', error);

                editForm.reset();
                editModal.hide();

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi cập nhật đơn hàng: ' + error.message;
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
                document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
                    window.location.reload();
                }, { once: true });
            });
        });
    } else {
        console.error('Edit form not found');
    }

    // Delete button handler
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Delete button clicked');
            const id = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');

            if (confirm(`Bạn có chắc chắn muốn xóa đơn hàng của khách hàng ${username}?`)) {
                if (isSubmitting) {
                    console.log('Delete blocked: already in progress');
                    return;
                }

                isSubmitting = true;

                fetch(`/admin/orders/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Delete response status:', response.status);
                    return response.json().then(data => ({ status: response.status, data }));
                })
                .then(({ status, data }) => {
                    isSubmitting = false;

                    if (status === 200 && data.success) {
                        console.log('Delete success:', data.success);
                        document.getElementById('successMessage').textContent = data.success;
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                        document.getElementById('successModal').addEventListener('hidden.bs.modal', function() {
                            window.location.reload();
                        }, { once: true });
                    } else if (status === 404 && data.error) {
                        console.log('Delete not found:', data.error);
                        document.getElementById('errorMessage').textContent = data.error;
                        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                        errorModal.show();
                        document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
                            window.location.reload();
                        }, { once: true });
                    } else {
                        throw new Error('Unexpected response: ' + JSON.stringify(data));
                    }
                })
                .catch(error => {
                    isSubmitting = false;
                    console.error('Delete error:', error);
                    document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi xóa đơn hàng: ' + error.message;
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
                        window.location.reload();
                    }, { once: true });
                });
            }
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
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection