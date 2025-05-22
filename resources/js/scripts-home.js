document.addEventListener('DOMContentLoaded', () => {
    // Xử lý mouseleave trên toàn bộ header
    const header = document.querySelector('header');
    header.addEventListener('mouseleave', (e) => {
        const dropdowns = document.querySelectorAll('.dropdown-menu');
        let isMouseInDropdown = false;

        // Kiểm tra xem chuột có đang ở trong một dropdown hay không
        dropdowns.forEach(dropdown => {
            if (dropdown.contains(e.relatedTarget)) {
                isMouseInDropdown = true;
            }
        });

        // Chỉ đóng các dropdown nếu chuột không ở trong dropdown
        if (!isMouseInDropdown) {
            closeAllDropdowns();
        }
    });

    // Xử lý mouseleave trên các dropdown để đóng khi chuột rời khỏi dropdown
    document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
        dropdown.addEventListener('mouseleave', (e) => {
            // Đóng dropdown nếu chuột không di chuyển vào header hoặc một dropdown khác
            if (!header.contains(e.relatedTarget) && !document.querySelector('.dropdown-menu').contains(e.relatedTarget)) {
                toggleDropdown(dropdown, false);
            }
        });
    });

    // Hàm tiện ích: Định dạng tiền tệ VND
    const formatVND = num => num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₫';

    // Hàm hiển thị thông báo bằng Swal
    const showAlert = (icon, title, text, options = {}) => Swal.fire({
        icon, title, text, confirmButtonText: 'OK', ...options
    });

    // Hàm bật/tắt dropdown
    const toggleDropdown = (dropdown, state) => {
        if (dropdown) {
            dropdown.classList.toggle('active', state);
            const trigger = dropdown.previousElementSibling || dropdown.parentElement;
            trigger?.setAttribute('aria-expanded', state);
        }
    };

    // Hàm đóng tất cả dropdown trừ dropdown được chỉ định
    const closeAllDropdowns = except => document.querySelectorAll('.dropdown-menu, .dropdown-search, .user-dropdown, .cart-dropdown')
        .forEach(d => d !== except && toggleDropdown(d, false));

    // Hàm debounce để trì hoãn thực thi
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    };

    function updateCartUI(data) {
        if (data.success) {
            // Cập nhật số lượng sản phẩm trên icon giỏ hàng (giả sử có phần tử #cart-count)
            const cartCountElement = document.querySelector('#cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.itemCount || 0;
            }

            // Nếu có phần tử hiển thị tổng tiền giỏ hàng
            const cartTotalElement = document.querySelector('#cart-total');
            if (cartTotalElement) {
                cartTotalElement.textContent = data.total
                    ? data.total.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })
                    : '0₫';
            }

            // Có thể hiển thị thông báo thành công
            showAlert('success', 'Thành công', 'Giỏ hàng đã được cập nhật.');
        } else {
            showAlert('error', 'Lỗi', data.message || 'Không thể cập nhật giỏ hàng.');
        }
    }


    // Hàm phân tích phản hồi JSON từ server
    const parseResponse = async response => {
        try {
            return await response.json();
        } catch (e) {
            console.error('Invalid JSON:', await response.text());
            showAlert('error', 'Lỗi', 'Phản hồi từ máy chủ không hợp lệ.');
            return null;
        }
    };

    // Hàm gửi yêu cầu AJAX đến server để xử lý giỏ hàng
    const sendCartRequest = async (action, data, callback) => {
        console.log('Sending data:', data);
        const url = `/cart/${action}`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showAlert('error', 'Lỗi', 'Thiếu CSRF token.');
            return;
        }
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            });
            let json;
            try {
                json = await response.json();
            } catch (e) {
                console.error('Invalid JSON response:', await response.text());
                showAlert('error', 'Lỗi', 'Phản hồi máy chủ không hợp lệ.');
                return;
            }
            console.log('Cart request response:', json);
            if (!response.ok) {
                showAlert('error', 'Lỗi', json.message || `Lỗi máy chủ (${response.status}).`);
                return;
            }
            if (json.success) {
                if (action === 'add') {
                    showAlert('success', 'Thành công', 'Sản phẩm đã được thêm vào giỏ hàng!', { timer: 1500, showConfirmButton: false });
                }
                callback(json); // Chỉ gọi callback, không gửi yêu cầu get tự động
            } else {
                showAlert('error', 'Thất bại', json.message || 'Hành động không thành công.');
            }
        } catch (error) {
            console.error('Cart Error:', error);
            showAlert('error', 'Lỗi kết nối', 'Đã có lỗi xảy ra: ' + error.message);
            callback({ total: 0, itemCount: 0, cart: [] });
        }
    };

    // Xử lý dropdown
    // Xử lý dropdown
    const setupDropdown = (trigger, dropdown, isMobileClick = false) => {
        const handleToggle = (e, forceState) => {
            const isMobile = window.innerWidth <= 768;
            if (isMobile && e.type === 'click' && isMobileClick) {
                e.preventDefault();
                closeAllDropdowns();
                toggleDropdown(dropdown, forceState ?? !dropdown.classList.contains('active'));
            } else if (!isMobile) {
                if (e.type === 'mouseenter') {
                    closeAllDropdowns(dropdown);
                    toggleDropdown(dropdown, true);
                }
                // Không xử lý mouseleave ở đây nữa
            }
        };

        trigger.addEventListener('mouseenter', handleToggle);
        trigger.addEventListener('click', handleToggle);
        trigger.addEventListener('keydown', e => (e.key === 'Enter' || e.key === ' ') && handleToggle(e));
    };

    document.querySelectorAll('nav a.nav-link').forEach(item => {
        const category = item.textContent.trim().toLowerCase();
        if (!categoriesFromDB[category]) return;

        const dropdown = document.createElement('div');
        dropdown.classList.add('dropdown-menu');
        dropdown.id = `dropdown-${category}`;
        item.setAttribute('aria-controls', dropdown.id);
        item.setAttribute('aria-expanded', 'false');

        const container = document.createElement('div');
        container.classList.add('dropdown-menu-container');

        categoriesFromDB[category].forEach(product => {
            const link = document.createElement('a');
            link.href = `/product/${product.ProductID}`;
            link.textContent = product.ProductName;
            container.appendChild(link);
        });
        dropdown.appendChild(container);
        item.parentElement.appendChild(dropdown);
        setupDropdown(item, dropdown, true);
    });

    // Dropdown tìm kiếm
    const searchInput = document.getElementById('search-input');
    const dropdownSearch = document.getElementById('dropdown-search');
    const searchResults = document.getElementById('search-results');
    const noResults = dropdownSearch?.querySelector('.no-results');

    if (searchInput && dropdownSearch && searchResults) {
        searchInput.addEventListener('focus', () => {
            closeAllDropdowns(dropdownSearch);
            toggleDropdown(dropdownSearch, true);
        });

        const performSearch = debounce(async value => {
            if (!value.trim()) {
                searchResults.innerHTML = '';
                noResults && (noResults.style.display = 'block');
                toggleDropdown(dropdownSearch, false);
                return;
            }

            try {
                const res = await fetch(`?search=${encodeURIComponent(value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                searchResults.innerHTML = '';
                noResults && (noResults.style.display = data.length ? 'none' : 'block');

                const regex = new RegExp(`(${value.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                data.forEach(product => {
                    const result = document.createElement('a');
                    result.classList.add('search-result');
                    result.href = `/product/${product.ProductID}`;

                    // Đảm bảo CurrentPrice là số trước khi format
                    const currentPrice = !isNaN(Number(product.CurrentPrice))
                        ? Number(product.CurrentPrice).toLocaleString()
                        : '0';
                    console.log('Search result item:', product);

                    result.innerHTML = `
        <img src="/images/${product.ImageURL}" alt="${product.ProductName}">
        <div class="search-result-details">
            <div class="search-result-name">${product.ProductName.replace(regex, '<strong>$1</strong>')}</div>
            <div class="search-result-price">
                ${currentPrice}₫
                ${product.DiscountPercentage ? `<span class="search-result-discount">-${product.DiscountPercentage}%</span>` : ''}
            </div>
        </div>
    `;
                    searchResults.appendChild(result);
                });

                toggleDropdown(dropdownSearch, true);
            } catch (err) {
                console.error('Search Error:', err);
                showAlert('error', 'Lỗi', 'Đã có lỗi khi tìm kiếm.');
            }
        }, 300);

        searchInput.addEventListener('input', e => {
            searchInput.setAttribute('placeholder', e.target.value.trim() ? '' : 'Tìm kiếm sản phẩm...');
            performSearch(e.target.value);
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('.search-container')) toggleDropdown(dropdownSearch, false);
        });
    }

    // Dropdown người dùng
    const userInfo = document.getElementById('user-info');
    const userDropdown = document.getElementById('user-dropdown');
    if (userInfo && userDropdown) setupDropdown(userInfo, userDropdown, true);

    // Chức năng giỏ hàng
    const cartContainer = document.querySelector('.cart-container');
    const cartDropdown = document.getElementById('cart-dropdown');
    const cartClose = cartDropdown?.querySelector('.cart-dropdown-close');
    const cartItems = cartDropdown?.querySelector('#cart-items');
    const cartEmpty = cartDropdown?.querySelector('#cart-empty');
    const cartTotalPrice = cartDropdown?.querySelector('#cart-total-price');
    const cartCount = document.querySelector('.cart-count');
    const checkoutBtn = cartDropdown?.querySelector('.checkout-btn');

    if (cartContainer && cartDropdown) {
        let isCartVisible = false;

        // Hàm lấy dữ liệu giỏ hàng từ server
        const fetchCart = () => {
            if (!window.isLoggedIn) return;
            cartItems.style.display = 'block';
            cartEmpty && (cartEmpty.style.display = 'none');
            sendCartRequest('get', {}, data => {
                updateCartUI(data);
                isCartVisible = true;
            });
        };

        // Hàm cập nhật giao diện giỏ hàng
        const updateCartUI = ({ total = 0, itemCount = 0, cart = [] }) => {
            // Cập nhật số lượng sản phẩm ở biểu tượng giỏ
            cartCount && (cartCount.textContent = itemCount);

            // Nếu không có sản phẩm trong giỏ
            if (!Array.isArray(cart) || cart.length === 0) {
                // Ẩn các sản phẩm nếu có
                const existingItems = cartItems.querySelectorAll('.cart-dropdown-item');
                existingItems.forEach(item => item.remove());

                // Hiển thị thông báo giỏ hàng trống
                cartEmpty && (cartEmpty.style.display = 'block');
            } else {
                // Ẩn phần thông báo trống
                cartEmpty && (cartEmpty.style.display = 'none');

                // Xóa sản phẩm cũ nếu có
                const existingItems = cartItems.querySelectorAll('.cart-dropdown-item');
                existingItems.forEach(item => item.remove());

                // Hiển thị các sản phẩm mới
                cart.forEach(item => {
                    const div = document.createElement('div');
                    div.classList.add('cart-dropdown-item');
                    div.innerHTML = `
                <img src="/images/${item.ImageURL}" alt="${item.ProductName}" class="cart-dropdown-item-image">
                <div class="cart-dropdown-item-details">
                    <div class="cart-dropdown-item-name">${item.ProductName}</div>
                    <div class="cart-dropdown-item-price">${formatVND(item.Price * item.Quantity)}</div>
                </div>
            `;
                    cartItems.appendChild(div);
                });
            }

            // Cập nhật tổng giá
            cartTotalPrice && (cartTotalPrice.textContent = formatVND(total));

            // Đánh dấu là đã load xong
            isCartVisible = true;
        };


        // Hàm xử lý sự kiện hover và click vào giỏ hàng
        const handleCartToggle = (e, forceState) => {
            e.stopPropagation();
            e.preventDefault();
            const isMobile = window.innerWidth <= 768;
            if (isMobile && (e.type === 'click' || e.key === 'Enter' || e.key === ' ')) {
                if (!window.isLoggedIn) return requireLogin('Vui lòng đăng nhập để xem giỏ hàng!');
                window.location.href = '/cart';
            } else if (!isMobile) {
                const shouldShow = forceState ?? e.type === 'mouseenter';
                if (shouldShow !== cartDropdown.classList.contains('active')) {
                    toggleDropdown(cartDropdown, shouldShow);
                    if (shouldShow && window.isLoggedIn && !isCartVisible) {
                        const itemCount = parseInt(cartCount?.textContent || 0);
                        if (itemCount === 0) {
                            updateCartUI({ total: 0, itemCount: 0, cart: [] });
                        } else {
                            fetchCart();
                        }
                    }
                    if (!shouldShow) isCartVisible = false;
                }
            }
        };

        cartContainer.addEventListener('mouseenter', handleCartToggle);
        cartContainer.addEventListener('mouseleave', handleCartToggle);
        cartContainer.addEventListener('click', handleCartToggle);
        cartContainer.addEventListener('keydown', handleCartToggle);
        cartClose?.addEventListener('click', () => {
            toggleDropdown(cartDropdown, false);
            isCartVisible = false;
        });
    }

    // Xử lý nút thanh toán
    checkoutBtn?.addEventListener('click', () => {
        if (!window.isLoggedIn) return requireLogin('Vui lòng đăng nhập để thanh toán!');
        sendCartRequest('get', {}, data => {
            if (data.success && data.cart?.length) window.location.href = '/checkout';
            else showAlert('warning', 'Giỏ hàng trống', 'Vui lòng thêm sản phẩm trước khi thanh toán.');
        });
    });

    // Tương tác với sản phẩm
    const initProductInteractions = () => {
        const handleButton = (selector, callback) => document.querySelectorAll(selector).forEach(btn =>
            btn.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent card click
                callback(btn)();
            }));

        handleButton('.add-to-cart', btn => () => {
            if (!window.isLoggedIn) return requireLogin('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
            btn.disabled = true;
            const productId = btn.getAttribute('data-product-id');
            if (!productId || isNaN(productId)) {
                console.error('Invalid product ID:', productId);
                btn.disabled = false;
                showAlert('error', 'Lỗi', 'Sản phẩm không hợp lệ.');
                return;
            }
            sendCartRequest('add', { product_id: productId, quantity: 1 }, (data) => {
                btn.disabled = false;
                updateCartUI(data);
            });
        });

        handleButton('.quick-view', btn => async () => {
            try {
                const res = await fetch(`/product/${btn.getAttribute('data-product-id')}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                document.querySelector('.product-modal-body').innerHTML = await res.text();
                document.querySelector('.product-modal').classList.add('active');
            } catch (err) {
                console.error('Quick View Error:', err);
                showAlert('error', 'Lỗi', 'Đã có lỗi khi xem nhanh sản phẩm.');
            }
        });

        handleButton('.add-to-wishlist', btn => () =>
            showAlert('success', 'Thành công', `Đã thêm sản phẩm ${btn.getAttribute('data-product-id')} vào danh sách yêu thích.`,
                { timer: 1500, showConfirmButton: false }));

        document.querySelector('.product-modal-close')?.addEventListener('click', () =>
            document.querySelector('.product-modal').classList.remove('active'));

        // Handle product card clicks for pagination-loaded cards
        document.querySelector('.products-slider').addEventListener('click', (e) => {
            const card = e.target.closest('.product-card');
            if (card && !e.target.closest('.product-action-btn, .add-to-cart')) {
                const href = card.getAttribute('href');
                const productId = card.getAttribute('data-product-id');
                console.log('Product card clicked:', {
                    href,
                    productId,
                    tagName: card.tagName,
                    target: e.target.tagName,
                    isAnchor: card.tagName === 'A'
                });
                if (href && href !== '/product/' && href !== '/product/null' && productId && productId !== 'null') {
                    console.log('Navigating to:', href);
                    window.location.href = href;
                } else {
                    console.error('Navigation failed:', { href, productId });
                    showAlert('error', 'Lỗi', 'Sản phẩm không hợp lệ. Vui lòng thử lại.');
                }
            }
        });
    };

    // Xử lý sự kiện trên các mục trong giỏ hàng
    if (cartItems) {
        cartItems.addEventListener('click', e => {
            if (!window.isLoggedIn) return requireLogin('Vui lòng đăng nhập để tiếp tục!');
            const target = e.target;
            const productId = target.getAttribute('data-product-id');
            if (!productId) return;

            if (target.classList.contains('quantity-btn')) {
                const input = cartItems.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                let qty = parseInt(input.value);
                target.disabled = true;

                if (target.classList.contains('decrease')) {
                    if (qty > 1) {
                        qty--;
                        sendCartRequest('update', { product_id: productId, quantity: qty }, data => {
                            target.disabled = false;
                            updateCartUI(data);
                        });
                    } else {
                        sendCartRequest('remove', { product_id: productId }, data => {
                            target.disabled = false;
                            updateCartUI(data);
                        });
                    }
                    input.value = qty;
                } else if (target.classList.contains('increase') && qty < parseInt(input.max)) {
                    qty++;
                    sendCartRequest('update', { product_id: productId, quantity: qty }, data => {
                        target.disabled = false;
                        updateCartUI(data);
                    });
                    input.value = qty;
                }
            } else if (target.classList.contains('cart-item-remove')) {
                e.preventDefault();
                target.disabled = true;
                sendCartRequest('remove', { product_id: productId }, data => {
                    target.disabled = false;
                    updateCartUI(data);
                });
            }
        });

        cartItems.addEventListener('change', e => {
            if (!window.isLoggedIn) return requireLogin('Vui lòng đăng nhập để tiếp tục!');
            if (e.target.classList.contains('quantity-input')) {
                const productId = e.target.getAttribute('data-product-id');
                let qty = parseInt(e.target.value);
                if (isNaN(qty) || qty <= 0) {
                    sendCartRequest('remove', { product_id: productId }, updateCartUI);
                } else if (qty > parseInt(e.target.max)) {
                    qty = parseInt(e.target.max);
                    e.target.value = qty;
                    showAlert('warning', 'Thông báo', `Số lượng tối đa là ${e.target.max} sản phẩm.`);
                    sendCartRequest('update', { product_id: productId, quantity: qty }, updateCartUI);
                } else {
                    sendCartRequest('update', { product_id: productId, quantity: qty }, updateCartUI);
                }
            }
        });
    }

    // Phân trang AJAX
    const initPagination = () => {
        document.querySelectorAll('.pagination .page-link').forEach(link => {
            link.addEventListener('click', async e => {
                e.preventDefault();
                const url = link.getAttribute('href');
                if (!url) return;
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    const slider = document.querySelector('.products-slider');
                    slider.innerHTML = data.products?.length ? data.products.map(product => `
                    <a href="/product/${product.id}" class="product-card" role="listitem" data-product-id="${product.id}">
                        <div class="product-image">
                            <img src="/images/${product.ImageURL}" alt="${product.ProductName}" loading="lazy" width="200" height="200">
                            ${product.DiscountPercentage ? '<span class="product-badge">Sale!</span>' : ''}
                            <div class="product-actions">
                                <button class="product-action-btn quick-view" data-product-id="${product.id}" aria-label="Quick view"><i class="fas fa-eye"></i></button>
                                <button class="product-action-btn add-to-wishlist" data-product-id="${product.id}" aria-label="Add to wishlist"><i class="far fa-heart"></i></button>
                            </div>
                        </div>
                        <div class="product-details">
                            <div class="product-title">${product.ProductName}</div>
                            <div class="product-rating">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                            <div class="price-container">
                                <span class="current-price">${product.CurrentPrice.toLocaleString()}₫</span>
                                ${product.DiscountPercentage ? `
                                    <div>
                                        <span class="original-price">${product.Price.toLocaleString()}₫</span>
                                        <span class="discount-badge">-${product.DiscountPercentage}%</span>
                                    </div>
                                ` : ''}
                            </div>
                            <button class="add-to-cart" data-product-id="${product.id}" data-name="${product.ProductName}" data-price="${product.CurrentPrice}">
                                <i class="fas fa-shopping-cart"></i> THÊM VÀO GIỎ
                            </button>
                        </div>
                    </a>
                `).join('') : '<p class="no-products">Không có sản phẩm nào để hiển thị.</p>';
                    document.querySelector('.pagination').innerHTML = data.pagination;
                    initProductInteractions();
                } catch (err) {
                    console.error('Pagination Error:', err);
                    showAlert('error', 'Lỗi', 'Đã có lỗi khi tải trang.');
                }
            });
        });
    };

    initProductInteractions();
    initPagination();

    // Điều hướng slider sản phẩm
    const prevBtn = document.querySelector('.nav-button.prev');
    const nextBtn = document.querySelector('.nav-button.next');
    const slider = document.querySelector('.products-slider');
    if (prevBtn && nextBtn && slider && !document.querySelector('.pagination')) {
        prevBtn.addEventListener('click', () => slider.scrollBy({ left: -300, behavior: 'smooth' }));
        nextBtn.addEventListener('click', () => slider.scrollBy({ left: 300, behavior: 'smooth' }));
    } else {
        prevBtn && (prevBtn.style.display = 'none');
        nextBtn && (nextBtn.style.display = 'none');
    }

    // Hiệu ứng cuộn hiển thị
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
});