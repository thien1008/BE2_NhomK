@extends('admin.layouts.app')

@section('title', 'Quản lý mã giảm giá')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Quản lý mã giảm giá</h2>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm mã giảm giá..." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Coupons List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="header-actions">
                <h5 class="mb-0">Danh sách mã giảm giá</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                    <i class="bi bi-plus-circle"></i> Thêm mã giảm giá
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Mã giảm giá</th>
                            <th>Phần trăm giảm</th>
                            <th>Hiệu lực từ</th>
                            <th>Hiệu lực đến</th>
                            <th>Giới hạn sử dụng</th>
                            <th>Giới hạn người dùng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->CouponID }}</td>
                            <td><strong>{{ $coupon->Code }}</strong></td>
                            <td>{{ $coupon->DiscountPercentage }}%</td>
                            <td>{{ $coupon->ValidFrom }}</td>
                            <td>{{ $coupon->ValidTo }}</td>
                            <td>{{ $coupon->UsageLimit ?? 'Không giới hạn' }}</td>
                            <td>{{ $coupon->UserLimit ?? 'Không giới hạn' }}</td>
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                        data-id="{{ $coupon->CouponID }}"
                                        data-code="{{ $coupon->Code }}"
                                        data-discount="{{ $coupon->DiscountPercentage }}"
                                        data-valid-from="{{ $coupon->ValidFrom }}"
                                        data-valid-to="{{ $coupon->ValidTo }}"
                                        data-usage-limit="{{ $coupon->UsageLimit }}"
                                        data-user-limit="{{ $coupon->UserLimit }}"
                                        data-bs-toggle="modal" data-bs-target="#editCouponModal">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa mã giảm giá {{ $coupon->Code }}?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $coupons->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1" aria-labelledby="addCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addCouponModalLabel">Thêm mã giảm giá mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.coupons.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-code" class="form-label">Mã giảm giá</label>
                        <input type="text" class="form-control @error('Code') is-invalid @enderror" id="add-code" name="Code" required>
                        @error('Code')
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
                        <label for="add-valid-from" class="form-label">Hiệu lực từ</label>
                        <input type="date" class="form-control @error('ValidFrom') is-invalid @enderror" id="add-valid-from" name="ValidFrom" required>
                        @error('ValidFrom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-valid-to" class="form-label">Hiệu lực đến</label>
                        <input type="date" class="form-control @error('ValidTo') is-invalid @enderror" id="add-valid-to" name="ValidTo" required>
                        @error('ValidTo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-usage-limit" class="form-label">Giới hạn sử dụng</label>
                        <input type="number" class="form-control @error('UsageLimit') is-invalid @enderror" id="add-usage-limit" name="UsageLimit" min="0">
                        @error('UsageLimit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add-user-limit" class="form-label">Giới hạn người dùng</label>
                        <input type="number" class="form-control @error('UserLimit') is-invalid @enderror" id="add-user-limit" name="UserLimit" min="0">
                        @error('UserLimit')
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

<!-- Edit Coupon Modal -->
<div class="modal fade" id="editCouponModal" tabindex="-1" aria-labelledby="editCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editCouponModalLabel">Cập nhật mã giảm giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editCouponForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="CouponID" id="edit-couponid">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-code" class="form-label">Mã giảm giá</label>
                        <input type="text" class="form-control" id="edit-code" name="Code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-discount" class="form-label">Phần trăm giảm</label>
                        <input type="number" class="form-control" id="edit-discount" name="DiscountPercentage" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-valid-from" class="form-label">Hiệu lực từ</label>
                        <input type="date" class="form-control" id="edit-valid-from" name="ValidFrom" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-valid-to" class="form-label">Hiệu lực đến</label>
                        <input type="date" class="form-control" id="edit-valid-to" name="ValidTo" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-usage-limit" class="form-label">Giới hạn sử dụng</label>
                        <input type="number" class="form-control" id="edit-usage-limit" name="UsageLimit" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit-user-limit" class="form-label">Giới hạn người dùng</label>
                        <input type="number" class="form-control" id="edit-user-limit" name="UserLimit" min="0">
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
            const code = this.getAttribute('data-code');
            const discount = this.getAttribute('data-discount');
            const validFrom = this.getAttribute('data-valid-from');
            const validTo = this.getAttribute('data-valid-to');
            const usageLimit = this.getAttribute('data-usage-limit');
            const userLimit = this.getAttribute('data-user-limit');

            document.getElementById('edit-couponid').value = id;
            document.getElementById('edit-code').value = code;
            document.getElementById('edit-discount').value = discount;
            document.getElementById('edit-valid-from').value = validFrom;
            document.getElementById('edit-valid-to').value = validTo;
            document.getElementById('edit-usage-limit').value = usageLimit;
            document.getElementById('edit-user-limit').value = userLimit;

            document.getElementById('editCouponForm').action = `/admin/coupons/${id}`;
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