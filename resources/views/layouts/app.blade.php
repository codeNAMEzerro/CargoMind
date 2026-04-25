<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CargoMind') - {{ setting('store_name', 'CargoMind') }}</title>
    <meta name="description" content="CargoMind - Sistem Manajemen Toko & Gudang">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="{{ session('large_font', false) ? 'large-font' : '' }}">
    {{-- ============================== --}}
    {{-- SIDEBAR NAVIGATION --}}
    {{-- ============================== --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="ri-box-3-fill"></i>
            <span>CargoMind</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                <i class="ri-dashboard-3-fill"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('pos') }}" class="nav-link {{ request()->routeIs('pos') ? 'active' : '' }}" id="nav-pos">
                <i class="ri-shopping-cart-2-fill"></i>
                <span>Kasir (POS)</span>
            </a>

            <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}" id="nav-items">
                <i class="ri-archive-2-fill"></i>
                <span>Barang</span>
            </a>

            <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" id="nav-transactions">
                <i class="ri-file-list-3-fill"></i>
                <span>Riwayat Transaksi</span>
            </a>

            @if(auth()->user()->hasRole('master') || auth()->user()->hasRole('manager'))
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" id="nav-reports">
                <i class="ri-bar-chart-box-fill"></i>
                <span>Laporan</span>
            </a>
            @endif

            @if(auth()->user()->hasRole('master'))
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" id="nav-settings">
                <i class="ri-settings-4-fill"></i>
                <span>Pengaturan</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="ri-user-3-fill"></i>
                </div>
                <div class="user-details">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-role">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout" id="btn-logout" title="Keluar">
                    <i class="ri-logout-box-r-fill"></i>
                </button>
            </form>
        </div>
    </aside>

    {{-- ============================== --}}
    {{-- MAIN CONTENT --}}
    {{-- ============================== --}}
    <main class="main-content">
        {{-- Top Bar --}}
        <header class="topbar">
            <button class="btn-toggle-sidebar" id="btn-toggle-sidebar" onclick="document.body.classList.toggle('sidebar-collapsed')">
                <i class="ri-menu-2-fill"></i>
            </button>

            <h1 class="page-title">@yield('title', 'Dashboard')</h1>

            <div class="topbar-actions">
                {{-- Large Font Toggle --}}
                <button class="btn-icon" id="btn-toggle-font" onclick="toggleLargeFont()" title="Toggle Font Besar">
                    <i class="ri-font-size-2"></i>
                </button>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success" id="alert-success">
            <i class="ri-checkbox-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button class="alert-close" onclick="this.parentElement.remove()"><i class="ri-close-fill"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error" id="alert-error">
            <i class="ri-error-warning-fill"></i>
            <span>{{ session('error') }}</span>
            <button class="alert-close" onclick="this.parentElement.remove()"><i class="ri-close-fill"></i></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error" id="alert-validation">
            <i class="ri-error-warning-fill"></i>
            <div>
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()"><i class="ri-close-fill"></i></button>
        </div>
        @endif

        {{-- Page Content --}}
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

    <script>
        // Large font toggle
        function toggleLargeFont() {
            document.body.classList.toggle('large-font');
            // Save preference
            fetch('/toggle-font', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            }).catch(() => {});
        }

        // Auto-dismiss alerts
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
    @stack('scripts')
</body>
</html>
