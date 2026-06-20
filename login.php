<?php
/**
 * Enterprise DWH Dashboard - Login Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: /dashboard/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Hardcoded credentials for enterprise security simulation
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name'] = 'Administrator';
        $_SESSION['user_email'] = 'admin@enterprise.dwh';
        $_SESSION['user_role'] = 'BI Architect';
        header("Location: /dashboard/index.php");
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enterprise DWH Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-logo">
            <i class="fa-solid fa-chart-line"></i>
            <span>DWH Portal</span>
        </div>
        
        <h5 class="text-center text-muted mb-4" style="font-size: 0.95rem; font-weight: 500;">
            Business Intelligence & Analytics
        </h5>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert" style="border-radius: var(--radius-sm); font-size: 0.85rem;">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="login-form">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); border-right: none; color: #cbd5e1;">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required style="border-left: none;">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); border-right: none; color: #cbd5e1;">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required style="border-left: none;">
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                Masuk Ke Portal <i class="fa-solid fa-right-to-bracket ms-2"></i>
            </button>
        </form>

        <div class="text-center mt-4" style="font-size: 0.75rem; color: #94a3b8;">
            Username: <code>admin</code> | Password: <code>admin123</code>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
