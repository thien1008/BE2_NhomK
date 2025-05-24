document.addEventListener('DOMContentLoaded', () => {
    const formatVND = num => {
        if (num == null || isNaN(num)) return '0 ₫';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₫';
    };

    const showAlert = (title, text, icon = 'error') => {
        Swal.fire({ title, text, icon, confirmButtonText: 'OK' });
    };

    const sendCartRequest = async (action, data, callback) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showAlert('Lỗi', 'Thiếu CSRF token.');
            return;
        }

        try {
            const response = await fetch(`/cart/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            });

            const json = await response.json();
            if (!response.ok) {
                showAlert('Lỗi', json.message || `Lỗi máy chủ (${response.status}).`);
                return;
            }

            if (json.success) {
                callback(json);
            } else {
                showAlert('Thất bại', json.message || 'Hành động không thành công.');
            }
        } catch (error) {
            console.error('Cart Error:', error);
            showAlert('Lỗi kết nối', error.message);
        }
    };

    // Cart item click
    document.querySelector('#cart-items-list')?.addEventListener('click', (e) => {
        const cartItem = e.target.closest('.cart-item');
        if (cartItem && !e.target.closest('.quantity-btn, .quantity-input, .remove-item')) {
            const productId = cartItem.getAttribute('data-id');
            if (productId && !isNaN(productId)) {
                window.location.href = `/product/${productId}`;
            } else {
                showAlert('Lỗi', 'ID sản phẩm không hợp lệ.');
            }
        }
    });

    // Update quantity
    const updateQuantity = (productId, quantity) => {
        if (!productId) {
            showAlert('Lỗi', 'ID sản phẩm không hợp lệ.');
            return;
        }

        sendCartRequest('update', { product_id: productId, quantity }, (data) => {
            if (data?.cart && Array.isArray(data.cart)) {
                const item = data.cart.find(i => i.ProductID == productId);
                if (item && typeof item.CurrentPrice === 'number' && typeof item.Quantity === 'number') {
                    document.querySelector(`.cart-item[data-id="${productId}"] .item-subtotal`).textContent = formatVND(item.CurrentPrice * item.Quantity);
                    document.querySelector('#subtotal').textContent = formatVND(data.total);
                    document.querySelector('#total').textContent = formatVND(data.total);
                } else {
                    showAlert('Lỗi', 'Dữ liệu sản phẩm không hợp lệ.');
                }
            } else {
                showAlert('Lỗi', 'Dữ liệu giỏ hàng không hợp lệ.');
            }
        });
    };

    // Remove item
    const removeItem = (productId) => {
        sendCartRequest('remove', { product_id: productId }, (data) => {
            document.querySelector(`.cart-item[data-id="${productId}"]`)?.remove();
            document.querySelector('#subtotal').textContent = formatVND(data.total);
            document.querySelector('#total').textContent = formatVND(data.total);
            document.querySelector('.cart-count-display').textContent = `${data.itemCount} sản phẩm`;
            if (data.itemCount === 0) {
                location.reload();
            }
        });
    };

    // Quantity buttons
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                showAlert('Lỗi', 'Vui lòng đăng nhập để tiếp tục!');
                window.location.href = '/login-register';
                return;
            }

            const productId = button.getAttribute('data-id');
            const input = document.querySelector(`.quantity-input[data-id="${productId}"]`);
            let quantity = parseInt(input.value);

            if (button.classList.contains('decrease')) {
                if (quantity > 1) quantity--;
            } else if (button.classList.contains('increase') && quantity < parseInt(input.max)) {
                quantity++;
            }
            input.value = quantity;
            updateQuantity(productId, quantity);
        });
    });

    // Quantity input
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', () => {
            if (!window.isLoggedIn) {
                showAlert('Lỗi', 'Vui lòng đăng nhập để tiếp tục!');
                window.location.href = '/login-register';
                return;
            }

            const productId = input.getAttribute('data-id');
            let quantity = parseInt(input.value);
            const max = parseInt(input.max);
            if (isNaN(quantity) || quantity <= 0) {
                removeItem(productId);
            } else if (quantity > max) {
                input.value = max;
                showAlert('Cảnh báo', `Số lượng tối đa là ${max} sản phẩm.`);
                updateQuantity(productId, max);
            } else {
                updateQuantity(productId, quantity);
            }
        });
    });

    // Remove buttons
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            if (!window.isLoggedIn) {
                showAlert('Lỗi', 'Vui lòng đăng nhập để tiếp tục!');
                window.location.href = '/login-register';
                return;
            }

            const productId = button.getAttribute('data-id');
            Swal.fire({
                title: 'Xác nhận',
                text: 'Bạn có chắc muốn xóa sản phẩm này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then(result => {
                if (result.isConfirmed) {
                    removeItem(productId);
                }
            });
        });
    });

    // Clear cart
    document.querySelector('.clear-cart-btn')?.addEventListener('click', () => {
        if (!window.isLoggedIn) {
            showAlert('Lỗi', 'Vui lòng đăng nhập để tiếp tục!');
            window.location.href = '/login-register';
            return;
        }

        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn xóa toàn bộ giỏ hàng?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(result => {
            if (result.isConfirmed) {
                sendCartRequest('clear', {}, () => location.reload());
            }
        });
    });

    // Update cart
    document.querySelector('.update-cart-btn')?.addEventListener('click', () => {
        if (!window.isLoggedIn) {
            showAlert('Lỗi', 'Vui lòng đăng nhập để tiếp tục!');
            window.location.href = '/login-register';
            return;
        }

        const items = document.querySelectorAll('.cart-item');
        let hasChanges = false;
        items.forEach(item => {
            const productId = item.getAttribute('data-id');
            const input = item.querySelector('.quantity-input');
            const quantity = parseInt(input.value);
            if (!isNaN(quantity) && quantity > 0) {
                sendCartRequest('update', { product_id: productId, quantity }, (data) => {
                    if (data.cart) {
                        const updatedItem = data.cart.find(i => i.ProductID == productId);
                        if (updatedItem) {
                            item.querySelector('.item-subtotal').textContent = formatVND(updatedItem.CurrentPrice * updatedItem.Quantity);
                            document.querySelector('#subtotal').textContent = formatVND(data.total);
                            document.querySelector('#total').textContent = formatVND(data.total);
                        }
                    }
                });
                hasChanges = true;
            }
        });
        if (hasChanges) {
            showAlert('Thành công', 'Giỏ hàng đã được cập nhật!', 'success');
        }
    });

    // Apply coupon
    document.querySelector('.apply-coupon-btn')?.addEventListener('click', () => {
        if (!window.isLoggedIn) {
            showAlert('Lỗi', 'Vui lòng đăng nhập để tiếp tục!');
            window.location.href = '/login-register';
            return;
        }

        const couponCode = document.querySelector('#coupon-code').value;
        if (couponCode) {
            sendCartRequest('apply-coupon', { code: couponCode }, (data) => {
                if (data.success) {
                    document.querySelector('#discount-row').style.display = 'flex';
                    document.querySelector('#discount-amount').textContent = formatVND(data.discount || 0);
                    document.querySelector('#total').textContent = formatVND(data.total);
                    showAlert('Thành công', 'Mã giảm giá đã được áp dụng!', 'success');
                } else {
                    showAlert('Lỗi', data.message || 'Mã giảm giá không hợp lệ.');
                }
            });
        } else {
            showAlert('Lỗi', 'Vui lòng nhập mã giảm giá!');
        }
    });

    // Checkout button
    document.querySelector('#checkout-btn')?.addEventListener('click', () => {
        if (!window.isLoggedIn) {
            showAlert('Lỗi', 'Vui lòng đăng nhập để tiếp tục!');
            window.location.href = '/login-register';
            return;
        }

        sendCartRequest('get', {}, (data) => {
            if (data.itemCount === 0) {
                showAlert('Lỗi', 'Giỏ hàng của bạn đang trống!');
            } else {
                window.location.href = '/checkout';
            }
        });
    });

    // Initialize UI
    if (window.cartData && window.cartTotal) {
        const initialData = {
            itemCount: window.cartData.length,
            cart: window.cartData,
            total: window.cartTotal
        };
        document.querySelector('#subtotal').textContent = formatVND(initialData.total);
        document.querySelector('#total').textContent = formatVND(initialData.total);
        document.querySelector('.cart-count-display').textContent = `${initialData.itemCount} sản phẩm`;
    }

    // Search Functionality (aligned with home page)
    const searchInput = document.getElementById('search-input');
    const dropdownSearch = document.getElementById('dropdown-search');
    const searchResults = document.getElementById('search-results');
    const noResults = dropdownSearch?.querySelector('.no-results');

    if (searchInput && dropdownSearch && searchResults) {
        const toggleDropdown = (dropdown, state) => {
            if (dropdown) {
                dropdown.classList.toggle('active', state);
                const trigger = dropdown.previousElementSibling || dropdown.parentElement;
                trigger?.setAttribute('aria-expanded', state);
            }
        };

        const debounce = (func, wait) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        };

        searchInput.addEventListener('focus', () => {
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
                showAlert('Lỗi', 'Đã có lỗi khi tìm kiếm.');
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

    // Scroll Reveal
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