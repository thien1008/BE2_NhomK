document.addEventListener('DOMContentLoaded', () => {

    document.querySelector('#cart-items-list')?.addEventListener('click', (e) => {
        const cartItem = e.target.closest('.cart-item');
        if (cartItem && !e.target.closest('.quantity-btn, .quantity-input, .remove-item')) {
            const productId = cartItem.getAttribute('data-id');
            console.log('Cart item clicked:', { productId, url: `/product/${productId}` });
            if (productId && productId !== 'null' && productId !== '' && !isNaN(productId)) {
                window.location.href = `/product/${productId}`;
            } else {
                console.error('Invalid product ID:', productId);
                alert('Mục giỏ hàng không hợp lệ. Vui lòng thử lại.');
            }
        }
    });

    const formatVND = num => {
        if (num == null || isNaN(num)) return '0 ₫';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₫';
    };
    const showAlert = (icon, title, text) => alert(`${title}: ${text}`);

    const sendCartRequest = async (action, data, callback) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showAlert('error', 'Lỗi', 'Thiếu CSRF token.');
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
            showAlert('error', 'Lỗi kết nối', error.message);
        }
    };

    // Cập nhật số lượng
    const updateQuantity = (productId, quantity) => {
        if (!productId) {
            console.error('Invalid productId:', productId);
            showAlert('error', 'Lỗi', 'ID sản phẩm không hợp lệ.');
            return;
        }

        sendCartRequest('update', { product_id: productId, quantity }, (data) => {
            console.log('Cart update response:', data); // Debug log
            if (data && data.cart && Array.isArray(data.cart)) {
                const item = data.cart.find(i => i.ProductID == productId); // Loose comparison for type flexibility
                if (item && typeof item.CurrentPrice === 'number' && typeof item.Quantity === 'number') {
                    document.querySelector(`.cart-item[data-id="${productId}"] .item-subtotal`).textContent = formatVND(item.CurrentPrice * item.Quantity);
                    document.querySelector(`#subtotal`).textContent = formatVND(data.total);
                    document.querySelector(`#total`).textContent = formatVND(data.total);
                } else {
                    console.error('Invalid cart item data for ProductID:', productId, 'Item:', item);
                    showAlert('error', 'Lỗi', 'Không thể cập nhật số lượng sản phẩm.');
                }
            } else {
                console.error('Invalid cart data:', data);
                showAlert('error', 'Lỗi', 'Dữ liệu giỏ hàng không hợp lệ.');
            }
        });
    };

    // Xóa sản phẩm
    const removeItem = (productId) => {
        sendCartRequest('remove', { product_id: productId }, (data) => {
            const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
            if (item) item.remove();
            document.querySelector(`#subtotal`).textContent = formatVND(data.total);
            document.querySelector(`#total`).textContent = formatVND(data.total);
            document.querySelector('.cart-count-display').textContent = `${data.itemCount} sản phẩm`;
            if (data.itemCount === 0) {
                location.reload(); // Tải lại trang nếu giỏ hàng trống
            }
        });
    };

    // Xử lý nút tăng/giảm số lượng
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', () => {
            if (!window.isLoggedIn) {
                alert('Vui lòng đăng nhập để tiếp tục!');
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

    // Xử lý input số lượng
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', () => {
            if (!window.isLoggedIn) {
                alert('Vui lòng đăng nhập để tiếp tục!');
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
                alert(`Số lượng tối đa là ${max} sản phẩm.`);
                updateQuantity(productId, max);
            } else {
                updateQuantity(productId, quantity);
            }
        });
    });

    // Xử lý nút xóa
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            if (!window.isLoggedIn) {
                alert('Vui lòng đăng nhập để tiếp tục!');
                window.location.href = '/login-register';
                return;
            }

            const productId = button.getAttribute('data-id');
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                removeItem(productId);
            }
        });
    });

    // Xử lý nút Xóa giỏ hàng
    document.querySelector('.clear-cart-btn')?.addEventListener('click', () => {
        if (!window.isLoggedIn) {
            alert('Vui lòng đăng nhập để tiếp tục!');
            window.location.href = '/login-register';
            return;
        }

        if (confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
            sendCartRequest('clear', {}, (data) => {
                location.reload(); // Tải lại trang sau khi xóa
            });
        }
    });

    // Xử lý nút Cập nhật giỏ hàng
    document.querySelector('.update-cart-btn')?.addEventListener('click', () => {
        if (!window.isLoggedIn) {
            alert('Vui lòng đăng nhập để tiếp tục!');
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
                        const updatedItem = data.cart.find(i => i.ProductID === productId);
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
            alert('Giỏ hàng đã được cập nhật!');
        }
    });

    // Xử lý nút Áp dụng mã giảm giá
    document.querySelector('.apply-coupon-btn')?.addEventListener('click', () => {
        if (!window.isLoggedIn) {
            alert('Vui lòng đăng nhập để tiếp tục!');
            window.location.href = '/login-register';
            return;
        }

        const couponCode = document.querySelector('#coupon-code').value;
        if (couponCode) {
            sendCartRequest('apply-coupon', { code: couponCode }, (data) => {
                if (data.success) {
                    document.querySelector('#discount-row').style.display = 'block';
                    document.querySelector('#discount-amount').textContent = formatVND(data.discount || 0);
                    document.querySelector('#total').textContent = formatVND(data.total);
                    alert('Mã giảm giá đã được áp dụng!');
                } else {
                    alert(data.message || 'Mã giảm giá không hợp lệ.');
                }
            });
        } else {
            alert('Vui lòng nhập mã giảm giá!');
        }
    });

    // Khởi tạo UI ban đầu
    if (window.cartData && window.cartTotal) {
        const initialData = {
            itemCount: window.cartData.length,
            cart: window.cartData,
            total: window.cartTotal
        };
        // Cập nhật thủ công tổng giá ban đầu (nếu cần)
        document.querySelector('#subtotal').textContent = formatVND(initialData.total);
        document.querySelector('#total').textContent = formatVND(initialData.total);
        document.querySelector('.cart-count-display').textContent = `${initialData.itemCount} sản phẩm`;
    }

});