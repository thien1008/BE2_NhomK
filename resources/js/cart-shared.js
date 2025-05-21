document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const cartIcon = document.querySelector('.cart-icon');
    const cartCount = document.querySelector('.cart-count');
    const cartItems = document.querySelector('.cart-items');
    const cartTotal = document.querySelector('#cart-total-price');
    const cartModal = document.querySelector('.cart-modal');
    const closeCartButton = document.querySelector('.cart-modal-close');
    const checkoutButton = document.querySelector('.cart-checkout-btn');
    const buyNowButton = document.querySelector('.buy-now');

    // Hàm định dạng tiền tệ VNĐ
    function formatVND(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' VNĐ';
    }

    // Xử lý sự kiện nhấn vào biểu tượng giỏ hàng
    if (cartIcon) {
        const cartContainer = cartIcon.parentElement;
        cartContainer.addEventListener('click', () => {
            cartModal.classList.toggle('active');
            if (cartModal.classList.contains('active')) {
                sendCartRequest('get', {}, updateCartUI);
            }
        });
    }

    // Xử lý nút Thêm vào giỏ hàng
    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                window.location.href = 'login-register';
                return;
            }

            const productId = button.dataset.productId;
            const quantityInput = document.querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;

            sendCartRequest('add', { product_id: productId, quantity }, updateCartUI);
        });
    });

    // Xử lý nút Đóng modal giỏ hàng
    if (closeCartButton) {
        closeCartButton.addEventListener('click', () => {
            cartModal.classList.remove('active');
        });
    }

    // Xử lý click bên ngoài modal để đóng
    if (cartModal) {
        cartModal.addEventListener('click', e => {
            if (e.target === cartModal) {
                cartModal.classList.remove('active');
            }
        });
    }

    // Xử lý nút Thanh Toán
    if (checkoutButton) {
        checkoutButton.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                alert('Vui lòng đăng nhập để tiếp tục thanh toán.');
                window.location.href = '';
                return;
            }

            // Kiểm tra giỏ hàng có sản phẩm hay không
            fetch('cart_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'get' })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.cart && data.cart.length > 0) {
                        window.location.href = 'checkout.php';
                    } else {
                        alert('Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã có lỗi xảy ra khi kiểm tra giỏ hàng.');
                });
        });
    }

    // Xử lý nút Mua Ngay
    if (buyNowButton) {
        buyNowButton.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                alert('Vui lòng đăng nhập để tiếp tục thanh toán.');
                window.location.href = 'login-register';
                return;
            }

            const productId = document.querySelector('.add-to-cart').dataset.productId;
            const quantityInput = document.querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;

            // Thêm sản phẩm vào giỏ hàng
            sendCartRequest('add', { product_id: productId, quantity }, (data) => {
                if (data.success) {
                    // Chuyển hướng đến checkout.php
                    window.location.href = 'checkout.php';
                } else {
                    alert(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                }
            });
        });
    }

    const sendCartRequest = (action, data, callback) => {
        fetch('cart_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action, ...data })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        if (action === 'add') {
                            alert('Sản phẩm đã được thêm vào giỏ hàng!');
                        }
                        if (action !== 'get') {
                            sendCartRequest('get', {}, callback);
                        } else {
                            callback(data);
                        }
                    } else if (action === 'remove' && data.message.includes('Không tìm thấy')) {
                        // Bỏ qua thông báo lỗi nếu sản phẩm đã bị xóa
                        sendCartRequest('get', {}, callback);
                    } else {
                        if (data.message.includes('đăng nhập')) {
                            alert('Vui lòng đăng nhập để tiếp tục.');
                            window.location.href = 'login-register';
                        } else {
                            alert(data.message || 'Hành động không thành công.');
                        }
                    }
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    alert('Lỗi: Phản hồi từ server không hợp lệ. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã có lỗi xảy ra: ' + error.message);
            });
    };

    const updateCartUI = (data) => {
        const cartEmptyMessage = document.querySelector('.cart-empty');
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
                        <img src="public/img/${item.ImageURL}" alt="${item.ProductName}" class="cart-item-image">
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

                // Thêm sự kiện cho nút tăng/giảm và xóa
                const quantityButtons = cartItems.querySelectorAll('.quantity-btn');
                quantityButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const productId = button.getAttribute('data-product-id');
                        const input = cartItems.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                        let quantity = parseInt(input.value);

                        // Vô hiệu hóa nút để tránh trùng lặp yêu cầu
                        button.disabled = true;

                        if (button.classList.contains('decrease')) {
                            if (quantity > 1) {
                                quantity--;
                                sendCartRequest('update', { product_id: productId, quantity }, (data) => {
                                    button.disabled = false;
                                    updateCartUI(data);
                                });
                            } else {
                                sendCartRequest('remove', { product_id: productId }, (data) => {
                                    button.disabled = false;
                                    updateCartUI(data);
                                });
                            }
                        } else if (button.classList.contains('increase') && quantity < parseInt(input.max)) {
                            quantity++;
                            sendCartRequest('update', { product_id: productId, quantity }, (data) => {
                                button.disabled = false;
                                updateCartUI(data);
                            });
                        }
                        input.value = quantity;
                    });
                });

                const removeButtons = cartItems.querySelectorAll('.cart-item-remove');
                removeButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const productId = button.getAttribute('data-product-id');
                        button.disabled = true;
                        sendCartRequest('remove', { product_id: productId }, (data) => {
                            button.disabled = false;
                            updateCartUI(data);
                        });
                    });
                });

                const quantityInputs = cartItems.querySelectorAll('.quantity-input');
                quantityInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        const productId = input.getAttribute('data-product-id');
                        let quantity = parseInt(input.value);
                        if (isNaN(quantity) || quantity <= 0) {
                            sendCartRequest('remove', { product_id: productId }, updateCartUI);
                            return;
                        } else if (quantity > parseInt(input.max)) {
                            quantity = parseInt(input.max);
                            input.value = input.max;
                            alert(`Số lượng tối đa là ${input.max} sản phẩm.`);
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

    if (cartModal && cartModal.classList.contains('active')) {
        sendCartRequest('get', {}, updateCartUI);
    }
});