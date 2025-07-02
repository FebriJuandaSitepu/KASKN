<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link rel="icon" href="{{ asset('assets/logo.png') }}" type="image/png">

    {{-- Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: linear-gradient(135deg, #1e1e2f, #2b2b45);
            color: white;
            padding: 20px;
        }

        .sidebar-header h4 {
            font-weight: bold;
            color: #ffc107;
        }

        .user-profile {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background-color: #fff;
            color: #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #aaa;
            text-transform: uppercase;
            margin: 15px 0 5px;
        }

        .nav-link {
            color: #cfd8dc;
            display: flex;
            align-items: center;
            padding: 8px 10px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .nav-link i {
            margin-right: 10px;
        }

        .nav-link:hover,
        .nav-link.fw-bold {
            color: #ffc107;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar-footer {
            margin-top: auto;
        }

        .content {
    margin-left: 300px;
    width: 100%;
    padding: 0;
}

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .content {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="d-flex">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="sidebar-header text-center mb-4">
            <h4>KasKN</h4>
        </div>

        {{-- User Info --}}
        @auth
        <div class="user-profile">
            <div class="avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div>
                <div class="fw-semibold text-white">{{ Auth::user()->name }}</div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-indicator bg-success rounded-circle"></span>
                    <small class="text-success">Online</small>
                </div>
            </div>
        </div>
        @endauth

        {{-- Menu Utama --}}
        <div class="nav-section-title">Menu Utama</div>
        <ul class="nav flex-column">
    <li><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'fw-bold' : '' }}"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="{{ route('konsumen.index') }}" class="nav-link {{ request()->routeIs('konsumen.*') ? 'fw-bold' : '' }}"><i class="fa-solid fa-users"></i> Data Konsumen</a></li>
    <li><a href="{{ route('pembayaran.index') }}" class="nav-link {{ request()->routeIs('pembayaran.*') ? 'fw-bold' : '' }}"><i class="fa-solid fa-credit-card"></i> Data Pembayaran</a></li>
    <li>
  <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'fw-bold' : '' }}">
    <i class="fa-solid fa-file-alt"></i> Laporan Konsumen
  </a>
</li>

    <li><a href="{{ route('notifikasi.index') }}" class="nav-link {{ request()->routeIs('notifikasi.*') ? 'fw-bold' : '' }}"><i class="fa-solid fa-bell"></i> Notifikasi</a></li>
    <li><a href="{{ route('admin.topup') }}" class="nav-link {{ request()->routeIs('admin.topup') ? 'fw-bold' : '' }}"><i class="fa-solid fa-wallet"></i> Top Up</a></li>
</ul>

        {{-- Tentang --}}
        <div class="nav-section-title">Tentang</div>
        <ul class="nav flex-column">
            <li><a href="{{ route('informasi') }}" class="nav-link {{ request()->is('informasi') ? 'fw-bold' : '' }}"><i class="fa-solid fa-info-circle"></i> Informasi</a></li>
        </ul>

        {{-- Logout --}}
        @auth
        <div class="sidebar-footer mt-3">
          <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-danger w-100"><i class="fa-solid fa-sign-out-alt me-2"></i> Log Out</button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- Main Content --}}
    <main class="content">
        @yield('content')
    </main>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@stack('scripts')
</body>
</html>
