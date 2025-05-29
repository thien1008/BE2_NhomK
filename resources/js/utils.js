// utils.js
(function () {
    const formatVND = (num) => {
        if (num == null || isNaN(num)) return '0 ₫';
        return num.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    };

    const showAlert = (icon, title, text, options = {}) => {
        Swal.fire({ icon, title, text, confirmButtonText: 'OK', ...options });
    };

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

    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    };

    // Gán vào window để dùng toàn cục
    window.utils = { formatVND, showAlert, sendCartRequest, debounce };
})();
