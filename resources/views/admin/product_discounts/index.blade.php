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
                                        data-bs-toggle="modal" data-bs-target="#editProductDiscountModal">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <form action="{{ route('admin.product_discounts.destroy', $discount) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa giảm giá cho sản phẩm {{ $discount->product->ProductName }}?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $productDiscounts->links() }}
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
            <form method="POST" action="{{ route('admin.product_discounts.store') }}">
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
                    <button type="submit" class="btn btn-success">Thêm mới</button>
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
            <form method="POST" id="editProductDiscountForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="DiscountID" id="edit-discountid">
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
            const productId = this.getAttribute('data-product-id');
            const discount = this.getAttribute('data-discount');
            const startDate = this.getAttribute('data-start-date');
            const endDate = this.getAttribute('data-end-date');

            document.getElementById('edit-discountid').value = id;
            document.getElementById('edit-product-id').value = productId;
            document.getElementById('edit-discount').value = discount;
            document.getElementById('edit-start-date').value = startDate;
            document.getElementById('edit-end-date').value = endDate;

            document.getElementById('editProductDiscountForm').action = `/admin/product-discounts/${id}`;
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