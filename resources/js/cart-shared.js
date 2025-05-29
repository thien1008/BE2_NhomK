document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const cartIcon = document.querySelector('.cart-icon');
    const cartCount = document.querySelector('.cart-count');
    const cartItems = document.querySelector('.cart-items');
    const cartTotal = document.querySelector('#cart-total-price');
    const checkoutButton = document.querySelector('.cart-checkout-btn');
    const buyNowButton = document.querySelector('.buy-now');

    // Track ongoing add requests to prevent duplicates
    let isAddingToCart = false;

    // Hàm định dạng tiền tệ VNĐ
    function formatVND(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' VNĐ';
    }

    // Hàm debounce để trì hoãn thực thi
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    };

    // Xử lý sự kiện nhấn vào biểu tượng giỏ hàng
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

    // Xử lý nút Thêm vào giỏ hàng
    if (!window.cartSharedInitialized) {
        window.cartSharedInitialized = true;
        addToCartButtons.forEach(button => {
            if (button.dataset.handlerAttached === "true") return;
            button.replaceWith(button.cloneNode(true)); // Xóa sự kiện cũ
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: 'Sản phẩm đã được thêm vào giỏ hàng.',
                            showConfirmButton: false,
                            timer: 1500
                        });
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

    // Xử lý nút Mua Ngay
    if (buyNowButton) {
        buyNowButton.removeEventListener('click', buyNowButton._clickHandler);
        buyNowButton._clickHandler = debounce(() => {
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
                console.log('Add to cart request already in progress');
                return;
            }

            const productId = document.querySelector('.add-to-cart').dataset.productId;
            const quantity = 1;

            isAddingToCart = true;
            buyNowButton.disabled = true;

            sendCartRequest('add', { product_id: productId, quantity }, (data, action) => {
                isAddingToCart = false;
                buyNowButton.disabled = false;

                if (data.success) {
                    if (action === 'add') {
                        window.location.href = '/checkout';
                    }
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
        }, 500); // Tăng thời gian debounce lên 500ms
        buyNowButton.addEventListener('click', buyNowButton._clickHandler);
    }

    const sendCartRequest = (action, data, callback) => {
        console.log(`Sending request to /cart/${action} with data:`, data);
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
                    console.log('Response not OK:', json);
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
                            console.log('Get request failed:', data);
                            // Không hiển thị lỗi vì add đã thành công
                            callback(data, action); // Vẫn gọi callback để cập nhật UI
                        }
                    });
                } else if (json.success) {
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
                } else {
                    console.log('Action failed:', json);
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

    window.updateCartUI = (data) => {
        console.log('Updating cart UI with:', data);
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
                        <div class="cart-item-price">${formatVND(item.Price)}</div>
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
                        sendCartRequest('update', { product_id: productId, quantity }, updateCartUI);
                    });
                });

                const removeButtons = cartItems.querySelectorAll('.cart-item-remove');
                removeButtons.forEach(button => {
                    button.removeEventListener('click', button._removeHandler);
                    button._removeHandler = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        // Skip if on the cart page
                        if (window.location.pathname === '/cart') {
                            return; // Let scripts-cart.js handle the removal
                        }
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
                                sendCartRequest('remove', { product_id: productId }, (data) => {
                                    button.disabled = false;
                                    if (data.success) {
                                        updateCartUI(data);
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
                                            text: data.message || 'Không thể xóa sản phẩm.',
                                            confirmButtonText: 'OK'
                                        });
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
                            Swal.fire({
                                icon: 'warning',
                                title: 'Cảnh báo',
                                text: `Số lượng tối đa là ${max} sản phẩm.`,
                                confirmButtonText: 'OK'
                            });
                        }
                        sendCartRequest('update', { product_id: productId, quantity }, updateCartUI);
                    });
                });
            } else {
                if (cartEmptyMessage) cartEmptyMessage.style.display = 'block';
                cartItems.style.display = 'none';
                cartItems.innerHTML = '';
            }
        }
        if (cartTotal) {
            cartTotal.textContent = data.total ? formatVND(data.total) : '0 VNĐ';
        }
    };
});