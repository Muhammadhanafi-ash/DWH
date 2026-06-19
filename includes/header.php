<?php
/**
 * Enterprise DWH Dashboard - Common Header Layout
 */

require_once __DIR__ . '/auth.php';
checkAuth();

// Active page helper
$currentUri = $_SERVER['REQUEST_URI'];
function isPageActive($path) {
    global $currentUri;
    $cleanUri = parse_url($currentUri, PHP_URL_PATH);
    
    if ($path === '/dashboard/') {
        return (strpos($cleanUri, '/dashboard/') !== false) ? 'active' : '';
    }
    return (strpos($cleanUri, $path) !== false) ? 'active' : '';
}

// Get user info from session
$userName = $_SESSION['user_name'] ?? 'Guest';
$userRole = $_SESSION['user_role'] ?? 'BI Architect';
$userInitials = '';
$words = explode(' ', $userName);
foreach ($words as $w) {
    if (!empty($w)) $userInitials .= strtoupper($w[0]);
}
$userInitials = substr($userInitials, 0, 2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Data Warehouse Portal</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- jQuery (Needed for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <!-- Dark Mode Initializer -->
    <script src="/assets/js/darkmode.js"></script>
</head>
<body>

    <div id="app-container">
        
        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="sidebar-header">
                <a href="/dashboard/index.php" class="sidebar-logo">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>DWH PORTAL</span>
                </a>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-item <?php echo isPageActive('/dashboard/'); ?>">
                    <a href="/dashboard/index.php" class="menu-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Dashboard Utama</span>
                    </a>
                </li>
                

                <li class="menu-item <?php echo isPageActive('/visualization/'); ?>">
                    <a href="/visualization/index.php" class="menu-link">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Visualisasi Data</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo isPageActive('/analytics/'); ?>">
                    <a href="/analytics/index.php" class="menu-link">
                        <i class="fa-solid fa-brain"></i>
                        <span>Analytics & Insights</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo isPageActive('/reports/'); ?>">
                    <a href="/reports/index.php" class="menu-link">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Export & Reports</span>
                    </a>
                </li>
                
                <li class="menu-item" style="border-top: 1px solid rgba(255,255,255,0.08); margin-top: 1rem; padding-top: 1rem;">
                    <a href="/logout.php" class="menu-link" style="color: rgba(255,255,255,0.6);">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar (Logout)</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                v1.8.3 | Enterprise BI
            </div>
        </aside>
        
        <!-- Main Content View -->
        <main id="main-content">
            
            <!-- Navbar Top -->
            <nav id="top-navbar">
                <!-- Sidebar Mobile Toggle -->
                <button class="nav-icon-btn d-lg-none" id="mobile-sidebar-toggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                
                <!-- Search -->
                <div class="search-container d-none d-md-block">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Cari data warehouse...">
                </div>
                
                <!-- Right Actions -->
                <div class="nav-actions">
                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="nav-icon-btn dropdown-toggle" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-bell"></i>
                            <span class="badge-dot"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-2 border-0 shadow-lg" aria-labelledby="notifDropdown" style="width: 280px; border-radius: var(--radius-md);">
                            <li class="dropdown-header text-dark font-weight-bold" style="font-size: 0.85rem;">Notifikasi Baru</li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item p-2" href="#" style="border-radius: var(--radius-sm); font-size: 0.8rem;">
                                    <div class="font-weight-bold text-truncate" style="color: var(--primary);">Database Terkoneksi</div>
                                    <small class="text-muted">Backup DWH sakiladb.sql terdeteksi.</small>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item p-2" href="#" style="border-radius: var(--radius-sm); font-size: 0.8rem;">
                                    <div class="font-weight-bold text-truncate text-success">Star Schema Berhasil Dimuat</div>
                                    <small class="text-muted">Dynamic modeler menganalisis relations.</small>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Dark Mode -->
                    <button class="nav-icon-btn" id="dark-mode-toggle" title="Aktifkan Mode Gelap">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="dropdown">
                        <div class="user-profile-dropdown dropdown-toggle" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar"><?php echo $userInitials; ?></div>
                            <div class="d-none d-sm-block">
                                <div style="font-size: 0.85rem; font-weight: 700; line-height: 1.2;"><?php echo htmlspecialchars($userName); ?></div>
                                <div style="font-size: 0.7rem; color: var(--text-secondary);"><?php echo htmlspecialchars($userRole); ?></div>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" aria-labelledby="userMenuBtn" style="border-radius: var(--radius-md);">
                            <li><a class="dropdown-item py-2 px-3" href="/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <!-- Main Content Container -->
            <div class="page-container">
