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
                {{-- Master God Mode Toggle --}}
                @if(auth()->user()->isMaster())
                <form method="POST" action="{{ route('master.toggle-god-mode') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-icon {{ session('master_god_mode') ? 'active' : '' }}" 
                            style="{{ session('master_god_mode') ? 'color: var(--accent-dark); background: rgba(52, 211, 153, 0.1);' : '' }}"
                            title="God Mode: Edit Riwayat Transaksi">
                        <i class="ri-shield-flash-fill"></i>
                    </button>
                </form>
                @endif

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
    {{-- Custom Confirm Modal --}}
    <div id="custom-confirm-modal" class="modal-overlay" style="display:none;">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h3 id="modal-title">Konfirmasi</h3>
            </div>
            <div class="modal-body">
                <p id="modal-message">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Batal</button>
                <button type="button" id="modal-confirm-btn" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.2s ease-out;
        }
        .modal-content.modal-sm {
            background: white;
            padding: 24px;
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .modal-header h3 {
            margin-bottom: 12px;
            color: var(--text-primary);
            font-size: 1.25rem;
        }
        .modal-body p {
            color: var(--text-secondary);
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>

    <script>
        let currentFormToSubmit = null;

        function confirmDelete(form, title = 'Hapus Data', message = 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.') {
            currentFormToSubmit = form;
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-message').innerText = message;
            document.getElementById('custom-confirm-modal').style.display = 'flex';
            
            // Focus confirm button
            setTimeout(() => {
                document.getElementById('modal-confirm-btn').focus();
            }, 100);
        }

        function closeConfirmModal() {
            document.getElementById('custom-confirm-modal').style.display = 'none';
            currentFormToSubmit = null;
        }

        document.getElementById('modal-confirm-btn').addEventListener('click', function() {
            if (currentFormToSubmit) {
                currentFormToSubmit.submit();
            }
            closeConfirmModal();
        });

        // Close on ESC
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeConfirmModal();
        });
    </script>
</body>
</html>
