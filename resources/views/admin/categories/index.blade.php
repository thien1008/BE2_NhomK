@extends('admin.layouts.app')

@section('title', 'Quản lý danh mục')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Quản lý danh mục</h2>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm danh mục..." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="header-actions">
                <h5 class="mb-0">Danh sách danh mục</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle"></i> Thêm danh mục
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->CategoryID }}</td>
                            <td><strong>{{ $category->CategoryName }}</strong></td>
                            <td class="category-desc">{{ $category->Description }}</td>
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                        data-id="{{ $category->CategoryID }}"
                                        data-name="{{ $category->CategoryName }}"
                                        data-desc="{{ $category->Description }}"
                                        data-version="{{ $category->version }}"
                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                        data-id="{{ $category->CategoryID }}"
                                        data-name="{{ $category->CategoryName }}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addCategoryModalLabel">Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-categoryname" class="form-label">Tên danh mục</label>
                        <input type="text" class="form-control @error('CategoryName') is-invalid @enderror" id="add-categoryname" name="CategoryName" required>
                        @error('CategoryName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="add-description" name="Description" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="addCategorySubmit">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editCategoryModalLabel">Cập nhật danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="CategoryID" id="edit-categoryid">
                <input type="hidden" name="version" id="edit-version">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-categoryname" class="form-label">Tên danh mục</label>
                        <input type="text" class="form-control" id="edit-categoryname" name="CategoryName" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit-description" name="Description" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning" id="editCategorySubmit">Cập nhật</button>
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
    console.log('Category management script loaded');

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
            const name = this.getAttribute('data-name');
            const desc = this.getAttribute('data-desc') || '';
            const version = this.getAttribute('data-version');

            document.getElementById('edit-categoryid').value = id;
            document.getElementById('edit-categoryname').value = name;
            document.getElementById('edit-description').value = desc;
            document.getElementById('edit-version').value = version;

            document.getElementById('editCategoryForm').action = `/admin/categories/${id}`;
        });
    });

    // Add form submission handler
    const addForm = document.getElementById('addCategoryForm');
    const addSubmitBtn = document.getElementById('addCategorySubmit');
    const addModalEl = document.getElementById('addCategoryModal');
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

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi thêm danh mục: ' + error.message;
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            });
        });
    } else {
        console.error('Add form not found');
    }

    // Edit form submission handler
    const editForm = document.getElementById('editCategoryForm');
    const editSubmitBtn = document.getElementById('editCategorySubmit');
    const editModalEl = document.getElementById('editCategoryModal');
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

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi cập nhật danh mục: ' + error.message;
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
            const name = this.getAttribute('data-name');

            if (confirm(`Bạn có chắc chắn muốn xóa danh mục ${name}?`)) {
                if (isSubmitting) {
                    console.log('Delete blocked: already in progress');
                    return;
                }

                isSubmitting = true;

                fetch(`/admin/categories/${id}`, {
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
                    document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi xóa danh mục: ' + error.message;
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
    .category-desc {
        max-width: 500px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection