document.addEventListener('DOMContentLoaded', () => {
    // Hàm định dạng tiền tệ VND
    const formatVND = (num) => {
        if (num == null || isNaN(num)) return '0 ₫';
        return num.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    };

    // Hàm gửi yêu cầu AJAX đến giỏ hàng
    const sendCartRequest = (action, data, callback) => {
        fetch('/cart/' + action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: new URLSearchParams({ ...data })
        })
            .then(async response => {
                const text = await response.text();
                console.log(`Response from /cart/${action}:`, text);
                let json = {};
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON:', text);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Phản hồi từ máy chủ không hợp lệ. Vui lòng thử lại sau.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (!response.ok) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: json.message || `Lỗi máy chủ (${response.status}). Vui lòng thử lại sau.`,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (json.success && action === 'add') {
                    // Hiển thị thông báo ngay sau khi add thành công
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Sản phẩm đã được thêm vào giỏ hàng.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    // Gọi get để cập nhật giao diện
                    sendCartRequest('get', {}, (data) => {
                        if (data.success) {
                            callback(data, action);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: data.message || 'Không thể lấy dữ liệu giỏ hàng.',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                } else if (json.success) {
                    sendCartRequest('get', {}, (data) => callback(data, action));
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: json.message || 'Hành động không thành công.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Đã có lỗi xảy ra: ' + error.message,
                    confirmButtonText: 'OK'
                });
            });
    };

    // Cập nhật xử lý addToCartButtons
    if (!window.cartSharedInitialized) {
        window.cartSharedInitialized = true;
        addToCartButtons.forEach(button => {
            if (button.dataset.handlerAttached === "true") return;
            button.replaceWith(button.cloneNode(true));
            button = document.querySelector(`[data-product-id="${button.dataset.productId}"]`);
            button._clickHandler = debounce(() => {
                if (!window.isLoggedIn) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Chưa đăng nhập',
                        text: 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!',
                        confirmButtonText: 'Đăng nhập',
                    }).then(() => {
                        window.location.href = '/login-register';
                    });
                    return;
                }
                if (isAddingToCart) {
                    console.log('Add to cart request already in progress');
                    return;
                }
                const productId = button.dataset.productId;
                const quantity = 1;
                isAddingToCart = true;
                button.disabled = true;
                sendCartRequest('add', { product_id: productId, quantity }, (data, action) => {
                    isAddingToCart = false;
                    button.disabled = false;
                    if (data.success) {
                        updateCartUI(data);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: data.message || 'Không thể thêm sản phẩm vào giỏ hàng.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }, 500);
            button.addEventListener('click', button._clickHandler);
            button.dataset.handlerAttached = "true";
        });
    }

    const cartItemsList = document.querySelector('#cart-items-list');
    const subtotal = document.querySelector('#subtotal');
    const total = document.querySelector('#total');
    const cartCountDisplay = document.querySelector('.cart-count-display');
    const couponCodeInput = document.querySelector('#coupon-code');
    const applyCouponBtn = document.querySelector('.apply-coupon-btn');
    const checkoutBtn = document.querySelector('#checkout-btn');
    const clearCartBtn = document.querySelector('.clear-cart-btn');
    const searchInput = document.getElementById('search-input');
    const dropdownSearch = document.getElementById('dropdown-search');
    const searchResults = document.getElementById('search-results');
    const noResults = dropdownSearch?.querySelector('.no-results');

    // Biến để theo dõi sản phẩm cần xóa
    let pendingRemoveProductId = null;

    const updateQuantity = (productId, quantity) => {
        if (!productId) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'ID sản phẩm không hợp lệ.',
                confirmButtonText: 'OK'
            });
            return;
        }
        sendCartRequest('update', { product_id: productId, quantity }, (data) => {
            if (!data?.cart || !Array.isArray(data.cart)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Dữ liệu giỏ hàng không hợp lệ.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            const item = data.cart.find(i => i.ProductID == productId);
            if (item && typeof item.CurrentPrice === 'number' && typeof item.Quantity === 'number') {
                const cartItem = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (cartItem) {
                    cartItem.querySelector('.item-subtotal').textContent = formatVND(item.CurrentPrice * item.Quantity);
                }
                subtotal.textContent = formatVND(data.total);
                total.textContent = formatVND(data.total);
                sessionStorage.setItem('cartTotal', data.total);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Dữ liệu sản phẩm không hợp lệ.',
                    confirmButtonText: 'OK'
                });
            }
        });
    };

    const removeItem = (productId) => {
        sendCartRequest('remove', { product_id: productId }, (data) => {
            if (data?.success) {
                document.querySelector(`.cart-item[data-id="${productId}"]`)?.remove();
                if (typeof data.total !== 'number' || typeof data.itemCount !== 'number') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Dữ liệu giỏ hàng không hợp lệ.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                subtotal.textContent = formatVND(data.total);
                total.textContent = formatVND(data.total);
                cartCountDisplay.textContent = `${data.itemCount} sản phẩm`;
                document.querySelector('.cart-count').textContent = data.itemCount;
                sessionStorage.setItem('cartTotal', data.total);
                sessionStorage.setItem('cartItemCount', data.itemCount);
                if (data.itemCount === 0) {
                    cartItemsList.innerHTML = `
                    <div class="cart-empty">
                        <i class="fas fa-shopping-cart fa-4x"></i>
                        <h2>Giỏ hàng của bạn đang trống</h2>
                        <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                        <a href="/" class="btn-continue-shopping">Tiếp tục mua sắm</a>
                    </div>
                `;
                    document.querySelector('.cart-summary').style.display = 'none';
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Sản phẩm đã được xóa khỏi giỏ hàng.',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: data?.message || 'Không thể xóa sản phẩm.',
                    confirmButtonText: 'OK'
                });
            }
        });
    };

    if (cartItemsList) {
        cartItemsList.addEventListener('click', (e) => {
            if (!window.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập',
                    text: 'Vui lòng đăng nhập để tiếp tục!',
                    confirmButtonText: 'Đăng nhập',
                }).then(() => {
                    window.location.href = '/login-register';
                });
                return;
            }
            const cartItem = e.target.closest('.cart-item');
            const productId = cartItem?.getAttribute('data-id');
            if (!productId) return;

            if (e.target.classList.contains('quantity-btn')) {
                const input = cartItem.querySelector('.quantity-input');
                let quantity = parseInt(input.value);
                const max = parseInt(input.max);
                if (e.target.classList.contains('decrease') && quantity > 1) {
                    quantity--;
                } else if (e.target.classList.contains('increase') && quantity < max) {
                    quantity++;
                }
                input.value = quantity;
                updateQuantity(productId, quantity);
            } else if (e.target.closest('.remove-item')) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Xác nhận xóa',
                    text: 'Bạn có chắc muốn xóa sản phẩm này?',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        removeItem(productId);
                    }
                });
            } else if (!e.target.closest('.quantity-btn, .quantity-input, .remove-item')) {
                window.location.href = `/product/${productId}`;
            }
        });

        cartItemsList.addEventListener('change', (e) => {
            if (!window.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập',
                    text: 'Vui lòng đăng nhập để tiếp tục!',
                    confirmButtonText: 'Đăng nhập',
                }).then(() => {
                    window.location.href = '/login-register';
                });
                return;
            }
            if (e.target.classList.contains('quantity-input')) {
                const productId = e.target.getAttribute('data-id');
                let quantity = parseInt(e.target.value);
                const max = parseInt(e.target.max);
                if (isNaN(quantity) || quantity <= 0) {
                    if (pendingRemoveProductId === productId) {
                        removeItem(productId);
                    } else {
                        pendingRemoveProductId = productId;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cảnh báo',
                            text: 'Số lượng không hợp lệ. Nhấn lại để xóa sản phẩm này.',
                            confirmButtonText: 'OK',
                            showCancelButton: true,
                            cancelButtonText: 'Hủy'
                        });
                    }
                } else if (quantity > max) {
                    e.target.value = max;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cảnh báo',
                        text: `Số lượng tối đa là ${max} sản phẩm.`,
                        confirmButtonText: 'OK'
                    });
                    updateQuantity(productId, max);
                } else {
                    updateQuantity(productId, quantity);
                }
            }
        });
    }

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập',
                    text: 'Vui lòng đăng nhập để tiếp tục!',
                    confirmButtonText: 'Đăng nhập',
                }).then(() => {
                    window.location.href = '/login-register';
                });
                return;
            }
            sendCartRequest('clear', {}, (data) => {
                if (data?.success) {
                    cartItemsList.innerHTML = `
                        <div class="cart-empty">
                            <i class="fas fa-shopping-cart fa-4x"></i>
                            <h2>Giỏ hàng của bạn đang trống</h2>
                            <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                            <a href="/" class="btn-continue-shopping">Tiếp tục mua sắm</a>
                        </div>
                    `;
                    subtotal.textContent = formatVND(0);
                    total.textContent = formatVND(0);
                    cartCountDisplay.textContent = '0 sản phẩm';
                    sessionStorage.setItem('cartTotal', 0);
                    sessionStorage.setItem('cartItemCount', 0);
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Giỏ hàng đã được xóa.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    document.querySelector('.cart-summary').style.display = 'none';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: data?.message || 'Không thể xóa giỏ hàng.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    }

    if (applyCouponBtn && couponCodeInput) {
        applyCouponBtn.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập',
                    text: 'Vui lòng đăng nhập để tiếp tục!',
                    confirmButtonText: 'Đăng nhập',
                }).then(() => {
                    window.location.href = '/login-register';
                });
                return;
            }
            const couponCode = couponCodeInput.value;
            if (!couponCode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cảnh báo',
                    text: 'Vui lòng nhập mã giảm giá!',
                    confirmButtonText: 'OK'
                });
                return;
            }
            sendCartRequest('apply-coupon', { code: couponCode }, (data) => {
                if (data.success) {
                    document.querySelector('#discount-row').style.display = 'flex';
                    document.querySelector('#discount-amount').textContent = formatVND(data.discount || 0);
                    total.textContent = formatVND(data.total);
                    sessionStorage.setItem('cartTotal', data.total);
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Mã giảm giá đã được áp dụng!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: data.message || 'Mã giảm giá không hợp lệ.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    }

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập',
                    text: 'Vui lòng đăng nhập để tiếp tục!',
                    confirmButtonText: 'Đăng nhập',
                }).then(() => {
                    window.location.href = '/login-register';
                });
                return;
            }
            sendCartRequest('get', {}, (data) => {
                if (data.itemCount === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cảnh báo',
                        text: 'Giỏ hàng của bạn đang trống!',
                        confirmButtonText: 'OK'
                    });
                } else {
                    window.location.href = '/checkout';
                }
            });
        });
    }

    if (searchInput && dropdownSearch && searchResults) {
        const toggleDropdown = (dropdown, state) => {
            if (dropdown) {
                dropdown.classList.toggle('active', state);
                const trigger = dropdown.previousElementSibling || dropdown.parentElement;
                trigger?.setAttribute('aria-expanded', state);
            }
        };

        searchInput.addEventListener('focus', () => toggleDropdown(dropdownSearch, true));

        const performSearch = debounce(async value => {
            if (!value.trim()) {
                searchResults.innerHTML = '';
                noResults && (noResults.style.display = 'block');
                toggleDropdown(dropdownSearch, false);
                return;
            }
            const cacheKey = `search_${value.trim()}`;
            const cachedResults = sessionStorage.getItem(cacheKey);
            if (cachedResults) {
                updateSearchResults(JSON.parse(cachedResults));
                return;
            }
            try {
                const res = await fetch(`?search=${encodeURIComponent(value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                sessionStorage.setItem(cacheKey, JSON.stringify(data));
                updateSearchResults(data);
            } catch (err) {
                console.error('Search Error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Đã có lỗi khi tìm kiếm.',
                    confirmButtonText: 'OK'
                });
            }
        }, 300);

        const updateSearchResults = (data) => {
            searchResults.innerHTML = '';
            noResults && (noResults.style.display = data.length ? 'none' : 'block');
            const regex = new RegExp(`(${value.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            const fragment = document.createDocumentFragment();
            data.forEach(product => {
                const result = document.createElement('a');
                result.classList.add('search-result');
                result.href = `/product/${product.ProductID}`;
                const currentPrice = !isNaN(Number(product.CurrentPrice)) ? Number(product.CurrentPrice).toLocaleString() : '0';
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
                fragment.appendChild(result);
            });
            searchResults.appendChild(fragment);
            toggleDropdown(dropdownSearch, true);
        };

        searchInput.addEventListener('input', e => {
            searchInput.setAttribute('placeholder', e.target.value.trim() ? '' : 'Tìm kiếm sản phẩm...');
            performSearch(e.target.value);
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('.search-container')) toggleDropdown(dropdownSearch, false);
        });
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '50px' });

    document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));

    if (window.cartData && window.cartTotal) {
        const initialData = { itemCount: window.cartData.length, cart: window.cartData, total: window.cartTotal };
        subtotal.textContent = formatVND(initialData.total);
        total.textContent = formatVND(initialData.total);
        cartCountDisplay.textContent = `${initialData.itemCount} sản phẩm`;
        sessionStorage.setItem('cartTotal', initialData.total);
        sessionStorage.setItem('cartItemCount', initialData.itemCount);
    }
});