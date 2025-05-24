@extends('layouts.app')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.isLoggedIn = @json(Auth::check());
        if (!window.isLoggedIn) {
            console.warn('User is not authenticated. Check middleware and session configuration.');
            Swal.fire({
                icon: 'warning',
                title: 'Chưa đăng nhập',
                text: 'Bạn cần đăng nhập để xem phần đăng xuất.',
                timer: 3000,
                showConfirmButton: true
            });
        }
    </script>
    @vite(['resources/css/profile.css', 'resources/js/profile.js'])
@endpush

@section('content')
    <div class="container">
        <div class="profile-card">
            <!-- User Info and Logout Section -->
            <div class="user-info-section">
                @auth
                    <div class="user-profile">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ e(auth()->user()->FullName) }}</span>
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </button>
                    </form>
                @else
                    <a href="{{ route('login-register') }}" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </a>
                @endauth
            </div>

            <!-- Rest of the profile content (unchanged) -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h1>{{ auth()->user()->FullName ?? 'Khách' }}</h1>
                <p>{{ auth()->user()->UserType ?? 'N/A' }}</p>
            </div>

            <div class="tabs-container">
                <ul class="tabs-nav">
                    <li><button class="tab-button active" data-tab="personal-info"><i class="fas fa-user"></i> Thông tin cá nhân</button></li>
                    <li><button class="tab-button" data-tab="change-password"><i class="fas fa-lock"></i> Đổi mật khẩu</button></li>
                    <li><button class="tab-button" data-tab="orders"><i class="fas fa-shopping-cart"></i> Đơn hàng</button></li>
                </ul>
            </div>

            <!-- Remaining tab content unchanged -->
            <div class="tab-content">
                <!-- Personal Information Tab -->
                <div class="tab-pane active" id="personal-info">
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-user"></i> Cập nhật thông tin</h2>
                        <form id="user-profile-form" method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="user-id">Mã người dùng</label>
                                    <input class="form-input" type="text" id="user-id" name="UserID"
                                        value="{{ old('UserID', auth()->user()->UserID ?? '') }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="email">Email</label>
                                    <input class="form-input" type="email" id="email" name="Email"
                                        value="{{ old('Email', auth()->user()->Email ?? '') }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="full-name">Họ và tên</label>
                                    <input class="form-input @error('FullName') error @enderror" type="text" id="full-name"
                                        name="FullName" value="{{ old('FullName', auth()->user()->FullName ?? '') }}" required>
                                    @error('FullName')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="phone">Số điện thoại</label>
                                    <input class="form-input @error('Phone') error @enderror" type="text" id="phone"
                                        name="Phone" value="{{ old('Phone', auth()->user()->Phone ?? '') }}" required>
                                    @error('Phone')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="user-type">Loại tài khoản</label>
                                    <input class="form-input" type="text" id="user-type" name="UserType"
                                        value="{{ old('UserType', auth()->user()->UserType ?? '') }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="created-at">Ngày tạo</label>
                                    <input class="form-input" type="text" id="created-at" name="CreatedAt"
                                        value="{{ old('CreatedAt', auth()->user()->created_at ? \Carbon\Carbon::parse(auth()->user()->created_at)->format('d/m/Y') : '') }}"
                                        readonly>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-full">Cập nhật thông tin</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane" id="change-password">
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-lock"></i> Thay đổi mật khẩu</h2>
                        <form id="change-password-form" method="POST" action="{{ route('password.change') }}">
                            @csrf
                            @method('PUT')
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="current-password">Mật khẩu hiện tại</label>
                                    <input class="form-input @error('current_password') error @enderror" type="password"
                                        id="current-password" name="current_password" required>
                                    @error('current_password')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="new-password">Mật khẩu mới</label>
                                    <input class="form-input @error('new_password') error @enderror" type="password"
                                        id="new-password" name="new_password" required>
                                    @error('new_password')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="new-password-confirmation">Xác nhận mật khẩu mới</label>
                                    <input class="form-input @error('new_password_confirmation') error @enderror"
                                        type="password" id="new-password-confirmation" name="new_password_confirmation"
                                        required>
                                    @error('new_password_confirmation')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-full">Đổi mật khẩu</button>
                        </form>
                    </div>
                </div>

                <!-- Orders Tab -->
                <div class="tab-pane" id="orders">
                    <div class="form-section">
                        <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Lịch sử đơn hàng</h2>
                        @if($orders->isEmpty())
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <h3>Không có đơn hàng</h3>
                                <p>Bạn chưa có đơn hàng nào. <a href="{{ route('home') }}">Mua sắm ngay!</a></p>
                            </div>
                        @else
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td data-label="Mã đơn hàng">{{ $order->OrderID }}</td>
                                            <td data-label="Tổng tiền">{{ number_format($order->TotalPrice, 0, ',', '.') }} VNĐ</td>
                                            <td data-label="Trạng thái">
                                                <span
                                                    class="status-badge {{ $order->Status == 'Hoàn thành' ? 'status-completed' : 'status-pending' }}">
                                                    {{ $order->Status }}
                                                </span>
                                            </td>
                                            <td data-label="Ngày đặt">
                                                {{ \Carbon\Carbon::parse($order->CreatedAt)->format('d/m/Y') }}</td>
                                            <td data-label="Thao tác">
                                                <button class="btn btn-secondary"
                                                    onclick="toggleOrderDetails('{{ $order->OrderID }}')">Chi tiết</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                <div id="order-details-{{ $order->OrderID }}" class="order-details">
                                                    @if($order->items->isEmpty())
                                                        <p>Không có chi tiết đơn hàng.</p>
                                                    @else
                                                        <ul class="order-items">
                                                            @foreach($order->items as $detail)
                                                                <li class="order-item">
                                                                    <div class="order-item-info">
                                                                        <div class="order-item-name">{{ $detail->product->ProductName }}
                                                                        </div>
                                                                        <div class="order-item-details">Số lượng: {{ $detail->Quantity }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="order-item-price">
                                                                        {{ number_format($detail->Price, 0, ',', '.') }} VNĐ</div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- Back to Home Button -->
        <a href="{{ route('home') }}" class="back-button" title="Quay lại trang chủ">
            <i class="fas fa-home"></i> Trang chủ
        </a>
    </div>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: '{{ session('success') }}',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                html: '{!! implode('<br>', $errors->all()) !!}',
            });
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle logout form submission via AJAX
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    try {
                        const response = await fetch(logoutForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(logoutForm),
                            credentials: 'include'
                        });
                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Đăng xuất thành công!',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '{{ route('login-register') }}';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi!',
                                text: 'Không thể đăng xuất. Vui lòng thử lại.',
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            text: 'Có lỗi xảy ra: ' + error.message,
                        });
                        console.error('Logout error:', error);
                    }
                });
            }
        });
    </script>
@endpush