document.addEventListener('DOMContentLoaded', () => {
    // Hàm tiện ích: Định dạng tiền tệ VND
    const formatVND = num => num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₫';

    // Hàm hiển thị thông báo bằng Swal
    const showAlert = (icon, title, text, options = {}) => Swal.fire({
        icon, title, text, confirmButtonText: 'OK', ...options
    });

    // Fallback: Hàm gửi yêu cầu AJAX đến server để xử lý giỏ hàng
    const sendCartRequest = async (action, data, callback) => {
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
            if (!response.ok) {
                showAlert('error', 'Lỗi', json.message || `Lỗi máy chủ (${response.status}).`);
                return;
            }
            if (json.success) {
                callback(json);
            } else {
                showAlert('error', 'Thất bại', json.message || 'Hành động không thành công.');
            }
        } catch (error) {
            console.error('Cart Error:', error);
            showAlert('error', 'Lỗi kết nối', 'Đã có lỗi xảy ra: ' + error.message);
            callback({ total: 0, itemCount: 0, cart: [] });
        }
    };

    // Hàm bật/tắt dropdown
    const toggleDropdown = (dropdown, state) => {
        if (dropdown) {
            dropdown.classList.toggle('active', state);
            const trigger = dropdown.previousElementSibling || dropdown.parentElement.querySelector('.nav-link');
            trigger?.setAttribute('aria-expanded', state);
        }
    };

    // Hàm đóng tất cả dropdown trừ dropdown được chỉ định
    const closeAllDropdowns = except => {
        document.querySelectorAll('.dropdown-menu, .dropdown-search, .user-dropdown, .cart-dropdown')
            .forEach(d => d !== except && toggleDropdown(d, false));
    };

    // Hàm debounce để trì hoãn thực thi
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    };

    // Xử lý dropdown
    const setupDropdown = (trigger, dropdown, isMobileClick = false) => {
        let isHoveringTrigger = false;
        let isHoveringDropdown = false;

        const handleToggle = (e, forceState) => {
            const isMobile = window.innerWidth <= 768;
            if (isMobile && e.type === 'click' && isMobileClick) {
                e.preventDefault();
                closeAllDropdowns(dropdown); // Đóng các dropdown khác
                toggleDropdown(dropdown, forceState ?? !dropdown.classList.contains('active'));
            } else if (!isMobile) {
                if (e.type === 'mouseenter') {
                    if (e.target === trigger) {
                        isHoveringTrigger = true;
                    } else if (e.target === dropdown || dropdown.contains(e.target)) {
                        isHoveringDropdown = true;
                    }
                    closeAllDropdowns(dropdown);
                    toggleDropdown(dropdown, true);
                } else if (e.type === 'mouseleave') {
                    if (e.target === trigger) {
                        isHoveringTrigger = false;
                    } else if (e.target === dropdown || dropdown.contains(e.target)) {
                        isHoveringDropdown = false;
                    }
                    // Chỉ đóng dropdown nếu chuột rời cả trigger và dropdown
                    setTimeout(() => {
                        if (!isHoveringTrigger && !isHoveringDropdown) {
                            toggleDropdown(dropdown, false);
                        }
                    }, 100); // Thêm độ trễ nhỏ để kiểm tra trạng thái
                }
            }
        };

        // Gắn sự kiện cho trigger
        trigger.addEventListener('mouseenter', handleToggle);
        trigger.addEventListener('mouseleave', handleToggle);
        trigger.addEventListener('click', handleToggle);
        trigger.addEventListener('keydown', e => (e.key === 'Enter' || e.key === ' ') && handleToggle(e));

        // Gắn sự kiện cho dropdown
        dropdown.addEventListener('mouseenter', handleToggle);
        dropdown.addEventListener('mouseleave', handleToggle);
    };

    // Xử lý mouseleave trên toàn bộ header
    const header = document.querySelector('header');
    if (header) {
        header.addEventListener('mouseleave', (e) => {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            let isMouseInDropdown = false;

            dropdowns.forEach(dropdown => {
                if (dropdown.contains(e.relatedTarget)) {
                    isMouseInDropdown = true;
                }
            });

            if (!isMouseInDropdown) {
                closeAllDropdowns();
            }
        });
    }

    // Trong phần tạo dropdown
    document.querySelectorAll('nav a.nav-link').forEach(item => {
        const category = item.textContent.trim().toLowerCase();
        if (category === 'home') return; // Bỏ qua danh mục "home"
        if (!categoriesFromDB[category] || !Array.isArray(categoriesFromDB[category]) || categoriesFromDB[category].length === 0) {
            console.warn(`Không tìm thấy sản phẩm cho danh mục: ${category}`);
            return;
        }

        const dropdown = document.createElement('div');
        dropdown.classList.add('dropdown-menu');
        dropdown.id = `dropdown-${category}`;
        item.setAttribute('aria-controls', dropdown.id);
        item.setAttribute('aria-expanded', 'false');

        const container = document.createElement('div');
        container.classList.add('dropdown-menu-container');

        // Giới hạn tối đa 28 sản phẩm
        const limitedProducts = categoriesFromDB[category].slice(0, 28);

        // Tạo lưới 4 cột x 7 hàng (28 ô)
        for (let i = 0; i < 28; i++) {
            const link = document.createElement('a');
            if (i < limitedProducts.length) {
                link.href = `/product/${limitedProducts[i].ProductID}`;
                link.textContent = limitedProducts[i].ProductName || limitedProducts[i].name || 'Unknown Product';
            } else {
                link.classList.add('empty');
            }
            container.appendChild(link);
        }

        dropdown.appendChild(container);
        item.parentElement.appendChild(dropdown);
        setupDropdown(item, dropdown, false);
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
            console.log('Search keyword:', value);
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
                console.log('Search results:', data); // Thêm log
                searchResults.innerHTML = '';
                noResults && (noResults.style.display = data.length ? 'none' : 'block');
                const regex = new RegExp(`(${value.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                data.forEach(product => {
                    const result = document.createElement('a');
                    result.classList.add('search-result');
                    result.href = `/product/${product.ProductID}`;
                    const currentPrice = !isNaN(Number(product.CurrentPrice))
                        ? Number(product.CurrentPrice).toLocaleString()
                        : '0';
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

    if (cartContainer && cartDropdown && cartItems) { // Thêm kiểm tra cartItems
        let isCartVisible = false;

        const fetchCart = () => {
            if (!window.isLoggedIn) return;
            cartItems.style.display = 'block';
            cartEmpty && (cartEmpty.style.display = 'none');
            sendCartRequest('get', {}, data => {
                updateCartUI(data);
                isCartVisible = true;
            });
        };

        const updateCartUI = ({ total = 0, itemCount = 0, cart = [] }) => {
            cartCount && (cartCount.textContent = itemCount);

            if (!Array.isArray(cart) || cart.length === 0) {
                const existingItems = cartItems.querySelectorAll('.cart-dropdown-item');
                existingItems.forEach(item => item.remove());
                cartEmpty && (cartEmpty.style.display = 'block');
            } else {
                cartEmpty && (cartEmpty.style.display = 'none');
                const existingItems = cartItems.querySelectorAll('.cart-dropdown-item');
                existingItems.forEach(item => item.remove());
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

            cartTotalPrice && (cartTotalPrice.textContent = formatVND(total));
            isCartVisible = true;
        };

        const handleCartToggle = (e, forceState) => {
            e.stopPropagation();
            e.preventDefault();
            const isMobile = window.innerWidth <= 768;
            if (isMobile && (e.type === 'click' || e.key === 'Enter' || e.key === ' ') && window.isLoggedIn) {
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

    checkoutBtn?.addEventListener('click', () => {
        if (!window.isLoggedIn) return showAlert('warning', 'Yêu cầu đăng nhập', 'Vui lòng đăng nhập để thanh toán!', {
            willClose: () => window.location.href = '/login-register'
        });
        sendCartRequest('get', {}, data => {
            if (data.success && data.cart?.length) window.location.href = '/checkout';
            else showAlert('warning', 'Giỏ hàng trống', 'Vui lòng thêm sản phẩm trước khi thanh toán.');
        });
    });

    // Hàm yêu cầu đăng nhập
    const requireLogin = (message) => showAlert('warning', 'Yêu cầu đăng nhập', message, {
        willClose: () => window.location.href = '/login-register'
    });

    const initProductInteractions = () => {
        const handleButton = (selector, callback) => document.querySelectorAll(selector).forEach(btn =>
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
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

            const quantityInput = btn.closest('.product-details')?.querySelector('.quantity-input') || document.querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;

            const max = quantityInput ? parseInt(quantityInput.max) : Infinity;
            if (quantity > max) {
                btn.disabled = false;
                showAlert('warning', 'Thông báo', `Số lượng tối đa là ${max} sản phẩm.`);
                return;
            }

            sendCartRequest('add', { product_id: productId, quantity }, (data) => {
                btn.disabled = false;
                if (data?.success) {
                    showAlert('success', 'Thành công', 'Sản phẩm đã được thêm vào giỏ hàng!', { timer: 1500 });
                    updateCartUI(data);
                    document.querySelector('.cart-count').textContent = data.itemCount || 0;
                    document.querySelector('#cart-empty').style.display = data.itemCount > 0 ? 'none' : 'block';
                } else {
                    showAlert('error', 'Thất bại', data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                }
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
            showAlert('success', 'Thành công', `Đã thêm sản phẩm ${btn.getAttribute('data-product-id')} vào danh sách sản phẩm.`, {
                timer: 1500, showConfirmButton: false
            }));

        document.querySelector('.product-modal-close')?.addEventListener('click', () =>
            document.querySelector('.product-modal').classList.remove('active'));

        const productsSlider = document.querySelector('.products-slider');
        if (productsSlider) {
            productsSlider.addEventListener('click', (e) => {
                const card = e.target.closest('.product-card');
                if (card && !e.target.closest('.product-action-btn, .add-to-cart, .quantity-btn, .quantity-input')) {
                    const href = card.querySelector('a[href]')?.getAttribute('href');
                    const productId = card.getAttribute('data-product-id');
                    if (href && href !== '/product/' && href !== '/product/null' && productId && productId !== 'null') {
                        window.location.href = href;
                    } else {
                        console.error('Navigation failed:', { href, productId });
                        showAlert('error', 'Lỗi', 'Sản phẩm không hợp lệ. Vui lòng thử lại.');
                    }
                }
            });
        }
    };

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
                    sendCartRequest('update', { product_id: productId, quantity: qty }, (data) => {
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

    // Hàm áp dụng bộ lọc giá cho All Products
    const applyFilter = () => {
        const filterValue = document.getElementById('price-filter').value;
        const containerId = 'product-list';
        const container = document.getElementById(containerId);

        if (!container) return;

        fetch(`/home/filter-products?filter=${encodeURIComponent(filterValue)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.text())
            .then(html => {
                if (!html || html.trim() === '') {
                    throw new Error('Empty response from server');
                }
                container.innerHTML = html;
                initProductInteractions();
                container.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Filter Error:', error);
                showAlert('error', 'Lỗi', 'Không thể tải danh sách sản phẩm. Vui lòng thử lại.');
            });
    };

    // Xử lý sắp xếp theo danh mục
    const handleCategorySort = () => {
        document.querySelectorAll('select[name="sort"][data-category]').forEach(select => {
            select.addEventListener('change', async function () {
                const category = this.dataset.category;
                const sort = this.value;
                const containerId = `${category.toLowerCase()}-product-list`;
                const container = document.getElementById(containerId);
                const paginationContainer = document.querySelector(`.pagination-container[data-category="${category}"]`);

                if (!container || !paginationContainer) {
                    console.error(`Container #${containerId} or pagination container not found`);
                    showAlert('error', 'Lỗi', `Không tìm thấy container cho danh mục ${category}.`);
                    return;
                }

                try {
                    const response = await fetch(`/products/filter?category=${encodeURIComponent(category)}&sort=${encodeURIComponent(sort)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    if (!response.ok) {
                        const text = await response.text();
                        console.error('Non-OK response:', response.status, text);
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    if (!data.html || data.html.trim() === '') {
                        throw new Error('Empty response from server');
                    }

                    container.innerHTML = data.html;
                    paginationContainer.innerHTML = data.pagination || '';
                    initProductInteractions();
                } catch (error) {
                    console.error('Sort Error:', error);
                    showAlert('error', 'Lỗi', 'Không thể tải sản phẩm. Vui lòng thử lại.');
                }
            });
        });
    };

    // Hàm xử lý phân trang với event delegation
    const handlePagination = () => {
        document.addEventListener('click', async (e) => {
            const link = e.target.closest('.pagination-container a.page-link');
            if (!link) return;

            e.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page') || '1';
            const paginationContainer = link.closest('.pagination-container');
            if (!paginationContainer) {
                console.error('No pagination container found');
                showAlert('error', 'Lỗi', 'Không tìm thấy container phân trang.');
                return;
            }

            const category = paginationContainer.dataset.category || 'all';
            const containerId = paginationContainer.dataset.containerId;
            const container = document.getElementById(containerId);
            const sortSelect = document.querySelector(`select[name="sort"][data-category="${category}"]`) || document.getElementById('price-filter');
            const sort = sortSelect && sortSelect.value ? sortSelect.value : '';

            if (!container) {
                console.error(`Container #${containerId} not found`);
                showAlert('error', 'Lỗi', `Không tìm thấy container cho danh mục ${category}.`);
                return;
            }

            try {
                const response = await fetch(`/products/filter?category=${encodeURIComponent(category)}&sort=${encodeURIComponent(sort)}&page=${page}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) {
                    const text = await response.text();
                    console.error('Non-OK response:', response.status, text);
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                let data;
                try {
                    data = await response.json();
                } catch (error) {
                    const text = await response.text();
                    console.error('Invalid JSON response:', text);
                    throw new Error('Server returned invalid JSON response');
                }

                if (!data.html || data.html.trim() === '') {
                    throw new Error('Empty response from server');
                }

                container.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination || '';
                window.history.pushState({}, '', `?${new URLSearchParams({ page, sort, category: category !== 'all' ? category : '' }).toString()}`);
                initProductInteractions();
                container.scrollIntoView({ behavior: 'smooth' });
            } catch (error) {
                console.error('Pagination Error:', error);
                showAlert('error', 'Lỗi', 'Không thể tải trang sản phẩm. Vui lòng thử lại.');
            }
        });
    };

    // Handle back/forward navigation
    window.addEventListener('popstate', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || '1';
        const sort = urlParams.get('sort') || '';
        const category = urlParams.get('category') || 'all';
        const container = document.getElementById(`${category.toLowerCase()}-product-list`) || document.getElementById('product-list');
        const paginationContainer = document.querySelector(`.pagination-container[data-category="${category}"]`);

        if (!container || !paginationContainer) return;

        fetch(`/products/filter?category=${encodeURIComponent(category)}&sort=${encodeURIComponent(sort)}&page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination || '';
                initProductInteractions();
                container.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Popstate Error:', error);
                showAlert('error', 'Lỗi', 'Không thể tải trang sản phẩm. Vui lòng thử lại.');
            });
    });

    // Khởi tạo
    initProductInteractions();
    document.getElementById('price-filter')?.addEventListener('change', applyFilter);
    handleCategorySort();
    handlePagination();

    const observer = new IntersectionObserver((entries, node) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                node.unobserve(entry.target);
            }
        });
    }, {});

    document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
});

// Xử lý nút điều hướng cho products-slider
document.querySelectorAll('.products-slider').forEach(slider => {
    const promoContainer = slider.closest('.promo-container');
    if (!promoContainer) return; // tránh lỗi nếu không tìm thấy

    const prevButton = promoContainer.querySelector('.nav-button.prev');
    const nextButton = promoContainer.querySelector('.nav-button.next');

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            slider.scrollBy({ left: -300, behavior: 'smooth' });
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', () => {
            slider.scrollBy({ left: 300, behavior: 'smooth' });
        });
    }
});
