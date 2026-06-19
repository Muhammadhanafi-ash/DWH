<?php
/**
 * Enterprise DWH Dashboard - Analytics & Insights Page
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/dwh_helper.php';

$dwhData = DWHHelper::getDWHTables();
$factTables = $dwhData['fact'];
$activeFactTable = $_GET['fact_table'] ?? (!empty($factTables) ? $factTables[0]['name'] : 'fact_sales');

// Extract unique selections for filters
$db = Database::getConnection();
$filterYears = [2005, 2006];
$filterMonths = [];
$filterCategories = [];
$filterRegions = [];

try {
    $dateInfo = $db->query("SELECT DISTINCT year FROM public.dim_date ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($dateInfo)) $filterYears = $dateInfo;
    
    $monthInfo = $db->query("SELECT DISTINCT month, month_name FROM public.dim_date ORDER BY month ASC")->fetchAll();
    if (!empty($monthInfo)) $filterMonths = $monthInfo;

    $catInfo = $db->query("SELECT DISTINCT film_category FROM public.dim_film WHERE film_category IS NOT NULL ORDER BY film_category")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($catInfo)) $filterCategories = $catInfo;

    $regInfo = $db->query("SELECT DISTINCT customer_country FROM public.dim_customer WHERE customer_country IS NOT NULL ORDER BY customer_country")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($regInfo)) $filterRegions = $regInfo;
} catch (Exception $e) {}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div class="page-header mb-0">
        <h1 class="page-title">Analytics & Insights</h1>
        <p class="page-subtitle">Statistik deskriptif dan intelijen bisnis otomatis</p>
    </div>
    
    <div>
        <button class="btn btn-outline-secondary btn-sm" id="btn-refresh-analytics" style="border-radius: var(--radius-sm);">
            <i class="fa-solid fa-rotate"></i> Refresh Analitik
        </button>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="row g-3">
        <!-- Active Fact Table -->
        <div class="col-md-3 col-sm-6">
            <label class="filter-label" for="filter-fact">Active Fact Table</label>
            <select class="filter-input form-select" id="filter-fact">
                <?php foreach ($factTables as $ft): ?>
                    <option value="<?php echo htmlspecialchars($ft['name']); ?>" <?php echo $activeFactTable === $ft['name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ft['name']); ?>
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
        
        <div class="col-md-2 col-sm-6 d-flex align-items-end">
            <button class="btn btn-primary w-100 btn-sm py-2" id="clear-analytics-filters" style="border-radius: var(--radius-sm);">Reset Filter</button>
        </div>
    </div>
</div>

<!-- Summary Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Card 1: Nilai Maksimum -->
    <div class="col-md-3 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--danger); --kpi-light-color: rgba(239, 68, 68, 0.15);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Nilai Maksimum</div>
                    <div class="kpi-value" id="stat-max"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="kpi-trend text-danger">Nilai record tertinggi</div>
        </div>
    </div>
    
    <!-- Card 2: Nilai Minimum -->
    <div class="col-md-3 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--info); --kpi-light-color: rgba(6, 182, 212, 0.15);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Nilai Minimum</div>
                    <div class="kpi-value" id="stat-min"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </div>
            </div>
            <div class="kpi-trend text-info">Nilai record terendah</div>
        </div>
    </div>
    
    <!-- Card 3: Rata-Rata -->
    <div class="col-md-3 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--warning); --kpi-light-color: rgba(245, 158, 11, 0.15);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Rata-Rata (Mean)</div>
                    <div class="kpi-value" id="stat-avg"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-calculator"></i>
                </div>
            </div>
            <div class="kpi-trend text-warning">Rerata seluruh transaksi</div>
        </div>
    </div>
    
    <!-- Card 4: Total Data Baris -->
    <div class="col-md-3 col-sm-6">
        <div class="custom-card kpi-card" style="--kpi-color: var(--success); --kpi-light-color: var(--success-light);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Total Data Baris</div>
                    <div class="kpi-value" id="stat-count"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div>
                </div>
                <div class="kpi-icon-wrapper">
                    <i class="fa-solid fa-arrow-down-9-1"></i>
                </div>
            </div>
            <div class="kpi-trend text-success">Jumlah baris terfilter</div>
        </div>
    </div>
</div>

<!-- Business Insight Panel -->
<div class="custom-card mb-4 position-relative" id="card-insights-panel" style="min-height: 120px;">
    <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
    <h5 class="fw-bold mb-3"><i class="fa-solid fa-lightbulb text-warning me-2"></i>Business Insights Otomatis</h5>
    <div class="row">
        <div class="col-12">
            <ul class="list-group list-group-flush" id="insights-list" style="background: transparent;">
                <!-- Loaded via AJAX -->
            </ul>
        </div>
    </div>
</div>

<!-- Analytics Charts Grid -->
<div class="row g-4 mb-4">
    <!-- Top 10 Data (Bar Chart) -->
    <div class="col-lg-6">
        <div class="custom-card position-relative" id="card-top10-chart">
            <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="card-title mb-3 fw-bold"><i class="fa-solid fa-trophy text-warning me-2"></i>Top 10 Performa Item Terbesar</h5>
            <div id="analytics-top10-chart" style="height: 350px;"></div>
        </div>
    </div>
    
    <!-- Trend Analysis (Line Chart) -->
    <div class="col-lg-6">
        <div class="custom-card position-relative" id="card-trend-analysis-chart">
            <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="card-title mb-3 fw-bold"><i class="fa-solid fa-chart-line text-primary me-2"></i>Analisis Tren Berjalan</h5>
            <div id="analytics-trend-chart" style="height: 350px;"></div>
        </div>
    </div>

    <!-- Distribution Analysis (Pie Chart) -->
    <div class="col-lg-12">
        <div class="custom-card position-relative" id="card-distribution-chart">
            <div class="loading-overlay active"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="card-title mb-3 fw-bold"><i class="fa-solid fa-chart-pie text-success me-2"></i>Analisis Komposisi Distribusi</h5>
            <div id="analytics-distribution-chart" style="height: 300px;"></div>
        </div>
    </div>
</div>

<!-- Page script to handle AJAX reloads for Analytics page -->
<script>
    $(document).ready(function() {
        let topChart = null;
        let trendChart = null;
        let distChart = null;

        function getFilterParams() {
            return {
                fact_table: $('#filter-fact').val(),
                year: $('#filter-year').val(),
                category: $('#filter-category').val(),
                region: $('#filter-region').val()
            };
        }

        function initCharts() {
            const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';
            const gridColor = theme === 'dark' ? '#1f2937' : '#e2e8f0';

            // Top 10 Bar Chart
            topChart = new ApexCharts(document.querySelector("#analytics-top10-chart"), {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                theme: { mode: theme },
                colors: ['#3b82f6'],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
                dataLabels: { enabled: true, style: { fontFamily: 'Outfit' } },
                series: [{ name: 'Total', data: [] }],
                xaxis: { categories: [], labels: { style: { colors: labelColor } } },
                yaxis: { labels: { style: { colors: labelColor } } },
                grid: { borderColor: gridColor }
            });
            topChart.render();

            // Trend Line Chart
            trendChart = new ApexCharts(document.querySelector("#analytics-trend-chart"), {
                chart: { type: 'area', height: 350, toolbar: { show: false } },
                theme: { mode: theme },
                colors: ['#ec4899'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
                dataLabels: { enabled: false },
                series: [{ name: 'Aktivitas', data: [] }],
                xaxis: { categories: [], labels: { style: { colors: labelColor } } },
                yaxis: { labels: { style: { colors: labelColor } } },
                grid: { borderColor: gridColor }
            });
            trendChart.render();

            // Distribution Doughnut Chart
            distChart = new ApexCharts(document.querySelector("#analytics-distribution-chart"), {
                chart: { type: 'donut', height: 300 },
                theme: { mode: theme },
                colors: ['#1e3a8a', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899'],
                series: [],
                labels: [],
                legend: { position: 'right', labels: { colors: labelColor } }
            });
            distChart.render();
        }

        // Apply theme configurations
        window.addEventListener('theme-changed', (e) => {
            const theme = e.detail.theme;
            const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';
            const gridColor = theme === 'dark' ? '#1f2937' : '#e2e8f0';

            const opts = {
                theme: { mode: theme },
                xaxis: { labels: { style: { colors: labelColor } } },
                yaxis: { labels: { style: { colors: labelColor } } },
                grid: { borderColor: gridColor }
            };
            topChart.updateOptions(opts);
            trendChart.updateOptions(opts);
            distChart.updateOptions({
                theme: { mode: theme },
                legend: { labels: { colors: labelColor } }
            });
        });

        function loadAnalytics() {
            $('.loading-overlay').addClass('active');
            const params = getFilterParams();

            // 1. Fetch Insights & Stats
            $.getJSON('/api/insights.php', params, function(data) {
                // Update stats UI
                const isSales = params.fact_table === 'fact_sales' || params.fact_table === 'fact_store_performance';
                
                const formatVal = val => {
                    const num = parseFloat(val);
                    if (isNaN(num)) return '-';
                    return isSales ? '$' + num.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : num.toLocaleString();
                };

                $('#stat-max').text(formatVal(data.stats.max));
                $('#stat-min').text(formatVal(data.stats.min));
                $('#stat-avg').text(formatVal(data.stats.avg));
                $('#stat-count').text(parseInt(data.stats.count).toLocaleString());

                // Update insights list
                const list = $('#insights-list');
                list.empty();
                data.insights.forEach(insight => {
                    list.append(`<li class="list-group-item px-0 border-0 bg-transparent text-primary d-flex align-items-start gap-2 py-2" style="font-size:0.95rem;">
                        <i class="fa-solid fa-circle-chevron-right text-success mt-1" style="font-size:0.85rem;"></i>
                        <div>${insight}</div>
                    </li>`);
                });
                $('#card-insights-panel').find('.loading-overlay').removeClass('active');
            });

            // 2. Fetch Chart datasets
            $.getJSON('/api/charts.php', params, function(data) {
                // 1) Top 10 item chart (Top film or Category)
                if (data.top_films && data.top_films.length > 0) {
                    const top10 = data.top_films.slice(0, 10);
                    const titles = top10.map(i => i.title);
                    const values = top10.map(i => parseFloat(i.val));
                    topChart.updateOptions({ xaxis: { categories: titles } });
                    topChart.updateSeries([{ data: values }]);
                } else if (data.category_comparison && data.category_comparison.length > 0) {
                    const top10 = data.category_comparison.slice(0, 10);
                    const cats = top10.map(i => i.category);
                    const values = top10.map(i => parseFloat(i.val));
                    topChart.updateOptions({ xaxis: { categories: cats } });
                    topChart.updateSeries([{ data: values }]);
                }
                $('#card-top10-chart').find('.loading-overlay').removeClass('active');

                // 2) Trend Analysis Chart
                if (data.monthly_trend && data.monthly_trend.length > 0) {
                    const periods = data.monthly_trend.map(i => i.period + ' ' + i.year);
                    const values = data.monthly_trend.map(i => parseFloat(i.val));
                    trendChart.updateOptions({ xaxis: { categories: periods } });
                    trendChart.updateSeries([{ data: values }]);
                }
                $('#card-trend-analysis-chart').find('.loading-overlay').removeClass('active');

                // 3) Distribution Chart (Rating or Region)
                if (data.rating_distribution && data.rating_distribution.length > 0) {
                    const ratings = data.rating_distribution.map(i => i.rating);
                    const values = data.rating_distribution.map(i => parseFloat(i.val));
                    distChart.updateOptions({ labels: ratings });
                    distChart.updateSeries(values);
                } else if (data.region_distribution && data.region_distribution.length > 0) {
                    const top10Regions = data.region_distribution.slice(0, 10);
                    const regions = top10Regions.map(i => i.region);
                    const values = top10Regions.map(i => parseFloat(i.val));
                    distChart.updateOptions({ labels: regions });
                    distChart.updateSeries(values);
                }
                $('#card-distribution-chart').find('.loading-overlay').removeClass('active');
            });
        }

        // Initialize and bind
        initCharts();
        loadAnalytics();

        $('.filter-input').change(function() {
            loadAnalytics();
        });

        $('#btn-refresh-analytics').click(function() {
            loadAnalytics();
        });

        $('#clear-analytics-filters').click(function() {
            $('#filter-year').val('');
            $('#filter-category').val('');
            $('#filter-region').val('');
            loadAnalytics();
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
