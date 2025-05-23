@extends('admin.layouts.app')

@section('title', 'Quản lý sản phẩm')

@section('content')
<div class="container-fluid">
    <h2 class="page-title">Quản lý sản phẩm</h2>

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

    <!-- Products List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="header-actions">
                <h5 class="mb-0">Danh sách sản phẩm</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-circle"></i> Thêm sản phẩm
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Kho</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->ProductID }}</td>
                            <td>
                                @if ($product->ImageURL)
                                    <img src="{{ asset('storage/' . ltrim($product->ImageURL, '/')) }}" class="product-img" alt="{{ $product->ProductName }}">
                                @else
                                    <div class="placeholder-img bg-light d-flex align-items-center justify-content-center" style="width:70px;height:70px;">
                                        <i class="bi bi-image text-secondary" style="font-size:1.5rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $product->ProductName }}</td>
                            <td><span class="badge bg-info">{{ $product->category->CategoryName }}</span></td>
                            <td class="price">{{ number_format($product->Price, 0, ',', '.') }} đ</td>
                            <td>
                                @php
                                    $stockClass = $product->Stock <= 5 ? 'low' : ($product->Stock <= 20 ? 'medium' : 'high');
                                @endphp
                                <span class="stock {{ $stockClass }}">{{ $product->Stock }}</span>
                            </td>
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-primary edit-btn"
                                        data-id="{{ $product->ProductID }}"
                                        data-name="{{ $product->ProductName }}"
                                        data-category="{{ $product->CategoryID }}"
                                        data-price="{{ $product->Price }}"
                                        data-stock="{{ $product->Stock }}"
                                        data-desc="{{ $product->Description }}"
                                        data-image="{{ $product->ImageURL ? Storage::url($product->ImageURL) : '' }}"
                                        data-bs-toggle="modal" data-bs-target="#editProductModal">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm {{ $product->ProductName }}?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addProductModalLabel">Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-productname" class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control @error('ProductName') is-invalid @enderror" id="add-productname" name="ProductName" required>
                            @error('ProductName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="add-category" class="form-label">Danh mục</label>
                            <select class="form-select @error('CategoryID') is-invalid @enderror" id="add-category" name="CategoryID" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach (\App\Models\Category::all() as $category)
                                    <option value="{{ $category->CategoryID }}">{{ $category->CategoryName }}</option>
                                @endforeach
                            </select>
                            @error('CategoryID')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="add-price" class="form-label">Giá</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('Price') is-invalid @enderror" id="add-price" name="Price" step="0.01" required>
                                <span class="input-group-text">VNĐ</span>
                                @error('Price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="add-stock" class="form-label">Kho</label>
                            <input type="number" class="form-control @error('Stock') is-invalid @enderror" id="add-stock" name="Stock" required>
                            @error('Stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="add-description" name="Description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="add-image" name="image" accept="image/*" onchange="previewImage(this, 'add-imagePreview')">
                        <div class="form-text">Định dạng cho phép: JPG, JPEG, PNG, GIF, WEBP</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <img id="add-imagePreview" src="#" alt="Preview" class="image-preview">
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

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editProductModalLabel">Cập nhật sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editProductForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="ProductID" id="edit-productid">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-productname" class="form-label">Tên sản phẩm</label>
                            <input type="text" class="form-control" id="edit-productname" name="ProductName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-category" class="form-label">Danh mục</label>
                            <select class="form-select" id="edit-category" name="CategoryID" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach (\App\Models\Category::all() as $category)
                                    <option value="{{ $category->CategoryID }}">{{ $category->CategoryName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit-price" class="form-label">Giá</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit-price" name="Price" step="0.01" required>
                                <span class="input-group-text">VNĐ</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-stock" class="form-label">Kho</label>
                            <input type="number" class="form-control" id="edit-stock" name="Stock" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit-description" name="Description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <div class="mb-3" id="edit-current-image-container">
                            <p>Ảnh hiện tại:</p>
                            <img id="edit-current-image-preview" src="#" alt="Current product image" class="current-image">
                        </div>
                        <div class="mb-3">
                            <input type="file" class="form-control" id="edit-image" name="image" accept="image/*" onchange="previewImage(this, 'edit-imagePreview')">
                            <div class="form-text">Định dạng cho phép: JPG, JPEG, PNG, GIF, WEBP</div>
                            <img id="edit-imagePreview" src="#" alt="Preview" class="image-preview">
                            <div class="form-text mt-2">
                                <em>Chỉ cần tải ảnh mới nếu bạn muốn thay đổi ảnh hiện tại.</em>
                            </div>
                        </div>
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
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const category = this.getAttribute('data-category');
            const price = this.getAttribute('data-price');
            const stock = this.getAttribute('data-stock');
            const desc = this.getAttribute('data-desc');
            const image = this.getAttribute('data-image');

            document.getElementById('edit-productid').value = id;
            document.getElementById('edit-productname').value = name;
            document.getElementById('edit-category').value = category;
            document.getElementById('edit-price').value = price;
            document.getElementById('edit-stock').value = stock;
            document.getElementById('edit-description').value = desc;

            const imagePreview = document.getElementById('edit-current-image-preview');
            const imageContainer = document.getElementById('edit-current-image-container');
            if (image && image !== '') {
                imagePreview.src = image;
                imageContainer.style.display = 'block';
            } else {
                imageContainer.style.display = 'none';
            }

            document.getElementById('editProductForm').action = `/admin/products/${id}`;
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
    .product-img {
        max-width: 70px;
        max-height: 70px;
        object-fit: contain;
    }
    .price {
        font-weight: bold;
        color: #e74c3c;
    }
    .stock {
        font-weight: bold;
    }
    .stock.low {
        color: #e74c3c;
    }
    .stock.medium {
        color: #f39c12;
    }
    .stock.high {
        color: #27ae60;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
        display: none;
    }
    .current-image {
        max-width: 200px;
        max-height: 200px;
        margin-top: 10px;
    }
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection