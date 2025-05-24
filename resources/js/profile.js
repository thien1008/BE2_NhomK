document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', () => {
        const tabId = button.getAttribute('data-tab');
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        button.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    });
});

document.getElementById('user-profile-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    const formData = new FormData(form);
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            credentials: 'include'
        });
        if (response.status === 419) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Phiên làm việc đã hết hạn. Vui lòng làm mới trang và thử lại.',
            });
            return;
        }
        if (!response.ok && response.status >= 300 && response.status < 400) {
            const redirectUrl = response.headers.get('Location');
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: `Yêu cầu bị chuyển hướng (mã ${response.status}) đến ${redirectUrl}. Vui lòng đăng nhập lại.`,
            });
            console.log('Redirect URL:', redirectUrl);
            return;
        }
        const result = await response.json();
        if (response.ok && result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: result.message || 'Cập nhật thông tin thành công!',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                html: Object.values(result.errors || { general: 'Có lỗi xảy ra khi cập nhật.' }).join('<br>'),
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi gửi yêu cầu: ' + error.message,
        });
        console.error('Fetch error:', error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});

function toggleOrderDetails(orderId) {
    const detailsElement = document.getElementById(`order-details-${orderId}`);
    document.querySelectorAll('.order-details.show').forEach(element => {
        if (element !== detailsElement) {
            element.classList.remove('show');
        }
    });
    detailsElement.classList.toggle('show');
}

document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('focus', function () {
        this.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});