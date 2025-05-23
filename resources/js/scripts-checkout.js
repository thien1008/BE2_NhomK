// resources/js/scripts-checkout.js

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
    const orderItemsContainer = document.getElementById('orderItems');
    orderItemsContainer.innerHTML = '';

    cartItems.forEach(item => {
        const itemTotal = item.CurrentPrice * item.Quantity;
        const orderItem = document.createElement('div');
        orderItem.className = 'order-item';
        orderItem.innerHTML = `
            <img src="/images/${item.ImageURL}" alt="${item.ProductName}" class="item-image">
            <div class="item-details">
                <div class="item-name">${item.ProductName}</div>
                <div class="item-quantity">Số lượng: ${item.Quantity}</div>
            </div>
            <div class="item-price">${formatVND(itemTotal)}</div>
        `;
        orderItemsContainer.appendChild(orderItem);
    });

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
        alert('Vui lòng nhập mã giảm giá!');
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
        if (data.success) {
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('discountAmount').textContent = '-' + formatVND(data.discount);
            document.getElementById('totalAmount').textContent = formatVND(data.total);
            alert('Áp dụng mã giảm giá thành công!');
        } else {
            alert(data.message || 'Mã giảm giá không hợp lệ!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi áp dụng mã giảm giá!');
    });
}

// Xử lý thanh toán
function processCheckout() {
    const form = document.getElementById('checkoutForm');
    const formData = new FormData(form);

    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Show loading
    document.getElementById('loadingOverlay').style.display = 'flex';

    // Prepare checkout data
    const checkoutData = {
        customer_info: {
            fullName: formData.get('fullName'),
            phone: formData.get('phone'),
            email: formData.get('email'),
            address: formData.get('address'),
            city: formData.get('city'),
            district: formData.get('district')
        },
        payment_method: formData.get('paymentMethod'),
        order_notes: formData.get('orderNotes'),
        coupon_code: document.getElementById('couponCode').value.trim()
    };

    // Send checkout request
    fetch('/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(checkoutData)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loadingOverlay').style.display = 'none';
        
        if (data.success) {
            alert('Đặt hàng thành công! Mã đơn hàng: ' + data.order_id);
            window.location.href = '/checkout/complete';
        } else {
            alert(data.message || 'Có lỗi xảy ra khi đặt hàng!');
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').style.display = 'none';
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi đặt hàng!');
    });
}

// Quay lại giỏ hàng
function goBack() {
    window.location.href = '/cart';
}

// Load danh sách quận/huyện dựa trên tỉnh/thành phố
function loadDistricts() {
    const province = document.getElementById('city').value;
    const districtSelect = document.getElementById('district');
    districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';

    const districts = {
        'hanoi': ['Ba Đình', 'Hoàn Kiếm', 'Đống Đa'],
        'hcm': ['Quận 1', 'Quận 3', 'Quận 7'],
        'danang': ['Hải Châu', 'Thanh Khê', 'Sơn Trà'],
        'haiphong': ['Hồng Bàng', 'Ngô Quyền', 'Lê Chân']
    };

    if (districts[province]) {
        districts[province].forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        });
    }
}

// Khởi tạo trang
document.addEventListener('DOMContentLoaded', function() {
    // Load dữ liệu giỏ hàng từ server
    fetch('/cart/get', {
        method: 'GET',
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
            alert('Không thể tải giỏ hàng!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi tải giỏ hàng!');
    });

    // Auto-fill user info if logged in
    if (window.userInfo) {
        document.getElementById('fullName').value = window.userInfo.FullName || '';
        document.getElementById('email').value = window.userInfo.email || '';
        document.getElementById('phone').value = window.userInfo.Phone || '';
    }

    // Load districts when province changes
    document.getElementById('city').addEventListener('change', loadDistricts);
});