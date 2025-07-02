<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>

    {{-- Logo Tab --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <style>
        body {
            background-color: #121212;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-area img {
            width: 130px; /* ✅ Diperbesar */
            margin-bottom: 24px;
            filter: drop-shadow(0 0 6px #ff4b2b);
        }

        .login-box {
            background: #1e1e1e;
            padding: 30px 25px;
            border-radius: 16px;
            box-shadow: 0 0 10px rgba(255, 75, 43, 0.3);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }

        .login-box h2 {
            color: #ffffff;
            font-size: 24px;
            margin-bottom: 24px;
        }

        .input-group {
            position: relative;
            margin-bottom: 18px;
        }

        .input-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .input-group input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 1px solid #333;
            border-radius: 10px;
            background-color: #2a2a2a;
            color: #fff;
            outline: none;
            transition: border 0.3s ease;
        }

        .input-group input:focus {
            border-color: #ff4b2b;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-button:hover {
            background: linear-gradient(to right, #ff4b2b, #ff416c);
        }

        .forgot-password,
        .register-link {
            margin-top: 14px;
        }

        .forgot-password a {
            font-size: 14px;
            color: #facc15; /* Kuning */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-link a {
            font-size: 14px;
            color: #ff4b2b; /* Merah terang untuk register */
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #fff176;
        }

        .register-link a:hover {
            color: #ff7d63;
            color:#facc15
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    {{-- Error Laravel (untuk SweetAlert) --}}
    <div id="error-messages-data" style="display: none;" 
        data-errors="{{ json_encode($errors->all()) }}">
    </div>

    <div class="login-container">
        <div class="logo-area">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo KasKN">
        </div>

        <div class="login-box">
            <h2>Admin Login</h2>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>

            <div class="forgot-password">
                <a href="{{ route('ShowPass') }}">Lupa Kata Sandi?</a>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const errors = JSON.parse(document.getElementById('error-messages-data')?.dataset.errors || "[]");
            if (errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    html: errors.join('<br>'),
                    confirmButtonColor: '#ff4b2b'
                });
            }
        });
    </script>
</body>
</html>
