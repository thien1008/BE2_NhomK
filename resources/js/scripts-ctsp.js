document.addEventListener('DOMContentLoaded', () => {
    // Hàm định dạng tiền tệ VND
    const formatVND = (num) => {
        if (num == null || isNaN(num)) return '0 ₫';
        return num.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    };

    // Hàm hiển thị thông báo bằng SweetAlert2
    const showAlert = (icon, title, text, options = {}) => {
        Swal.fire({ icon, title, text, confirmButtonText: 'OK', ...options });
    };

    // Hàm gửi yêu cầu AJAX đến giỏ hàng
    const sendCartRequest = async (action, data, callback = null) => {
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
            if (json.success && callback) {
                callback(json);
            }
            return json;
        } catch (error) {
            console.error(`Cart ${action} Error:`, error);
            showAlert('error', 'Lỗi kết nối', 'Đã có lỗi xảy ra: ' + error.message);
            return null;
        }
    };

    // Hàm debounce để trì hoãn thực thi
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    };

    // Lấy các phần tử DOM
    const quantityInput = document.querySelector('#quantity');
    const decreaseBtn = document.querySelector('.quantity-btn.decrease');
    const increaseBtn = document.querySelector('.quantity-btn.increase');
    const addToCartBtn = document.querySelector('.action-btn.add-to-cart');

    // Kiểm tra sự tồn tại của các phần tử
    if (!quantityInput || !decreaseBtn || !increaseBtn || !addToCartBtn) {
        console.error('Thiếu phần tử DOM:', { quantityInput, decreaseBtn, increaseBtn, addToCartBtn });
        showAlert('error', 'Lỗi', 'Không tìm thấy các phần tử điều khiển số lượng!');
        return;
    }

    // Kiểm tra CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('Thiếu CSRF token.');
        showAlert('error', 'Lỗi', 'Thiếu CSRF token.');
        return;
    }

    // Hàm kiểm tra tồn kho (tạm thời bỏ qua để test)
    const checkStock = async (productId, quantity) => {
        return true; // Bỏ qua kiểm tra tồn kho để kiểm tra nút
        /*
        try {
            const response = await fetch(`/product/${productId}/stock`, {
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Không thể kiểm tra tồn kho');
            }
            return data.stock >= quantity;
        } catch (error) {
            console.error('Stock check error:', error);
            showAlert('error', 'Lỗi', 'Không thể kiểm tra tồn kho. Vui lòng thử lại!');
            return false;
        }
        */
    };

    // Hàm cập nhật số lượng
    const updateQuantity = async (newQty) => {
        if (!window.isLoggedIn) {
            showAlert('warning', 'Yêu cầu đăng nhập', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!', {
                willClose: () => window.location.href = '/login-register'
            });
            return false;
        }

        const max = parseInt(quantityInput.max) || Infinity;
        const min = parseInt(quantityInput.min) || 1;
        const productId = addToCartBtn.getAttribute('data-product-id');

        if (!productId) {
            console.error('Thiếu ID sản phẩm.');
            showAlert('error', 'Lỗi', 'ID sản phẩm không hợp lệ!');
            return false;
        }

        // Kiểm tra tồn kho
        const isStockAvailable = await checkStock(productId, newQty);
        if (!isStockAvailable) {
            showAlert('warning', 'Thông báo', `Số lượng tối đa là ${quantityInput.max} sản phẩm.`);
            quantityInput.value = quantityInput.max || min;
            return false;
        }

        newQty = Math.max(min, Math.min(newQty, max));

        if (newQty === max && max !== Infinity) {
            showAlert('warning', 'Thông báo', `Số lượng tối đa là ${max} sản phẩm.`);
        } else if (newQty === min && newQty < parseInt(quantityInput.value)) {
            showAlert('warning', 'Thông báo', `Số lượng tối thiểu là ${min}.`);
        }

        quantityInput.value = newQty;
        sessionStorage.setItem('quantity', newQty);

        // Cập nhật trạng thái nút
        decreaseBtn.disabled = newQty <= min;
        increaseBtn.disabled = newQty >= max;

        return true;
    };

    // Gắn sự kiện cho nút tăng/giảm
    decreaseBtn.addEventListener('click', async () => {
        const newQty = parseInt(quantityInput.value) - 1;
        console.log('Nút giảm được nhấn, số lượng mới:', newQty);
        await updateQuantity(newQty);
    });

    increaseBtn.addEventListener('click', async () => {
        const newQty = parseInt(quantityInput.value) + 1;
        console.log('Nút tăng được nhấn, số lượng mới:', newQty);
        await updateQuantity(newQty);
    });

    // Xử lý nhập tay số lượng
    quantityInput.addEventListener('input', debounce(async () => {
        let newQty = parseInt(quantityInput.value);
        console.log('Nhập số lượng, số lượng mới:', newQty);
        if (isNaN(newQty) || newQty < 1) {
            showAlert('warning', 'Thông báo', 'Vui lòng nhập số lượng hợp lệ.');
            newQty = 1;
        }
        await updateQuantity(newQty);
    }, 300));

    // Ngăn nhập ký tự không phải số
    quantityInput.addEventListener('keypress', (e) => {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
            showAlert('warning', 'Thông báo', 'Chỉ được nhập số!');
        }
    });

    // Khởi tạo trạng thái ban đầu
    console.log('Khởi tạo số lượng:', quantityInput.value);
    updateQuantity(parseInt(quantityInput.value) || 1);
});