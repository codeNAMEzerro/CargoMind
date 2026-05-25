<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CargoMind</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body style="display:block;">
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo"><i class="ri-box-3-fill"></i></div>
            <h1 class="login-title">CargoMind</h1>
            <p class="login-subtitle">Sistem Manajemen Toko & Gudang</p>

            <?php if($errors->any()): ?>
            <div class="alert alert-error mb-2" style="text-align:left;">
                <i class="ri-error-warning-fill"></i>
                <span><?php echo e($errors->first()); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" id="login-form" style="text-align:left;">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo e(old('email')); ?>" required autofocus placeholder="contoh@cargomind.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="remember" id="remember" style="width:auto;">
                    <label for="remember" style="margin:0;font-weight:500;color:var(--text-secondary);">Ingat Saya</label>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-login">
                    <i class="ri-login-box-fill"></i> Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Users/ILHAM-sama/Documents/CargoMind/CargoMind/resources/views/auth/login.blade.php ENDPATH**/ ?>