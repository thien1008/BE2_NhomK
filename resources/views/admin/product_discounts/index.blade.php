@extends('admin.layouts.app')

@section('title', 'Quản lý giảm giá sản phẩm')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Quản lý giảm giá sản phẩm</h2>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm sản phẩm..." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Discounts List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="header-actions">
                <h5 class="mb-0">Danh sách giảm giá sản phẩm</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductDiscountModal">
                    <i class="bi bi-plus-circle"></i> Thêm giảm giá
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Sản phẩm</th>
                            <th>Phần trăm giảm</th>
                            <th>Bắt đầu</th>
                            <th>Kết thúc</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productDiscounts as $discount)
                        <tr>
                            <td>{{ $discount->DiscountID }}</td>
                            <td><strong>{{ $discount->product->ProductName }}</strong></td>
                            <td>{{ $discount->DiscountPercentage }}%</td>
                            <td>{{ $discount->StartDate }}</td>
                            <td>{{ $discount->EndDate }}</td>
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                        data-id="{{ $discount->DiscountID }}"
                                        data-product-id="{{ $discount->ProductID }}"
                                        data-discount="{{ $discount->DiscountPercentage }}"
                                        data-start-date="{{ $discount->StartDate }}"
                                        data-end-date="{{ $discount->EndDate }}"
                                        data-version="{{ $discount->version }}"
                                        data-bs-toggle="modal" data-bs-target="#editProductDiscountModal">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                        data-id="{{ $discount->DiscountID }}"
                                        data-product-name="{{ $discount->product->ProductName }}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $productDiscounts->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Add Product Discount Modal -->
<div class="modal fade" id="addProductDiscountModal" tabindex="-1" aria-labelledby="addProductDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addProductDiscountModalLabel">Thêm giảm giá mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProductDiscountForm" action="{{ route('admin.product_discounts.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-product-id" class="form-label">Sản phẩm</label>
                        <select class="form-control @error('ProductID') is-invalid @enderror" id="add-product-id" name="ProductID" required>
                            @foreach (\App\Models\Product::all() as $product)
                                <option value="{{ $product->ProductID }}">{{ $product->ProductName }}</option>
                            @endforeach
                        </select>
                        @error('ProductID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-discount" class="form-label">Phần trăm giảm</label>
                        <input type="number" class="form-control @error('DiscountPercentage') is-invalid @enderror" id="add-discount" name="DiscountPercentage" min="0" max="100" required>
                        @error('DiscountPercentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-start-date" class="form-label">Bắt đầu</label>
                        <input type="date" class="form-control @error('StartDate') is-invalid @enderror" id="add-start-date" name="StartDate" required>
                        @error('StartDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-end-date" class="form-label">Kết thúc</label>
                        <input type="date" class="form-control @error('EndDate') is-invalid @enderror" id="add-end-date" name="EndDate" required>
                        @error('EndDate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="addProductDiscountSubmit">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Discount Modal -->
<div class="modal fade" id="editProductDiscountModal" tabindex="-1" aria-labelledby="editProductDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editProductDiscountModalLabel">Cập nhật giảm giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductDiscountForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="DiscountID" id="edit-discountid">
                <input type="hidden" name="version" id="edit-version">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-product-id" class="form-label">Sản phẩm</label>
                        <select class="form-control" id="edit-product-id" name="ProductID" required>
                            @foreach (\App\Models\Product::all() as $product)
                                <option value="{{ $product->ProductID }}">{{ $product->ProductName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-discount" class="form-label">Phần trăm giảm</label>
                        <input type="number" class="form-control" id="edit-discount" name="DiscountPercentage" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-start-date" class="form-label">Bắt đầu</label>
                        <input type="date" class="form-control" id="edit-start-date" name="StartDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-end-date" class="form-label">Kết thúc</label>
                        <input type="date" class="form-control" id="edit-end-date" name="EndDate" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning" id="editProductDiscountSubmit">Cập nhật</button>
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
    console.log('Product discount management script loaded');

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
            const productId = this.getAttribute('data-product-id');
            const discount = this.getAttribute('data-discount');
            const startDate = this.getAttribute('data-start-date');
            const endDate = this.getAttribute('data-end-date');
            const version = this.getAttribute('data-version');

            document.getElementById('edit-discountid').value = id;
            document.getElementById('edit-product-id').value = productId;
            document.getElementById('edit-discount').value = discount;
            document.getElementById('edit-start-date').value = startDate;
            document.getElementById('edit-end-date').value = endDate;
            document.getElementById('edit-version').value = version;

            document.getElementById('editProductDiscountForm').action = `/admin/product-discounts/${id}`;
        });
    });

    // Add form submission handler
    const addForm = document.getElementById('addProductDiscountForm');
    const addSubmitBtn = document.getElementById('addProductDiscountSubmit');
    const addModalEl = document.getElementById('addProductDiscountModal');
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
                    const errorMessages = Object.values(data.errors).flat().join(' ');
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

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi thêm giảm giá sản phẩm: ' + error.message;
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            });
        });
    } else {
        console.error('Add form not found');
    }

    // Edit form submission handler
    const editForm = document.getElementById('editProductDiscountForm');
    const editSubmitBtn = document.getElementById('editProductDiscountSubmit');
    const editModalEl = document.getElementById('editProductDiscountModal');
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
                    const errorMessages = Object.values(data.errors).flat().join(' ');
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

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi cập nhật giảm giá sản phẩm: ' + error.message;
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
            const productName = this.getAttribute('data-product-name');

            if (confirm(`Bạn có chắc chắn muốn xóa giảm giá cho sản phẩm ${productName}?`)) {
                if (isSubmitting) {
                    console.log('Delete blocked: already in progress');
                    return;
                }

                isSubmitting = true;

                fetch(`/admin/product-discounts/${id}`, {
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
                    document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi xóa giảm giá sản phẩm: ' + error.message;
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
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection