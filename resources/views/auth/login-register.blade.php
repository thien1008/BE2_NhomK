{{-- resources/views/auth/login-register.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {{-- <link rel="stylesheet" href="{{ asset('') }}"> --}}
    @vite(['resources/css/login-register.css'])
    <title>Login & Register</title>
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
        }

        .success-message {
            color: green;
            font-size: 0.9em;
        }

        .input-error {
            border: 1px solid red;
        }

        .form-container span {
            font-size: 12px;
            margin-top: 15px;
        }

        .social-container a {
            width: 30px;
            height: 30px;
            margin: 0 3px;
        }

        .social-container a i {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div id="container" class="container {{ session('active_tab') === 'register' ? 'right-panel-active' : '' }}">

        <!-- Register Form -->
        <div class="form-container register-container">
            <form method="POST" action="{{ route('register') }}" onsubmit="return validateRegister()">
                @csrf
                <h1>Register here.</h1>
                
                @if(session('register_success'))
                    <span class="success-message">{{ session('register_success') }}</span>
                @endif
                
                @if($errors->register->any())
                    <span class="error-message">{{ $errors->register->first() }}</span>
                @endif
                
                <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
                <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                <input type="text" name="phone" placeholder="Phone" value="{{ old('phone') }}" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Register</button>
                <span>Or use your account</span>
                <div class="social-container">
                    <a href="{{ route('auth.facebook') }}" class="social"><i class="lni lni-facebook-fill"></i></a>
                    <a href="{{ route('auth.google') }}" class="social"><i class="lni lni-google"></i></a>
                </div>
            </form>
        </div>

        <!-- Login Form -->
        <div class="form-container login-container">
            <form method="POST" action="{{ route('login') }}" onsubmit="return validateLogin()">
                @csrf
                <h1>Login here.</h1>
                
                @if($errors->has('login_error'))
                    <span class="error-message">{{ $errors->first('login_error') }}</span>
                @endif
                
                <input type="text" name="login_input" placeholder="Email or Phone" value="{{ old('login_input') }}" required>
                <input type="password" name="password" placeholder="Password" required>
                <div class="content">
                    <div class="checkbox">
                        <input type="checkbox" name="checkbox" id="checkbox">
                        <label>Remember me</label>
                    </div>
                    <div class="pass-link">
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    </div>
                </div>
                <button type="submit">Login</button>
                <span>Or use your account</span>
                <div class="social-container">
                    <a href="{{ route('auth.facebook') }}" class="social"><i class="lni lni-facebook-fill"></i></a>
                    <a href="{{ route('auth.google') }}" class="social"><i class="lni lni-google"></i></a>
                </div>
            </form>
        </div>

        <!-- Overlay -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1 class="title">Hello <br> Friends</h1>
                    <p>If you have an account, login here and have fun</p>
                    <button class="ghost" id="login">Login
                        <i class="lni lni-arrow-left login"></i>
                    </button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1 class="title">Start your <br> journey now</h1>
                    <p>If you don't have an account, join us and start your journey</p>
                    <button class="ghost" id="register">Register
                        <i class="lni lni-arrow-right login"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

     {{-- <!-- Toastr JS -->
    <script src="{{ asset('vendor/toastr/toastr.min.js') }}"></script>

    <!-- Render Toastr messages -->
    {!! Toastr::message() !!} --}}

    <script>
        const registerButton = document.getElementById("register");
        const loginButton = document.getElementById("login");
        const container = document.getElementById("container");

        registerButton.addEventListener("click", () => {
            container.classList.add("right-panel-active");
        });

        loginButton.addEventListener("click", () => {
            container.classList.remove("right-panel-active");
        });

        function validateRegister() {
            let isValid = true;
            const inputs = document.querySelectorAll('.register-container input');
            inputs.forEach(input => {
                input.classList.remove('input-error');
                const errorSpan = input.parentElement.querySelector('.error-message') || document.createElement('span');
                errorSpan.className = 'error-message';
                errorSpan.textContent = '';
                input.parentElement.insertBefore(errorSpan, input.nextSibling);

                if (input.name === 'email') {
                    if (!input.value.includes('@') || !input.value.includes('.')) {
                        errorSpan.textContent = 'Email phải có @ và domain hợp lệ';
                        input.classList.add('input-error');
                        isValid = false;
                    }
                }

                if (input.name === 'phone') {
                    const phone = input.value;
                    if (!/^\d+$/.test(phone)) {
                        errorSpan.textContent = 'Số điện thoại chỉ được chứa số';
                        input.classList.add('input-error');
                        isValid = false;
                    } else if (phone.length !== 10) {
                        errorSpan.textContent = 'Số điện thoại phải có đúng 10 số';
                        input.classList.add('input-error');
                        isValid = false;
                    }
                }

                if (!input.value.trim()) {
                    errorSpan.textContent = `${input.placeholder} không được để trống`;
                    input.classList.add('input-error');
                    isValid = false;
                }
            });
            return isValid;
        }

        function validateLogin() {
            let isValid = true;
            const inputs = document.querySelectorAll('.login-container input');
            inputs.forEach(input => {
                input.classList.remove('input-error');
                const errorSpan = input.parentElement.querySelector('.error-message') || document.createElement('span');
                errorSpan.className = 'error-message';
                errorSpan.textContent = '';
                input.parentElement.insertBefore(errorSpan, input.nextSibling);

                if (input.name === 'login_input') {
                    if (input.value !== 'admin' && (!input.value.includes('@') || !input.value.includes('.'))) {
                        errorSpan.textContent = 'Email phải có @ và domain hợp lệ (trừ admin)';
                        input.classList.add('input-error');
                        isValid = false;
                    }
                }

                if (!input.value.trim()) {
                    errorSpan.textContent = `${input.placeholder} không được để trống`;
                    input.classList.add('input-error');
                    isValid = false;
                }
            });
            return isValid;
        }
    </script>
</body>

</html>