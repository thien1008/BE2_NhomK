console.log('scripts-checkout.js loaded');

// Hàm định dạng tiền tệ
function formatVND(amount) {
    if (typeof amount !== 'number') amount = parseFloat(amount) || 0;
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Load thông tin đơn hàng từ server
function loadOrderSummary(cartItems, subtotal, shippingFee, discount) {
    console.log('🚀 cartItems:', cartItems);
    const orderItemsContainer = document.getElementById('orderItems');
    orderItemsContainer.innerHTML = '';

    cartItems.forEach(item => {
        const price = parseFloat(item.Price) || 0;
        const quantity = parseInt(item.Quantity) || 0;
        const itemTotal = price * quantity;

        const orderItem = document.createElement('div');
        orderItem.className = 'order-item';
        orderItem.innerHTML = `
            <img src="/images/${item.ImageURL}" alt="${item.ProductName}" class="item-image">
            <div class="item-details">
                <div class="item-name">${item.ProductName}</div>
                <div class="item-quantity">Số lượng: ${quantity}</div>
            </div>
            <div class="item-price">${formatVND(itemTotal)}</div>
        `;
        orderItemsContainer.appendChild(orderItem);
    });

    subtotal = parseFloat(subtotal) || 0;
    shippingFee = parseFloat(shippingFee) || 0;
    discount = parseFloat(discount) || 0;
    const total = subtotal + shippingFee - discount;

    document.getElementById('subtotal').textContent = formatVND(subtotal);
    document.getElementById('shippingFee').textContent = formatVND(shippingFee);
    document.getElementById('discountRow').style.display = discount > 0 ? 'flex' : 'none';
    document.getElementById('discountAmount').textContent = '-' + formatVND(discount);
    document.getElementById('totalAmount').textContent = formatVND(total);
}

// Áp dụng mã giảm giá
function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    if (!couponCode) {
        Swal.fire({
            title: 'Lỗi',
            text: 'Vui lòng nhập mã giảm giá!',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    fetch('/checkout/apply-coupon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ coupon_code: couponCode })
    })
        .then(response => response.json())
        .then(data => {
            console.log('🛒 cart response:', data);
            if (data.success) {
                document.getElementById('discountRow').style.display = 'flex';
                document.getElementById('discountAmount').textContent = '-' + formatVND(data.discount);
                document.getElementById('totalAmount').textContent = formatVND(data.total);
                Swal.fire({
                    title: 'Thành công',
                    text: 'Áp dụng mã giảm giá thành công!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Lỗi',
                    text: data.message || 'Mã giảm giá không hợp lệ!',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Lỗi',
                text: 'Có lỗi xảy ra khi áp dụng mã giảm giá!',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

// Xử lý thanh toán
window.processCheckout = function () {
    if (!window.isLoggedIn) {
        Swal.fire({
            title: 'Lỗi',
            text: 'Vui lòng đăng nhập để tiếp tục!',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = '/login-register';
        });
        return;
    }

    const form = document.getElementById('checkoutForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    fetch('/cart/get', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log('🛒 cart response:', data);
            if (!data.success || data.itemCount === 0) {
                Swal.fire({
                    title: 'Lỗi',
                    text: 'Giỏ hàng của bạn đang trống!',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '/cart';
                });
                return;
            }

            document.getElementById('loadingOverlay').style.display = 'flex';

            const formData = new FormData(form);
            const checkoutData = {
                payment_method: formData.get('paymentMethod'),
                coupon_code: document.getElementById('couponCode').value.trim()
            };

            fetch('/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(checkoutData)
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `Server returned ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    if (data.success) {
                        Swal.fire({
                            title: 'Thành công',
                            text: 'Đặt hàng thành công! Mã đơn hàng: ' + data.order_id,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = `/order/details/${data.order_id}`;
                        });
                    } else {
                        Swal.fire({
                            title: 'Lỗi',
                            text: data.message || 'Có lỗi xảy ra khi đặt hàng!',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    console.error('Checkout Error:', error);
                    Swal.fire({
                        title: 'Lỗi',
                        text: 'Có lỗi xảy ra khi đặt hàng: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        })
        .catch(error => {
            console.error('Cart Check Error:', error);
            Swal.fire({
                title: 'Lỗi',
                text: 'Không thể kiểm tra giỏ hàng!',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
};

// Quay lại giỏ hàng
function goBack() {
    window.location.href = '/cart';
}

// Khởi tạo trang
document.addEventListener('DOMContentLoaded', function () {
    const checkoutButton = document.getElementById('checkout-btn');
    if (checkoutButton) {
        checkoutButton.addEventListener('click', window.processCheckout);
    }

    fetch('/cart/get', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadOrderSummary(data.cart, data.total, 30000, 0);
            } else {
                Swal.fire({
                    title: 'Lỗi',
                    text: 'Không thể tải giỏ hàng!',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Lỗi',
                text: 'Có lỗi xảy ra khi tải giỏ hàng!',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
});
