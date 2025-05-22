@extends('layouts.app')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-family: 'Kanit', sans-serif;
            font-size: 28px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-family: 'Kanit', sans-serif;
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        .form-group input[readonly] {
            background-color: #e0e0e0;
            color: #666;
            cursor: not-allowed;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .form-group .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .form-group .error.show {
            display: block;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            font-family: 'Kanit', sans-serif;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            font-family: 'Kanit', sans-serif;
            font-size: 16px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 15px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <h1>Thông Tin Cá Nhân</h1>
        <form id="user-profile-form" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="user-id">Mã Người Dùng</label>
                <input type="text" id="user-id" name="UserID" value="{{ $user->UserID }}" readonly>
            </div>

            <div class="form-group">
                <label for="full-name">Họ Tên</label>
                <input type="text" id="full-name" name="FullName" value="{{ $user->FullName }}" required>
                @error('FullName')
                    <span class="error show">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="Email" value="{{ $user->Email }}" readonly>
            </div>

            <div class="form-group">
                <label for="phone">Số Điện Thoại</label>
                <input type="text" id="phone" name="Phone" value="{{ $user->Phone }}" required>
                @error('Phone')
                    <span class="error show">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="user-type">Loại Người Dùng</label>
                <input type="text" id="user-type" name="UserType" value="{{ $user->UserType }}" readonly>
            </div>

            <div class="form-group">
                <label for="google-id">Google ID</label>
                <input type="text" id="google-id" name="GoogleID" value="{{ $user->GoogleID ?? 'Không có' }}" readonly>
            </div>

            <div class="form-group">
                <label for="created-at">Ngày Tạo</label>
                <input type="text" id="created-at" name="CreatedAt" value="{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}
    " readonly>
            </div>

            <button type="submit" class="submit-btn">Cập Nhật Thông Tin</button>
        </form>
        <a href="{{ route('home') }}" class="back-btn">Quay Về Trang Chủ</a>
    </div>

    <script>
        document.getElementById('user-profile-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const response = await fetch(form.action, {
                    method: 'POST', // Laravel converts PUT to POST internally
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: data.message || 'Cập nhật thông tin thành công!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload(); // Refresh to show updated data
                    });
                } else {
                    const errors = data.errors || { general: 'Cập nhật thất bại. Vui lòng thử lại.' };
                    let errorMessage = Object.values(errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        html: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error('Update Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Đã có lỗi xảy ra: ' + error.message,
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
@endsection