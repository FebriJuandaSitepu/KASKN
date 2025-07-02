<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
    body {
        background-color: #121212;
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .register-container {
        background: #1e1e1e;
        padding: 40px 30px;
        border-radius: 18px;
        box-shadow: 0 0 14px rgba(255, 75, 43, 0.25);
        max-width: 420px;
        width: 100%;
        box-sizing: border-box;
    }

    .register-container h2 {
        color: #fff;
        font-size: 24px;
        text-align: center;
        margin-bottom: 28px;
    }

    .input-group {
        margin-bottom: 18px;
    }

    .input-group input {
        width: 100%;
        padding: 14px 18px;
        border-radius: 10px;
        border: 1px solid #333;
        background-color: #2a2a2a;
        color: #fff;
        font-size: 15px;
        box-sizing: border-box;
    }

    .input-group input:focus {
        border-color: #ff4b2b;
        background-color: #2f2f2f;
    }

    .register-button {
        width: 100%;
        padding: 14px;
        background: linear-gradient(to right, #ff416c, #ff4b2b);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        margin-top: 8px;
    }

    .register-button:hover {
        background: linear-gradient(to right, #ff4b2b, #ff416c);
    }

    .login-link {
        margin-top: 20px;
        text-align: center;
        font-size: 14px;
        color: #bfbfbf;
    }

    .login-link a {
        color: #facc15;
        font-weight: 600;
        text-decoration: none;
    }

    .login-link a:hover {
        color: #fff176;
    }
</style>

    </style>
</head>
<body>
    {{-- SweetAlert Error --}}
    <div id="error-messages-data" style="display: none;" 
        data-errors="{{ json_encode($errors->all()) }}">
    </div>

    <div class="register-container">
        <h2>Daftar Admin</h2>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="input-group">
                <input type="text" name="name" placeholder="Nama" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-group">
                <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required>
            </div>
            <button type="submit" class="register-button">Daftar</button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const errors = JSON.parse(document.getElementById('error-messages-data')?.dataset.errors || "[]");
            if (errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Daftar',
                    html: errors.join('<br>'),
                    confirmButtonColor: '#ff4b2b'
                });
            }
        });
    </script>
</body>
</html>
