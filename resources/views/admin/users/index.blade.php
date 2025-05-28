@extends('admin.layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Quản lý người dùng</h2>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Tìm theo tên hoặc email..." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="header-actions">
                <h5 class="mb-0">Danh sách người dùng</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Thêm người dùng
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Loại</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->UserID }}</td>
                            <td>{{ $user->FullName }}</td>
                            <td>{{ $user->Email }}</td>
                            <td>{{ $user->Phone ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $typeClass = match ($user->UserType) {
                                        'Regular' => 'bg-secondary',
                                        'VIP' => 'bg-warning',
                                        'Admin' => 'bg-success',
                                        default => 'bg-info',
                                    };
                                @endphp
                                <span class="badge {{ $typeClass }}">{{ $user->UserType }}</span>
                            </td>
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                        data-id="{{ $user->UserID }}"
                                        data-name="{{ $user->FullName }}"
                                        data-email="{{ $user->Email }}"
                                        data-phone="{{ $user->Phone }}"
                                        data-type="{{ $user->UserType }}"
                                        data-version="{{ $user->version }}"
                                        data-bs-toggle="modal" data-bs-target="#editUserModal">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                        data-id="{{ $user->UserID }}"
                                        data-name="{{ $user->FullName }}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addUserModalLabel">Thêm người dùng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="FullName" class="form-label">Họ tên</label>
                        <input type="text" class="form-control @error('FullName') is-invalid @enderror" id="FullName" name="FullName" required>
                        @error('FullName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('Email') is-invalid @enderror" id="Email" name="Email" required>
                        @error('Email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="Phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control @error('Phone') is-invalid @enderror" id="Phone" name="Phone">
                        @error('Phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="UserType" class="form-label">Loại người dùng</label>
                        <select class="form-select @error('UserType') is-invalid @enderror" id="UserType" name="UserType" required>
                            <option value="Regular">Regular</option>
                            <option value="VIP">VIP</option>
                            <option value="Admin">Admin</option>
                        </select>
                        @error('UserType')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="addUserSubmit">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editUserModalLabel">Cập nhật người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit-id">
                <input type="hidden" name="version" id="edit-version">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-fullname" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="edit-fullname" name="FullName" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="Email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-password" class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                        <input type="password" class="form-control" id="edit-password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit-phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="edit-phone" name="Phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit-type" class="form-label">Loại người dùng</label>
                        <select class="form-select" id="edit-type" name="UserType" required>
                            <option value="Regular">Regular</option>
                            <option value="VIP">VIP</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning" id="editUserSubmit">Cập nhật</button>
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
    console.log('User management script loaded');

    // Flag to track submission state
    let isSubmitting = false;

    // CSRF token
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
            const email = this.getAttribute('data-email');
            const phone = this.getAttribute('data-phone');
            const type = this.getAttribute('data-type');
            const version = this.getAttribute('data-version');

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-fullname').value = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-phone').value = phone || '';
            document.getElementById('edit-type').value = type;
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-version').value = version;

            document.getElementById('editUserForm').action = `/admin/users/${id}`;
        });
    });

    // Add form submission handler
    const addForm = document.getElementById('addUserForm');
    const addSubmitBtn = document.getElementById('addUserSubmit');
    const addModalEl = document.getElementById('addUserModal');
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

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi thêm người dùng: ' + error.message;
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            });
        });
    } else {
        console.error('Add form not found');
    }

    // Edit form submission handler
    const editForm = document.getElementById('editUserForm');
    const editSubmitBtn = document.getElementById('editUserSubmit');
    const editModalEl = document.getElementById('editUserModal');
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

                document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi cập nhật người dùng: ' + error.message;
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

            if (confirm(`Bạn có chắc chắn muốn xóa người dùng ${name}?`)) {
                if (isSubmitting) {
                    console.log('Delete blocked: already in progress');
                    return;
                }

                isSubmitting = true;

                fetch(`/admin/users/${id}`, {
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
                    } else if (status === 403 && data.error) {
                        console.log('Delete forbidden:', data.error);
                        document.getElementById('errorMessage').textContent = data.error;
                        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                        errorModal.show();
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
                    document.getElementById('errorMessage').textContent = 'Đã xảy ra lỗi khi xóa người dùng: ' + error.message;
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
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection