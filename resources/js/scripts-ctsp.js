document.addEventListener('DOMContentLoaded', () => {
    const formatVND = num => num.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    const showAlert = (icon, title, text, options = {}) => Swal.fire({ icon, title, text, confirmButtonText: 'OK', ...options });

    const sendCartRequest = async (action, data) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showAlert('error', 'Lỗi', 'Thiếu CSRF token.');
            return null;
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
                return null;
            }
            return json;
        } catch (error) {
            console.error('Cart Error:', error);
            showAlert('error', 'Lỗi kết nối', 'Đã có lỗi xảy ra: ' + error.message);
            return null;
        }
    };

    // Quantity controls
    const quantityInput = document.querySelector('#quantity');
    const decreaseBtn = document.querySelector('.quantity-btn.decrease');
    const increaseBtn = document.querySelector('.quantity-btn.increase');
    const addToCartBtn = document.querySelector('.add-to-cart');

    if (quantityInput && decreaseBtn && increaseBtn) {
        const updateQuantity = (newQty) => {
            if (!window.isLoggedIn) {
                showAlert('warning', 'Yêu cầu đăng nhập', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!', {
                    willClose: () => window.location.href = '/login-register'
                });
                return false;
            }
            const max = parseInt(quantityInput.max);
            if (isNaN(newQty) || newQty < 1) {
                quantityInput.value = 1;
                return false;
            }
            if (newQty > max) {
                quantityInput.value = max;
                showAlert('warning', 'Thông báo', `Số lượng tối đa là ${max} sản phẩm.`);
                return false;
            }
            quantityInput.value = newQty;
            return true;
        };

        decreaseBtn.addEventListener('click', () => {
            updateQuantity(parseInt(quantityInput.value) - 1);
        });

        increaseBtn.addEventListener('click', () => {
            updateQuantity(parseInt(quantityInput.value) + 1);
        });

        quantityInput.addEventListener('change', () => {
            updateQuantity(parseInt(quantityInput.value));
        });
    }

    // Image zoom effect
    const mainImage = document.querySelector('.main-image img');
    if (mainImage) {
        mainImage.addEventListener('mousemove', (e) => {
            const rect = mainImage.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            mainImage.style.transformOrigin = `${x}% ${y}%`;
            mainImage.style.transform = 'scale(1.5)';
        });
        mainImage.addEventListener('mouseleave', () => {
            mainImage.style.transform = 'scale(1)';
        });
    }
});