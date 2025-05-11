<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
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

        .forgot-password-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            width: 450px;
            max-width: 100%;
            transition: var(--transition);
        }

        .forgot-password-container:hover {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        }
        
        .separator {
            color: #ddd;
            margin: 0 8px;
            font-weight: 300;
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--text-color);
            text-align: center;
            font-weight: 500;
        }

        .page-subtitle {
            font-size: 15px;
            color: var(--light-text);
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.5;
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

        .forgot-password-form-group {
            margin-bottom: 24px;
        }

        .forgot-password-form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--light-text);
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password-form-group input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            transition: var(--transition);
        }

        .forgot-password-form-group input[type="email"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.15);
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

        .actions-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .submit-button {
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

        .submit-button:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .forgot-password-actions {
            text-align: center;
        }

        .forgot-password-actions a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
        }

        .forgot-password-actions a:hover {
            color: var(--accent-color);
        }

        .forgot-password-actions a::before {
            content: "←";
            margin-right: 6px;
            display: inline-block;
        }

        @media (max-width: 480px) {
            .forgot-password-container {
                padding: 30px 20px;
            }
            
            .forgot-password-header h2 {
                font-size: 16px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h1 class="page-title">Quên mật khẩu?</h1>
        <p class="page-subtitle">Vui lòng nhập email của bạn để nhận hướng dẫn đặt lại mật khẩu</p>
        
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
        
        <form action="{{ route('password.email') }}" method="post" class="forgot-password-form">
            @csrf
            <div class="forgot-password-form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập địa chỉ email của bạn" required>
            </div>
            
            <p class="recaptcha-text">This site is protected by reCAPTCHA and the Google <a href="#">Privacy Policy</a> and <a href="#">Terms of Service</a> apply.</p>
            
            <div class="actions-container">
                <button type="submit" class="submit-button">Gửi email</button>
                <div class="forgot-password-actions">
                    <a href="{{ route('login') }}">Quay lại đăng nhập</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>