@extends('layouts.app')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.isLoggedIn = @json($isLoggedIn);
        window.categoriesFromDB = @json($categoriesFromDB);
    </script>
    @vite(['resources/css/styles-ctsp.css', 'resources/js/scripts-ctsp.js', 'resources/js/cart-shared.js'])
@endpush

@section('content')
    <!-- Announcement Bar -->
    <div class="hotline-bar">
        <div class="container">
            <div class="hotline-content">
                <span><i class="fas fa-phone-alt"></i> Hotline: 0346 638 136</span>
                <span><i class="fas fa-headset"></i> Tư vấn Laptop - Điện thoại</span>
                <span><i class="fas fa-map-marker-alt"></i> CS1: Quận 1 - Đồng khởi</span>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="/">TPV E-COMMERCE</a>
            </div>
            <div class="header-slogan">
                <div class="slogan-item">
                    <img src="{{ asset('../img/header1.webp') }}" alt="Chất lượng" class="slogan-icon" width="20"
                        height="20" loading="lazy" />
                    <span>Chất lượng đảm bảo</span>
                </div>
                <div class="slogan-item">
                    <img src="{{ asset('../img/header2.webp') }}" alt="Vận chuyển" class="slogan-icon" width="20"
                        height="20" loading="lazy" />
                    <span>Vận chuyển siêu tốc</span>
                </div>
                <div class="slogan-item">
                    <img src="{{ asset('../img/header3.webp') }}" alt="Tư vấn" class="slogan-icon" width="20" height="20"
                        loading="lazy" />
                    <span>Tư vấn Hotline: 0346638136</span>
                </div>
            </div>
            <nav>
                <div class="hamburger-menu" aria-label="Menu" tabindex="0">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span class="close-menu" aria-label="Close menu"></span>
                <a href="/" class="nav-link">Home</a>
                <a href="#" class="nav-link">Mac</a>
                <a href="#" class="nav-link">Iphone</a>
                <a href="#" class="nav-link">Watch</a>
                <a href="#" class="nav-link">AirPods</a>
                <div class="search-container">
                    <div class="search-box">
                        <span class="search-icon-input">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                                <path
                                    d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                            </svg>
                        </span>
                        <input type="text" id="search-input" placeholder="Tìm kiếm sản phẩm..." autocomplete="off"
                            aria-label="Search" aria-controls="dropdown-search" aria-expanded="false">
                    </div>
                    <div class="dropdown-search" id="dropdown-search" role="listbox">
                        <p class="no-results" style="display: none;">Không tìm thấy sản phẩm nào.</p>
                        <div id="search-results"></div>
                    </div>
                </div>
                @auth
                    <div class="cart-container" aria-label="Shopping cart" role="button" tabindex="0"
                        aria-controls="cart-dropdown" aria-expanded="false">
                        <div class="cart-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                <path
                                    d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1.003 1.003 0 0 0 20 4H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                            </svg>
                        </div>
                        <span class="cart-count" aria-label="{{ $cartCount }} items in cart">{{ $cartCount }}</span>
                        <div class="cart-dropdown" id="cart-dropdown">
                            <div class="cart-dropdown-header">
                                <h3>Giỏ Hàng</h3>
                                <span class="cart-dropdown-close" aria-label="Close">×</span>
                            </div>
                            <div class="cart-dropdown-body" id="cart-items">
                                <div class="cart-empty" id="cart-empty"
                                    style="display: {{ $cartCount > 0 ? 'none' : 'block' }};">
                                    <i class="fas fa-shopping-cart fa-3x"></i>
                                    <p>Giỏ hàng của bạn đang trống.</p>
                                    <p class="cart-empty-hint">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                                </div>
                            </div>
                            <div class="cart-dropdown-footer">
                                <div class="cart-total">
                                    <span>Tổng cộng:</span>
                                    <span id="cart-total-price">0₫</span>
                                </div>
                                <div class="cart-dropdown-buttons">
                                    <a href="/cart" class="cart-dropdown-button view-cart-btn">Xem giỏ hàng</a>
                                    <a href="/checkout" class="cart-dropdown-button checkout-btn">Thanh toán</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth
                @guest
                    <a href="{{ route('login-register') }}" id="login-btn" class="btn-primary">Đăng nhập</a>
                @else
                    <div id="user-info" aria-controls="user-dropdown" aria-expanded="false">
                        <span class="user-profile">
                            <i class="fas fa-user-circle"></i>
                            {{ e($username) }}
                        </span>
                        <div class="user-dropdown" id="user-dropdown">
                            <a href="{{ route('profile') }}">Thông tin cá nhân</a>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Đăng xuất</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                @endguest
            </nav>
        </div>
    </header>


    <!-- Product Details Section -->
    <section class="product-details-section">
        <div class="container">
            <div class="product-details">
                <div class="product-main">
                    <div class="product-image-gallery">
                        <div class="main-image">
                            <img src="{{ asset('images/' . $product->ImageURL) }}" alt="{{ e($product->ProductName) }}"
                                loading="lazy" width="400" height="400">
                        </div>
                    </div>
                    <div class="product-content">
                        <h1>{{ e($product->ProductName) }}</h1>
                        <div class="product-meta">
                            <p class="stock-status">
                                Tình trạng: <span
                                    class="{{ $product->Stock > 0 ? 'in-stock' : 'out-of-stock' }}">{{ $product->Stock > 0 ? 'Còn hàng' : 'Hết hàng' }}</span>
                            </p>
                            <div class="price-container">
                                <span class="current-price">{{ number_format($product->CurrentPrice, 0, ',', '.') }}₫</span>
                                @if ($product->DiscountPercentage)
                                    <div>
                                        <span class="original-price">{{ number_format($product->Price, 0, ',', '.') }}₫</span>
                                        <span
                                            class="discount-badge">-{{ number_format($product->DiscountPercentage, 0) }}%</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <form class="product-form" id="add-to-cart-form" action="/cart/add" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->ProductID }}">
                            <div class="quantity-control">
                                <label for="quantity">Số lượng:</label>
                                <div class="quantity-input">
                                    <button type="button" class="quantity-btn decrease" data-id="{{ $product->ProductID }}"
                                        aria-label="Giảm số lượng">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1"
                                        max="{{ $product->Stock }}" data-id="{{ $product->ProductID }}"
                                        aria-label="Số lượng sản phẩm" required>
                                    <button type="button" class="quantity-btn increase" data-id="{{ $product->ProductID }}"
                                        aria-label="Tăng số lượng">+</button>
                                </div>
                            </div>
                            <div class="product-actions">
                                <button type="submit" class="add-to-cart" data-product-id="{{ $product->ProductID }}"
                                    data-name="{{ e($product->ProductName) }}" data-price="{{ $product->CurrentPrice }}" {{ $product->Stock <= 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                </button>
                                <a href="/checkout" class="buy-now" {{ $product->Stock <= 0 ? 'aria-disabled="true"' : '' }}>Mua ngay</a>
                            </div>
                        </form>
                        <div class="product-policies">
                            <h3>Chính sách bán hàng</h3>
                            <ul>
                                <li><i class="fas fa-check"></i> Cam kết 100% chính hãng</li>
                                <li><i class="fas fa-check"></i> Hỗ trợ 24/7</li>
                                <li><i class="fas fa-check"></i> Hoàn tiền 111% nếu hàng giả</li>
                                <li><i class="fas fa-check"></i> Mở hộp kiểm tra nhận hàng</li>
                                <li><i class="fas fa-check"></i> Đổi trả trong 7 ngày</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="product-description">
                    <h3>Mô tả sản phẩm</h3>
                    <p>{!! $product->Description ?? 'Không có mô tả.' !!}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <section class="related-products-section scroll-reveal">
        <div class="container">
            <h2 class="section-title">Có thể bạn quan tâm</h2>
            <div class="products-slider" role="list">
                @foreach($latestProducts as $related)
                    @if($related->ProductID && \App\Models\Product::where('ProductID', $related->ProductID)->exists())
                        <a href="/product/{{ $related->ProductID }}" class="product-card" role="listitem"
                            data-product-id="{{ $related->ProductID }}">
                            <div class="product-image">
                                <img src="{{ asset('images/' . $related->ImageURL) }}" alt="{{ e($related->ProductName) }}"
                                    loading="lazy" width="200" height="200">
                                @if($related->DiscountPercentage)
                                    <span class="product-badge">Sale!</span>
                                @endif
                                <div class="product-actions">
                                    <button class="product-action-btn quick-view" data-id="{{ $related->ProductID }}"
                                        aria-label="Xem nhanh">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="product-action-btn add-to-wishlist" data-id="{{ $related->ProductID }}"
                                        aria-label="Thêm vào danh sách yêu thích">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-details">
                                <div class="product-title">{{ e($related->ProductName) }}</div>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <div class="price-container">
                                    <span class="current-price">{{ number_format($related->CurrentPrice, 0, ',', '.') }}₫</span>
                                    @if($related->DiscountPercentage)
                                        <div>
                                            <span class="original-price">{{ number_format($related->Price, 0, ',', '.') }}₫</span>
                                            <span class="discount-badge">-{{ number_format($related->DiscountPercentage, 0) }}%</span>
                                        </div>
                                    @endif
                                </div>
                                <button class="add-to-cart" data-product-id="{{ $related->ProductID }}"
                                    data-name="{{ e($related->ProductName) }}" data-price="{{ $related->CurrentPrice }}">
                                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                </button>
                            </div>
                        </a>
                    @else
                        <p class="no-product-id">Sản phẩm không hợp lệ (ID: {{ $related->ProductID ?? 'null' }}).</p>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer scroll-reveal">
        <div class="container">
            <div class="footer-row">
                <div class="footer-column">
                    <h3>Về TPV E-COMMERCE</h3>
                    <p>Trang thương mại chính thức của TPV E-COMMERCE. Luôn tìm kiếm những sản phẩm vì mọi người.</p>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Youtube"><i class="fab fa-youtube"></i></a>
                        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Thông tin liên hệ</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> CS1: Đồng khởi - Quận 1</p>
                        <p><i class="fas fa-phone"></i> 0346638136</p>
                        <p><i class="fas fa-envelope"></i> bthvuong23@gmail.com</p>
                        <p><i class="fas fa-clock"></i> Thứ 2 - Thứ 7: 8:00 - 22:00</p>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Hỗ trợ khách hàng</h3>
                    <ul>
                        <li><a href="#">Tài Khoản Ngân Hàng</a></li>
                        <li><a href="#">Hướng dẫn mua hàng</a></li>
                        <li><a href="#">Phương thức thanh toán</a></li>
                        <li><a href="#">Hướng dẫn đổi trả</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Chính sách</h3>
                    <ul>
                        <li><a href="#">Chính Sách Bảo Mật</a></li>
                        <li><a href="#">Quy Định Bảo Hành</a></li>
                        <li><a href="#">Chính Sách Đổi Trả</a></li>
                        <li><a href="#">Điều khoản sử dụng</a></li>
                        <li><a href="#">Chính sách vận chuyển</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>Copyright © 2025 Bản quyền của Công ty cổ phần TPV E-COMMERCE Việt Nam - Trụ sở: Hồ Chí Minh</p>
                <div class="payment-methods">
                    <img src="{{ asset('../img/payment-visa.webp') }}" alt="Visa" width="40" height="25" loading="lazy">
                    <img src="{{ asset('../img/payment-mastercard.webp') }}" alt="Mastercard" width="40" height="25"
                        loading="lazy">
                    <img src="{{ asset('../img/payment-momo.webp') }}" alt="MoMo" width="40" height="25" loading="lazy">
                    <img src="{{ asset('../img/payment-vnpay.webp') }}" alt="VNPay" width="40" height="25" loading="lazy">
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to top button -->
    <div id="back-to-top" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Quick product view modal -->
    <div class="product-modal" role="dialog" aria-hidden="true">
        <div class="product-modal-content">
            <span class="product-modal-close">×</span>
            <div class="product-modal-body">
                <!-- Product quick view content will be loaded here -->
            </div>
        </div>
    </div>
@endsection