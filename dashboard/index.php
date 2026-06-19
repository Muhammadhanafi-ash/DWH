<?php
/**
 * Enterprise DWH Dashboard - Main Dashboard UI
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/dwh_helper.php';

// Check database connection status
try {
    $db = Database::getConnection();
} catch (PDOException $e) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Error - Enterprise DWH</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="/DWH/assets/css/style.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }
            .error-card {
                width: 100%;
                max-width: 600px;
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: var(--radius-lg);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
                padding: 2.5rem;
                color: #ffffff;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h4 class="mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Koneksi PostgreSQL Gagal</h4>
            <p class="text-muted small mb-4">Aplikasi tidak dapat tersambung ke database PostgreSQL. Pesan error dari sistem:</p>
            
            <div class="alert alert-danger p-3 mb-4 font-monospace" style="border-radius: var(--radius-sm); font-size: 0.85rem; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: #f87171;">
                <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>

            <?php if (strpos($e->getMessage(), 'could not find driver') !== false): ?>
                <h6 class="fw-bold mb-2 text-warning"><i class="fa-solid fa-lightbulb me-1"></i>Cara Mengatasi (Driver PDO PgSQL belum aktif):</h6>
                <ol class="small ps-3 mb-0" style="color: #cbd5e1; line-height: 1.6;">
                    <li class="mb-2">Buka <strong>XAMPP Control Panel</strong> Anda.</li>
                    <li class="mb-2">Klik tombol <strong>Config</strong> di baris <strong>Apache</strong>, lalu pilih <strong>PHP (php.ini)</strong>.</li>
                    <li class="mb-2">Cari baris berikut (gunakan Ctrl+F): <br><code>;extension=pdo_pgsql</code> dan <code>;extension=pgsql</code></li>
                    <li class="mb-2">Hapus tanda titik koma (<code>;</code>) di awal kedua baris tersebut sehingga menjadi:<br><code>extension=pdo_pgsql</code><br><code>extension=pgsql</code></li>
                    <li class="mb-2">Simpan (Save) file tersebut.</li>
                    <li class="mb-2">Kembali ke XAMPP Control Panel, <strong>Stop</strong> Apache lalu <strong>Start</strong> kembali.</li>
                    <li>Muat ulang (refresh) halaman ini.</li>
                </ol>
            <?php else: ?>
                <h6 class="fw-bold mb-2 text-warning"><i class="fa-solid fa-lightbulb me-1"></i>Saran Pemeriksaan:</h6>
                <ul class="small ps-3 mb-0" style="color: #cbd5e1; line-height: 1.6;">
                    <li class="mb-2">Pastikan server PostgreSQL Anda sudah dalam status **Running** (aktif).</li>
                    <li class="mb-2">Pastikan kredensial koneksi di file <code>config/database.php</code> sudah sesuai (host, port, username, password, dan dbname).</li>
                </ul>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Load normal headers
require_once __DIR__ . '/../includes/header.php';

// Get available DWH Tables list
$dwhData = DWHHelper::getDWHTables();
$factTables = $dwhData['fact'];
$activeFactTable = $_GET['fact_table'] ?? 'fact_sales';

// Extract unique values for filter selections
$db = Database::getConnection();
$filterYears = [2005, 2006];
$filterMonths = [];
$filterCategories = [];
$filterRegions = [];

try {
    // Fetch unique years and months from dim_date
    $dateInfo = $db->query("SELECT DISTINCT year FROM public.dim_date ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($dateInfo)) $filterYears = $dateInfo;
    
    $monthInfo = $db->query("SELECT DISTINCT month, month_name FROM public.dim_date ORDER BY month ASC")->fetchAll();
    if (!empty($monthInfo)) $filterMonths = $monthInfo;

    // Fetch categories from dim_film
    $catInfo = $db->query("SELECT DISTINCT film_category FROM public.dim_film WHERE film_category IS NOT NULL ORDER BY film_category")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($catInfo)) $filterCategories = $catInfo;

    // Fetch countries from dim_customer
    $regInfo = $db->query("SELECT DISTINCT customer_country FROM public.dim_customer WHERE customer_country IS NOT NULL ORDER BY customer_country")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($regInfo)) $filterRegions = $regInfo;
} catch (Exception $e) {}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div class="page-header mb-0">
        <h1 class="page-title">Dashboard Utama</h1>
        <p class="page-subtitle">Visualisasi Enterprise Data Warehouse & Business Intelligence</p>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary px-3 py-2 fs-7" style="border-radius: var(--radius-sm);">
            <i class="fa-solid fa-database me-1"></i> Active DB: <?php echo htmlspecialchars(Database::getConfig()['dbname']); ?>
        </span>
        <button class="btn btn-outline-secondary btn-sm" id="btn-refresh" style="border-radius: var(--radius-sm);" title="Refresh Data">
            <i class="fa-solid fa-rotate"></i>
        </button>
    </div>
</div>

<!-- Filters Panel -->
<div class="filter-section">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-sliders text-primary me-2"></i>Filter Interaktif</h6>
        <button class="btn btn-link btn-sm p-0 text-decoration-none" id="clear-filters">Reset Filter</button>
    </div>
    
    <div class="row g-3">
        <!-- Active Fact Table -->
        <div class="col-md-3 col-sm-6">
            <label class="filter-label" for="filter-fact">Active Fact Table</label>
            <select class="filter-input form-select" id="filter-fact">
                <?php foreach ($factTables as $ft): ?>
                    <option value="<?php echo htmlspecialchars($ft['name']); ?>" <?php echo $activeFactTable === $ft['name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ft['name']); ?> (<?php echo number_format($ft['rows']); ?> rows)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Year -->
        <div class="col-md-2 col-sm-6">
            <label class="filter-label" for="filter-year">Tahun</label>
            <select class="filter-input form-select" id="filter-year">
                <option value="">Semua Tahun</option>
                <?php foreach ($filterYears as $year): ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Month -->
        <div class="col-md-2 col-sm-6">
            <label class="filter-label" for="filter-month">Bulan</label>
            <select class="filter-input form-select" id="filter-month">
                <option value="">Semua Bulan</option>
                <?php foreach ($filterMonths as $m): ?>
                    <option value="<?php echo $m['month']; ?>"><?php echo htmlspecialchars($m['month_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Category -->
        <div class="col-md-2 col-sm-6">
            <label class="filter-label" for="filter-category">Kategori Film</label>
            <select class="filter-input form-select" id="filter-category">
                <option value="">Semua Kategori</option>
                <?php foreach ($filterCategories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Region -->
        <div class="col-md-3 col-sm-6">
            <label class="filter-label" for="filter-region">Wilayah / Negara</label>
            <select class="filter-input form-select" id="filter-region">
                <option value="">Semua Wilayah</option>
                <?php foreach ($filterRegions as $reg): ?>
                    <option value="<?php echo htmlspecialchars($reg); ?>"><?php echo htmlspecialchars($reg); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="row g-2 mt-2">
        <div class="col-md-6">
            <label class="filter-label">Rentang Tanggal</label>
            <div class="d-flex align-items-center gap-2">
                <input type="date" class="filter-input" id="filter-start-date" placeholder="Tanggal Mulai">
                <span class="text-muted">s/d</span>
                <input type="date" class="filter-input" id="filter-end-date" placeholder="Tanggal Akhir">
            </div>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <!-- Card 1: Total Data -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--primary); --kpi-light-color: var(--primary-light);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Total Data</div>
                    <div class="kpi-value" id="kpi-total-data"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-database"></i>
                </div>
            </div>
            <div class="kpi-trend text-primary">All schemas rows</div>
        </div>
    </div>
    
    <!-- Card 2: Total Fact Records -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--warning); --kpi-light-color: rgba(245, 158, 11, 0.15);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Total Fact Records</div>
                    <div class="kpi-value" id="kpi-total-fact"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
            </div>
            <div class="kpi-trend text-warning">Fact tables total</div>
        </div>
    </div>
    
    <!-- Card 3: Total Dimension Records -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--info); --kpi-light-color: rgba(6, 182, 212, 0.15);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Total Dimension Records</div>
                    <div class="kpi-value" id="kpi-total-dim"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-shapes"></i>
                </div>
            </div>
            <div class="kpi-trend text-info">Dimensions total</div>
        </div>
    </div>
    
    <!-- Card 4: Total Transactions (Filtered metric count) -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--success); --kpi-light-color: var(--success-light);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label" id="kpi-label-trans">Total Transaksi</div>
                    <div class="kpi-value" id="kpi-total-trans"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="kpi-trend text-success"><i class="fa-solid fa-filter me-1"></i>Filtered rows</div>
        </div>
    </div>
    
    <!-- Card 5: Last Update -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: #a855f7; --kpi-light-color: rgba(168, 85, 247, 0.15);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Last Update</div>
                    <div class="kpi-value" id="kpi-last-update" style="font-size: 1.15rem; margin-top: 1.3rem;"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="kpi-trend text-purple">DWH Last Refresh</div>
        </div>
    </div>
    
    <!-- Card 6: Total Query Process -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--danger); --kpi-light-color: rgba(239, 68, 68, 0.15);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Query Process</div>
                    <div class="kpi-value" id="kpi-query-process"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-server"></i>
                </div>
            </div>
            <div class="kpi-trend text-danger">Transactions count</div>
        </div>
    </div>
</div>

<!-- Charts Panel Grid -->
<div class="row g-4 mb-4">
    <!-- Bar Chart: Category Comparison -->
    <div class="col-lg-6">
        <div class="custom-card position-relative" id="card-comparison-chart">
            <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="card-title mb-3 fw-bold"><i class="fa-solid fa-chart-simple text-primary me-2"></i>Perbandingan Kategori</h5>
            <div id="category-comparison-chart" style="height: 320px;"></div>
        </div>
    </div>
    
    <!-- Line Chart: Monthly Trend -->
    <div class="col-lg-6">
        <div class="custom-card position-relative" id="card-trend-chart">
            <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="card-title mb-3 fw-bold"><i class="fa-solid fa-chart-line text-success me-2"></i>Tren Bulanan</h5>
            <div id="monthly-trend-chart" style="height: 320px;"></div>
        </div>
    </div>
    
    <!-- Area Chart: Cumulative Growth or Composition -->
    <div class="col-lg-8">
        <div class="custom-card position-relative" id="card-region-chart">
            <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="card-title mb-3 fw-bold"><i class="fa-solid fa-chart-area text-info me-2"></i>Perkembangan Wilayah / Store</h5>
            <div id="region-distribution-chart" style="height: 320px;"></div>
        </div>
    </div>
    
    <!-- Pie/Doughnut Chart: Rating Distribution -->
    <div class="col-lg-4">
        <div class="custom-card position-relative" id="card-rating-chart">
            <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="card-title mb-3 fw-bold"><i class="fa-solid fa-chart-pie text-warning me-2"></i>Komposisi Rating</h5>
            <div id="rating-distribution-chart" style="height: 320px;"></div>
        </div>
    </div>
</div>

<!-- Data Table Panel -->
<div class="custom-card position-relative mb-4" id="card-data-table">
    <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h5 class="card-title mb-0 fw-bold"><i class="fa-solid fa-table-list text-primary me-2"></i>Dataset Detail Fact Table</h5>
        <div class="dt-buttons-container"></div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="dwh-data-table" style="width: 100%;">
            <thead>
                <tr id="table-headers">
                    <!-- Loaded dynamically via JS -->
                </tr>
            </thead>
            <tbody>
                <!-- Loaded via AJAX Server Side -->
            </tbody>
        </table>
    </div>
</div>

<!-- Load dashboard Controller script at the end of body -->
<script src="/DWH/assets/js/dashboard.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
