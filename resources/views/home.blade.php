@extends('layouts.app')

@push('head')
    <!-- Font Awesome and Google Fonts with display=swap for better performance -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Preload critical assets -->
    <div class="hero-section scroll-reveal">
        <link rel="preload" href="{{ asset('../img/banner1.avif') }}" as="image">
    </div>

    <!-- Vite Assets -->
    @vite([
        'resources/css/styles-home.css',
        'resources/js/scripts-home.js',
        'resources/js/cart-shared.js'
    ])
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
        <div class="logo">
            <a href="#">TPV E-COMMERCE</a>
        </div>

        <div class="header-slogan">
            <div class="slogan-item">
                <img src="{{ asset('../img/header1.webp') }}" alt="Chất lượng" class="slogan-icon" width="20" height="20"
                    loading="lazy" />
                <span>Chất lượng đảm bảo</span>
            </div>
            <div class="slogan-item">
                <img src="{{ asset('../img/header2.webp') }}" alt="Vận chuyển" class="slogan-icon" width="20" height="20"
                    loading="lazy" />
                <span>Vận chuyển siêu tốc</span>
            </div>
            <div class="slogan-item">
                <img src="{{ asset('../img/header3.webp') }}" alt="Tư vấn" class="slogan-icon" width="20" height="20"
                    loading="lazy" />
                <span>Tư vấn Hotline: 0346638136</span>
            </div>
        </div>

        <nav>
            <!-- Hamburger for mobile -->
            <div class="hamburger-menu" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="close-menu" aria-label="Close menu"></span>

            <!-- Navigation links -->
            <a href="#" class="nav-link">Home</a>
            <a href="#" class="nav-link">Mac</a>
            <a href="#" class="nav-link">Iphone</a>
            <a href="#" class="nav-link">Watch</a>
            <a href="#" class="nav-link">AirPods</a>

            <!-- Search box -->
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

            <!-- Shopping cart -->
            <div class="cart-container" aria-label="Shopping cart" role="button" tabindex="0" aria-controls="cart-dropdown"
                aria-expanded="false">
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
                        <div class="cart-empty" id="cart-empty">
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
                            <a href="cart.php" class="cart-dropdown-button view-cart-btn">Xem giỏ hàng</a>
                            <a href="checkout.php" class="cart-dropdown-button checkout-btn">Thanh toán</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User account -->
            @guest
                <a href="{{ route('login-register') }}" id="login-btn" class="btn-primary">Đăng nhập</a>
            @else
                <div id="user-info" aria-controls="user-dropdown" aria-expanded="false">
                    <span class="user-profile">
                        <i class="fas fa-user-circle"></i>
                        {{ e($user->FullName) }}
                    </span>
                    <div class="user-dropdown" id="user-dropdown">
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Đăng xuất</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            @endguest
        </nav>
    </header>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Cart modal -->
    <div class="cart-modal" role="dialog" aria-labelledby="cart-modal-title">
        <div class="cart-modal-content">
            <div class="cart-modal-header">
                <h2 id="cart-modal-title">Giỏ Hàng</h2>
                <span class="cart-modal-close" aria-label="Close">&times;</span>
            </div>
            <div class="cart-modal-body">
                <div class="cart-empty" id="cart-empty">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <p>Giỏ hàng của bạn đang trống.</p>
                    <p class="cart-empty-hint">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                </div>
                <div class="cart-items" id="cart-items"></div>
            </div>
            <div class="cart-modal-footer">
                <div class="cart-total">
                    <span>Tổng cộng:</span>
                    <span id="cart-total-price">0₫</span>
                </div>
                <button class="cart-checkout-btn" disabled>Thanh Toán</button>
            </div>
        </div>
    </div>

    <!-- Hero banner -->
    <section class="hero-section">
        <div class="container">
            <div class="content hero-banner">
                <img src="{{ asset('../img/banner1.avif') }}" alt="Hero banner" loading="eager" width="1200" height="400">
                <div class="banner-overlay">
                    <h1 class="banner-title">Khám phá công nghệ mới</h1>
                    <p class="banner-subtitle">Sản phẩm chính hãng với giá tốt nhất thị trường</p>
                    <a href="#" class="banner-cta">Khám phá ngay</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products section -->
    <section class="products-section scroll-reveal">
        <div class="promo-container">
            <div class="promo-title">
                <span class="icon">⭐</span>
                <span class="icon">⚡</span>
                SẢN PHẨM NỔI BẬT
                <span class="icon">🔥</span>
            </div>
            <div class="navigation-buttons">
                <button class="nav-button prev" aria-label="Previous">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nav-button next" aria-label="Next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="products-slider" role="list">
                @if($products->isNotEmpty())
                    @foreach($products as $product)
                        <div class="product-card" role="listitem">
                            <div class="product-image">
                                <img src="{{ asset('images/' . $product->ImageURL) }}" alt="{{ e($product->ProductName) }}"
                                    loading="lazy" width="200" height="200">
                                @if($product->DiscountPercentage)
                                    <span class="product-badge">Sale!</span>
                                @endif
                                <div class="product-actions">
                                    <button class="product-action-btn quick-view" data-id="{{ $product->id }}"
                                        aria-label="Quick view">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="product-action-btn add-to-wishlist" data-id="{{ $product->id }}"
                                        aria-label="Add to wishlist">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-details">
                                <div class="product-title">{{ e($product->ProductName) }}</div>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <div class="price-container">
                                    <span class="current-price">{{ number_format($product->CurrentPrice, 0) }}₫</span>
                                    @if($product->DiscountPercentage)
                                        <div>
                                            <span class="original-price">{{ number_format($product->Price, 0) }}₫</span>
                                            <span class="discount-badge">-{{ number_format($product->DiscountPercentage, 0) }}%</span>
                                        </div>
                                    @endif
                                </div>
                                <button class="add-to-cart" data-id="{{ $product->id }}" data-name="{{ e($product->ProductName) }}"
                                    data-price="{{ $product->CurrentPrice }}">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>THÊM VÀO GIỎ</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="no-products">Không có sản phẩm nào để hiển thị.</p>
                @endif
            </div>
            <!-- Pagination Links -->
            <div class="pagination">
                {{ $products->links() }}
            </div>
            <div class="view-all">
                <a href="#" class="view-all-button">
                    <i class="fas fa-list"></i> XEM TẤT CẢ SẢN PHẨM
                </a>
            </div>
        </div>
    </section>

    <!-- Features section -->
    <section class="features-section scroll-reveal">
        <div class="container">
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Giao hàng nhanh</h3>
                        <p>Giao hàng toàn quốc trong 24h</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Bảo hành chính hãng</h3>
                        <p>Bảo hành tới 12 tháng</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Đổi trả dễ dàng</h3>
                        <p>7 ngày đổi trả miễn phí</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Hỗ trợ 24/7</h3>
                        <p>Tư vấn nhiệt tình, chu đáo</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-container">
                <div class="newsletter-content">
                    <h2>Đăng ký nhận tin</h2>
                    <p>Nhận thông tin về sản phẩm mới và khuyến mãi đặc biệt</p>
                </div>
                <form class="newsletter-form">
                    <input type="email" placeholder="Nhập email của bạn" required>
                    <button type="submit">Đăng ký</button>
                </form>
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
                        <li><a href="#">Qui Định Bảo Hành</a></li>
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
                    <img src="{{ asset('../img/payment-visa.png') }}" alt="Visa" width="40" height="25" loading="lazy">
                    <img src="{{ asset('../img/payment-mastercard.png') }}" alt="Mastercard" width="40" height="25"
                        loading="lazy">
                    <img src="{{ asset('../img/payment-momo.png') }}" alt="MoMo" width="40" height="25" loading="lazy">
                    <img src="{{ asset('../img/payment-vnpay.png') }}" alt="VNPay" width="40" height="25" loading="lazy">
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
            <span class="product-modal-close">&times;</span>
            <div class="product-modal-body">
                <!-- Product quick view content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Dữ liệu cho scripts-home.js -->
    <script>
        window.categoriesFromDB = @json($categories);
    </script>

@endsection