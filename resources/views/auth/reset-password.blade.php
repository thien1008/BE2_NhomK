<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <style>
        :root {
            --primary-color: #4a6cf7;
            --accent-color: #3d5af1;
            --text-color: #333;
            --light-text: #666;
            --border-color: #e0e0e0;
            --background: #f8f9fa;
            --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --error-color: #f44336;
            --success-color: #4caf50;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', 'Segoe UI', sans-serif;
            background-color: var(--background);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
            padding: 20px;
        }

        .reset-password-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            width: 450px;
            max-width: 100%;
            transition: var(--transition);
        }

        .reset-password-container:hover {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        }

        .reset-password-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .reset-password-header h1 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 12px;
            font-weight: 600;
        }

        .reset-password-header p {
            font-size: 15px;
            color: var(--light-text);
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .reset-password-header .divider {
            height: 3px;
            width: 60px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            margin: 0 auto;
            border-radius: 3px;
        }

        .reset-password-form {
            margin-top: 30px;
        }

        .reset-password-form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .reset-password-form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--light-text);
            font-size: 14px;
            font-weight: 500;
        }

        .reset-password-form-group input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            transition: var(--transition);
        }

        .reset-password-form-group input[type="password"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.15);
        }

        .error-message {
            background-color: #ffebee;
            color: var(--error-color);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-message {
            background-color: #e8f5e9;
            color: var(--success-color);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .recaptcha-text {
            font-size: 12px;
            color: var(--light-text);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .recaptcha-text a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .recaptcha-text a:hover {
            text-decoration: underline;
        }

        .save-button {
            background-color: var(--primary-color);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .save-button:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .save-button:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .reset-password-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-header">
            <h1>Đặt lại mật khẩu</h1>
            <p>Vui lòng tạo mật khẩu mới cho tài khoản của bạn</p>
            <div class="divider"></div>
        </div>
        
        @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif
        
        @if(session('status'))
            <div class="success-message">
                {{ session('status') }}
            </div>
        @endif
        
        <form action="{{ route('password.update') }}" method="post" class="reset-password-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            
            <div class="reset-password-form-group">
                <label for="new-password">Mật khẩu mới</label>
                <input type="password" id="new-password" name="new_password" placeholder="Nhập mật khẩu mới" required minlength="8">
                @error('new_password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="reset-password-form-group">
                <label for="new-password-confirm">Xác nhận mật khẩu</label>
                <input type="password" id="new-password-confirm" name="new_password_confirmation" placeholder="Nhập lại mật khẩu mới" required minlength="8">
            </div>
            
            <p class="recaptcha-text">This site is protected by reCAPTCHA and the Google <a href="#">Privacy Policy</a> and <a href="#">Terms of Service</a> apply.</p>
            
            <button type="submit" class="save-button">Lưu mật khẩu</button>
        </form>
    </div>

    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            var password = document.getElementById('new-password').value;
            var confirmPassword = document.getElementById('new-password-confirm').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
            }
        });
    </script>
</body>
</html>