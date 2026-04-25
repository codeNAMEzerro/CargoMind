<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'CargoMind'); ?> - <?php echo e(setting('store_name', 'CargoMind')); ?></title>
    <meta name="description" content="CargoMind - Sistem Manajemen Toko & Gudang">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="<?php echo e(session('large_font', false) ? 'large-font' : ''); ?>">
    
    
    
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="ri-box-3-fill"></i>
            <span>CargoMind</span>
        </div>

        <nav class="sidebar-nav">
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" id="nav-dashboard">
                <i class="ri-dashboard-3-fill"></i>
                <span>Dashboard</span>
            </a>

            <a href="<?php echo e(route('pos')); ?>" class="nav-link <?php echo e(request()->routeIs('pos') ? 'active' : ''); ?>" id="nav-pos">
                <i class="ri-shopping-cart-2-fill"></i>
                <span>Kasir (POS)</span>
            </a>

            <a href="<?php echo e(route('items.index')); ?>" class="nav-link <?php echo e(request()->routeIs('items.*') ? 'active' : ''); ?>" id="nav-items">
                <i class="ri-archive-2-fill"></i>
                <span>Barang</span>
            </a>

            <a href="<?php echo e(route('transactions.index')); ?>" class="nav-link <?php echo e(request()->routeIs('transactions.*') ? 'active' : ''); ?>" id="nav-transactions">
                <i class="ri-file-list-3-fill"></i>
                <span>Riwayat Transaksi</span>
            </a>

            <?php if(auth()->user()->hasRole('master') || auth()->user()->hasRole('manager')): ?>
            <a href="<?php echo e(route('reports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>" id="nav-reports">
                <i class="ri-bar-chart-box-fill"></i>
                <span>Laporan</span>
            </a>
            <?php endif; ?>

            <?php if(auth()->user()->hasRole('master')): ?>
            <a href="<?php echo e(route('settings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>" id="nav-settings">
                <i class="ri-settings-4-fill"></i>
                <span>Pengaturan</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="ri-user-3-fill"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo e(auth()->user()->name); ?></span>
                    <span class="user-role"><?php echo e(ucfirst(auth()->user()->role)); ?></span>
                </div>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-logout" id="btn-logout" title="Keluar">
                    <i class="ri-logout-box-r-fill"></i>
                </button>
            </form>
        </div>
    </aside>

    
    
    
    <main class="main-content">
        
        <header class="topbar">
            <button class="btn-toggle-sidebar" id="btn-toggle-sidebar" onclick="document.body.classList.toggle('sidebar-collapsed')">
                <i class="ri-menu-2-fill"></i>
            </button>

            <h1 class="page-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>

            <div class="topbar-actions">
                
                <button class="btn-icon" id="btn-toggle-font" onclick="toggleLargeFont()" title="Toggle Font Besar">
                    <i class="ri-font-size-2"></i>
                </button>
            </div>
        </header>

        
        <?php if(session('success')): ?>
        <div class="alert alert-success" id="alert-success">
            <i class="ri-checkbox-circle-fill"></i>
            <span><?php echo e(session('success')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()"><i class="ri-close-fill"></i></button>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="alert alert-error" id="alert-error">
            <i class="ri-error-warning-fill"></i>
            <span><?php echo e(session('error')); ?></span>
            <button class="alert-close" onclick="this.parentElement.remove()"><i class="ri-close-fill"></i></button>
        </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
        <div class="alert alert-error" id="alert-validation">
            <i class="ri-error-warning-fill"></i>
            <div>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()"><i class="ri-close-fill"></i></button>
        </div>
        <?php endif; ?>

        
        <div class="content-wrapper">
            <?php echo $__env->yieldContent('content'); ?>
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
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\repo-proyekpemrog\CargoMind\resources\views/layouts/app.blade.php ENDPATH**/ ?>