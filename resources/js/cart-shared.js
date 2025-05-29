window.debounce = (func, wait) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
};

window.formatVND = (num) => {
    if (num == null || isNaN(num)) return '0 ₫';
    return num.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
};

document.addEventListener('DOMContentLoaded', () => {
    // Hàm hiển thị lỗi chung
    window.showError = (message, options = {}) => {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: message || 'Đã có lỗi xảy ra. Vui lòng thử lại sau.',
            confirmButtonText: 'OK',
            ...options
        });
    };

    // Hàm hiển thị thông báo thành công
    window.showSuccess = (message, options = {}) => {
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: message,
            showConfirmButton: false,
            timer: 1500,
            ...options
        });
    };

    // Hàm gửi yêu cầu AJAX đến giỏ hàng
    window.sendCartRequest = (action, data, callback, options = {}) => {
        fetch('/cart/' + action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: new URLSearchParams({ ...data }),
            signal: AbortSignal.timeout(5000)
        })
            .then(async response => {
                const text = await response.text();
                let json = {};
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON:', text);
                    window.showError('Phản hồi từ máy chủ không hợp lệ.');
                    options.errorCallback?.();
                    return;
                }

                if (!response.ok) {
                    window.showError(json.message || `Lỗi máy chủ (${response.status}).`);
                    options.errorCallback?.();
                    return;
                }

                if (json.success) {
                    if (action === 'add') {
                        window.showSuccess(options.successMessage || 'Sản phẩm đã được thêm vào giỏ hàng.');
                    }
                    callback(json, action); // truyền toàn bộ json
                } else {
                    window.showError(json.message || 'Hành động không thành công.');
                    options.errorCallback?.();
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                window.showError('Đã có lỗi xảy ra: ' + error.message);
                options.errorCallback?.();
            });
    };

    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const cartIcon = document.querySelector('.cart-icon');
    const cartCount = document.querySelector('.cart-count');
    const cartItems = document.querySelector('.cart-items');
    const cartTotal = document.querySelector('#cart-total-price');
    const buyNowButton = document.querySelector('.buy-now');
    let isAddingToCart = false;

    if (cartIcon) {
        const cartContainer = cartIcon.parentElement;
        cartContainer.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập',
                    text: 'Vui lòng đăng nhập để xem giỏ hàng!',
                    confirmButtonText: 'Đăng nhập',
                }).then(() => {
                    window.location.href = '/login-register';
                });
                return;
            }
            window.location.href = '/cart';
        });
    }

    if (!window.cartSharedInitialized) {
        window.cartSharedInitialized = true;
        addToCartButtons.forEach(button => {
            if (button.dataset.handlerAttached === "true") return;
            button._clickHandler = window.debounce(() => {
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
                    window.showError('Vui lòng đợi, yêu cầu đang được xử lý.', { icon: 'info', timer: 1000 });
                    return;
                }
                const productId = button.dataset.productId;
                const quantity = 1;
                isAddingToCart = true;
                button.disabled = true;
                button.classList.add('loading');
                window.sendCartRequest('add', { product_id: productId, quantity }, (res) => {
                    isAddingToCart = false;
                    button.disabled = false;
                    button.classList.remove('loading');
                    if (res.success) {
                        window.updateCartUI(res.data);
                    }
                }, {
                    errorCallback: () => {
                        isAddingToCart = false;
                        button.disabled = false;
                        button.classList.remove('loading');
                    }
                });
            }, 200);
            button.addEventListener('click', button._clickHandler);
            button.dataset.handlerAttached = "true";
        });
    }

    if (buyNowButton) {
        buyNowButton.removeEventListener('click', buyNowButton._clickHandler);
        buyNowButton._clickHandler = window.debounce(() => {
            if (!window.isLoggedIn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa đăng nhập',
                    text: 'Vui lòng đăng nhập để tiếp tục thanh toán.',
                    confirmButtonText: 'Đăng nhập',
                }).then(() => {
                    window.location.href = '/login-register';
                });
                return;
            }
            if (isAddingToCart) {
                window.showError('Vui lòng đợi, yêu cầu đang được xử lý.', { icon: 'info', timer: 1000 });
                return;
            }
            const productId = document.querySelector('.add-to-cart').dataset.productId;
            const quantity = 1;
            isAddingToCart = true;
            buyNowButton.disabled = true;
            window.sendCartRequest('add', { product_id: productId, quantity }, (res, action) => {
                isAddingToCart = false;
                buyNowButton.disabled = false;
                if (res.success && action === 'add') {
                    window.location.href = '/checkout';
                }
            }, {
                errorCallback: () => {
                    isAddingToCart = false;
                    buyNowButton.disabled = false;
                }
            });
        }, 200);
        buyNowButton.addEventListener('click', buyNowButton._clickHandler);
    }

    window.updateCartUI = (data) => {
        console.log('Updating cart UI with:', data);

        if (!data) {
            console.error('updateCartUI called without data');
            // Optionally clear cart UI or set to default state here
            if (cartCount) cartCount.textContent = 0;
            if (cartItems) {
                cartItems.innerHTML = '';
                cartItems.style.display = 'none';
            }
            if (cartTotal) cartTotal.textContent = window.formatVND(0);
            return;
        }

        sessionStorage.setItem('cartItemCount', data.itemCount);
        if (cartCount) cartCount.textContent = data.itemCount || 0;
        if (cartItems) {
            cartItems.innerHTML = '';
            if (data.cart && data.cart.length > 0) {
                if (cartEmptyMessage) cartEmptyMessage.style.display = 'none';
                cartItems.style.display = 'block';
                data.cart.forEach(item => {
                    const cartItem = document.createElement('div');
                    cartItem.classList.add('cart-item');
                    cartItem.innerHTML = `
                        <img src="/images/${item.ImageURL}" alt="${item.ProductName}" class="cart-item-image">
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.ProductName}</div>
                            <div class="cart-item-price">${window.formatVND(item.Price)}</div>
                            <div class="cart-item-quantity">
                                <button class="quantity-btn decrease" data-product-id="${item.ProductID}">-</button>
                                <input type="number" class="quantity-input" value="${item.Quantity}" min="1" max="${item.Stock}" data-product-id="${item.ProductID}">
                                <button class="quantity-btn increase" data-product-id="${item.ProductID}">+</button>
                                <a href="#" class="cart-item-remove" data-product-id="${item.ProductID}">Xóa</a>
                            </div>
                        </div>
                    `;
                    cartItems.appendChild(cartItem);
                });

                const quantityButtons = cartItems.querySelectorAll('.quantity-btn');
                quantityButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const productId = button.dataset.productId;
                        const input = button.closest('.cart-item-quantity').querySelector('.quantity-input');
                        let quantity = parseInt(input.value);
                        const max = parseInt(input.max);
                        if (button.classList.contains('decrease') && quantity > 1) {
                            quantity--;
                        } else if (button.classList.contains('increase') && quantity < max) {
                            quantity++;
                        }
                        input.value = quantity;
                        window.sendCartRequest('update', { product_id: productId, quantity }, (res) => {
                            if (res.success) {
                                window.updateCartUI(res.data);
                            }
                        });
                    });
                });

                const removeButtons = cartItems.querySelectorAll('.cart-item-remove');
                removeButtons.forEach(button => {
                    button.removeEventListener('click', button._removeHandler);
                    button._removeHandler = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (window.location.pathname === '/cart') return;

                        if (!window.isLoggedIn) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Chưa đăng nhập',
                                text: 'Vui lòng đăng nhập để tiếp tục.',
                                confirmButtonText: 'Đăng nhập',
                            }).then(() => {
                                window.location.href = '/login-register';
                            });
                            return;
                        }

                        const productId = button.getAttribute('data-product-id');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Xác nhận xóa',
                            text: 'Bạn có chắc muốn xóa sản phẩm này?',
                            showCancelButton: true,
                            confirmButtonText: 'Xóa',
                            cancelButtonText: 'Hủy'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                button.disabled = true;
                                window.sendCartRequest('remove', { product_id: productId }, (res) => {
                                    button.disabled = false;
                                    if (res.success) {
                                        window.updateCartUI(res.data);
                                        window.showSuccess('Sản phẩm đã được xóa khỏi giỏ hàng.');
                                    }
                                }, {
                                    errorCallback: () => {
                                        button.disabled = false;
                                    }
                                });
                            }
                        });
                    };
                    button.addEventListener('click', button._removeHandler);
                });

                const quantityInputs = cartItems.querySelectorAll('.quantity-input');
                quantityInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        const productId = input.dataset.productId;
                        let quantity = parseInt(input.value);
                        const max = parseInt(input.max);
                        if (isNaN(quantity) || quantity < 1) {
                            quantity = 1;
                            input.value = 1;
                        } else if (quantity > max) {
                            quantity = max;
                            input.value = max;
                            window.showError(`Số lượng tối đa là ${max} sản phẩm.`, { icon: 'warning' });
                        }
                        window.sendCartRequest('update', { product_id: productId, quantity }, (res) => {
                            if (res.success) {
                                window.updateCartUI(res.data);
                            }
                        });
                    });
                });
            } else {
                if (cartEmptyMessage) cartEmptyMessage.style.display = 'block';
                cartItems.style.display = 'none';
                cartItems.innerHTML = '';
            }
        }
        if (cartTotal) {
            cartTotal.textContent = window.formatVND(data.total || 0);
        }
    };
});
