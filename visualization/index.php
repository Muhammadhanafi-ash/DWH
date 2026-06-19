<?php
/**
 * Enterprise DWH Dashboard - Visual Exploration Canvas
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/dwh_helper.php';

$dwhData = DWHHelper::getDWHTables();
$factTables = $dwhData['fact'];
$activeFactTable = $_GET['fact_table'] ?? (!empty($factTables) ? $factTables[0]['name'] : 'fact_sales');
?>

<!-- Page Header -->
<div class="page-header mb-4">
    <h1 class="page-title">Visualisasi Data</h1>
    <p class="page-subtitle">Pusat eksplorasi grafik interaktif berbasis data warehouse</p>
</div>

<!-- Explorer Control Panel -->
<div class="row g-4 mb-4" id="viz-main-row">
    <div class="col-lg-3" id="col-filter-panel">
        <div class="custom-card">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-screwdriver-wrench text-primary me-2"></i>Konfigurasi Grafik</h5>
            
            <div class="mb-3">
                <label class="filter-label" for="vis-fact-table">1. Pilih Fact Table</label>
                <select class="filter-input form-select" id="vis-fact-table">
                    <?php foreach ($factTables as $ft): ?>
                        <option value="<?php echo htmlspecialchars($ft['name']); ?>" <?php echo $activeFactTable === $ft['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ft['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="filter-label" for="vis-group-by">2. Dimensi Pengelompokan (X-Axis)</label>
                <select class="filter-input form-select" id="vis-group-by">
                    <!-- Populated dynamically via JS lookup based on fact table -->
                </select>
            </div>
            
            <div class="mb-3">
                <label class="filter-label" for="vis-aggregation">3. Metrik Agregasi (Y-Axis)</label>
                <select class="filter-input form-select" id="vis-aggregation">
                    <!-- Populated dynamically via JS lookup -->
                </select>
            </div>
            
            <div class="mb-4">
                <label class="filter-label" for="vis-chart-type">4. Jenis Visualisasi</label>
                <select class="filter-input form-select" id="vis-chart-type">
                    <option value="bar"><i class="fa-solid fa-chart-bar"></i> Bar Chart (Perbandingan)</option>
                    <option value="line"><i class="fa-solid fa-chart-line"></i> Line Chart (Tren Waktu)</option>
                    <option value="area"><i class="fa-solid fa-chart-area"></i> Area Chart (Perkembangan)</option>
                    <option value="pie"><i class="fa-solid fa-chart-pie"></i> Pie Chart (Persentase)</option>
                    <option value="donut"><i class="fa-solid fa-chart-simple"></i> Doughnut Chart (Komposisi)</option>
                    <option value="horizontal-bar"><i class="fa-solid fa-align-left"></i> Horizontal Bar (Top 10)</option>
                </select>
            </div>
            
            <button class="btn btn-primary w-100 py-2 fw-bold" id="btn-generate-chart" style="border-radius: var(--radius-sm);">
                Gambarkan Grafik <i class="fa-solid fa-chart-line-up ms-1"></i>
            </button>

            <!-- Data Limit Selector -->
            <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.07);">
                <label class="filter-label mb-2 d-block">5. Jumlah Data Ditampilkan</label>
                <div class="d-flex flex-wrap gap-1" id="limit-btn-group" role="group">
                    <button type="button" class="btn limit-btn" data-limit="5">5</button>
                    <button type="button" class="btn limit-btn" data-limit="25">25</button>
                    <button type="button" class="btn limit-btn" data-limit="50">50</button>
                    <button type="button" class="btn limit-btn" data-limit="100">100</button>
                    <button type="button" class="btn limit-btn" data-limit="200">200</button>
                    <button type="button" class="btn limit-btn limit-btn-active" data-limit="all">Semua</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Visual Canvas Display -->
    <div class="col-lg-9" id="col-chart-canvas">
        <div class="custom-card position-relative" id="card-visualization-canvas" style="min-height: 420px;">
            <div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>
            <h5 class="fw-bold mb-3" id="canvas-chart-title">Visualisasi Output</h5>
            
            <div class="d-flex align-items-center justify-content-center" id="canvas-empty-state" style="height: 360px;">
                <div class="text-center text-muted">
                    <i class="fa-solid fa-chart-area mb-3 text-secondary" style="font-size: 4rem; opacity: 0.4;"></i>
                    <p class="mb-0">Silakan tentukan konfigurasi data warehouse dan klik <strong>Gambarkan Grafik</strong>.</p>
                </div>
            </div>

            <!-- No data state -->
            <div class="d-none d-flex align-items-center justify-content-center" id="canvas-nodata-state" style="height: 360px;">
                <div class="text-center text-muted">
                    <i class="fa-solid fa-database mb-3 text-secondary" style="font-size: 4rem; opacity: 0.4;"></i>
                    <p class="mb-0">Tidak ada data ditemukan untuk konfigurasi yang dipilih.</p>
                </div>
            </div>
            
            <!-- Scroll hint badge -->
            <div class="d-none mb-2" id="canvas-scroll-hint">
                <span class="badge" style="background: rgba(99,102,241,0.15); color: #818cf8; font-size: 0.72rem; border-radius: 20px; padding: 4px 10px;">
                    <i class="fa-solid fa-arrows-left-right me-1"></i> Grafik dapat di-scroll ke kanan
                </span>
            </div>

            <div class="d-none" id="canvas-chart-wrapper" style="overflow-x: auto; overflow-y: auto; max-height: 82vh;">
                <div id="explorer-active-chart"></div>
            </div>
        </div>
    </div>
</div>

<!-- Visualization JS Controller -->
<script>
    $(document).ready(function() {
        // Schema mappings for generic dynamic queries
        const schemaMetadata = {
            fact_sales: {
                groupOptions: [
                    { value: 'category', text: 'Film Category (Kategori Film)' },
                    { value: 'rating', text: 'Film Rating (Klasifikasi Rating)' },
                    { value: 'title', text: 'Film Title (Judul Film)' },
                    { value: 'region', text: 'Customer Country (Negara Pelanggan)' },
                    { value: 'period', text: 'Transaction Month (Bulan Transaksi)' }
                ],
                aggregations: [
                    { value: 'sum_amount', text: 'Sum of Amount (Total Penjualan)' },
                    { value: 'count_sales', text: 'Count of Transactions (Jumlah Transaksi)' }
                ]
            },
            fact_rental: {
                groupOptions: [
                    { value: 'category', text: 'Film Category (Kategori Film)' },
                    { value: 'title', text: 'Film Title (Judul Film)' },
                    { value: 'region', text: 'Customer Country (Negara Pelanggan)' },
                    { value: 'period', text: 'Rental Month (Bulan Sewa)' }
                ],
                aggregations: [
                    { value: 'sum_rentals', text: 'Sum of Rentals (Total Sewa Film)' },
                    { value: 'avg_duration', text: 'Avg Rental Duration (Rerata Durasi Sewa)' }
                ]
            },
            fact_inventory: {
                groupOptions: [
                    { value: 'category', text: 'Film Category (Kategori Film)' },
                    { value: 'title', text: 'Film Title (Judul Film)' },
                    { value: 'period', text: 'Stock Log Date (Bulan Input)' }
                ],
                aggregations: [
                    { value: 'sum_stock', text: 'Sum of Stock (Total Jumlah Stok)' }
                ]
            },
            fact_actor_performance: {
                groupOptions: [
                    { value: 'actor', text: 'Actor Name (Nama Aktor)' },
                    { value: 'title', text: 'Film Title (Judul Film)' },
                    { value: 'period', text: 'Performance Month (Bulan)' }
                ],
                aggregations: [
                    { value: 'sum_rentals', text: 'Sum of Rentals (Total Kontribusi Rental)' }
                ]
            },
            fact_store_performance: {
                groupOptions: [
                    { value: 'region', text: 'Store Country (Negara Toko)' },
                    { value: 'staff', text: 'Staff Name (Nama Pegawai)' },
                    { value: 'period', text: 'Performance Month (Bulan)' }
                ],
                aggregations: [
                    { value: 'sum_sales', text: 'Sum of Total Sales (Total Penjualan Toko)' },
                    { value: 'sum_rentals', text: 'Sum of Total Rentals (Total Rental Toko)' }
                ]
            }
        };

        let activeChart   = null;
        let selectedLimit = 'all';   // default: show all
        let lastFullDataset = [];    // cache full API result to avoid re-fetching on limit change
        let lastTitleText   = '';
        let lastChartType   = 'bar';
        let lastYAxisLabel  = '';    // Y-axis title (aggregation metric)
        let lastXAxisLabel  = '';    // X-axis title (group-by dimension)

        // ── Limit button toggle ────────────────────────────────────────
        $('#limit-btn-group').on('click', '.limit-btn', function() {
            $('#limit-btn-group .limit-btn').removeClass('limit-btn-active');
            $(this).addClass('limit-btn-active');
            selectedLimit = $(this).data('limit').toString();

            // If a dataset is already loaded, immediately re-render
            if (lastFullDataset.length > 0) {
                renderChart(lastFullDataset, lastTitleText, lastChartType, lastYAxisLabel, lastXAxisLabel);
            }
        });

        // ── Core render function ───────────────────────────────────────
        function renderChart(fullDataset, titleText, chartType, yLabel, xLabel) {
            // Apply limit
            let rawDataset = (selectedLimit !== 'all')
                ? fullDataset.slice(0, parseInt(selectedLimit))
                : fullDataset;

            let categories = [];
            let values     = [];

            if (rawDataset.length > 0) {
                categories = rawDataset.map(i => i.category || i.period || i.region || i.rating || i.title || i.actor || i.staff || 'N/A');
                values     = rawDataset.map(i => parseFloat(i.val) || 0);
            }

            // No data
            if (categories.length === 0) {
                $('#canvas-chart-wrapper').addClass('d-none');
                $('#canvas-nodata-state').removeClass('d-none').addClass('d-flex');
                $('#card-visualization-canvas').find('.loading-overlay').removeClass('active');
                return;
            }

            $('#canvas-nodata-state').addClass('d-none').removeClass('d-flex');
            $('#canvas-chart-wrapper').removeClass('d-none');

            const limitLabel = (selectedLimit === 'all') ? rawDataset.length + ' data' : `Top ${categories.length} dari ${fullDataset.length}`;
            $('#canvas-chart-title').html(`<i class="fa-solid fa-chart-area text-primary me-2"></i>${titleText} <small class="text-muted ms-2" style="font-size:0.75rem;">(${limitLabel})</small>`);

            // Destroy previous chart
            if (activeChart) { activeChart.destroy(); activeChart = null; }

            const theme      = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';
            const gridColor  = theme === 'dark' ? '#334155' : '#e2e8f0';
            const n          = categories.length;

            // Height
            let dynamicHeight;
            if (chartType === 'horizontal-bar') {
                dynamicHeight = Math.max(480, n * 34);
            } else if (chartType === 'pie' || chartType === 'donut') {
                dynamicHeight = Math.max(480, Math.min(980, 480 + Math.max(0, n - 5) * 14));
            } else {
                dynamicHeight = Math.max(480, Math.min(1400, 480 + Math.max(0, n - 10) * 16));
            }

            // Width (horizontal scroll for dense vertical charts)
            const PX_PER_ITEM   = 40;
            const needsWideChart = (chartType === 'bar' || chartType === 'line' || chartType === 'area') && n > 20;
            const dynamicWidth   = needsWideChart ? Math.max(800, n * PX_PER_ITEM) : null;

            $('#explorer-active-chart').css('height', dynamicHeight + 'px');
            if (dynamicWidth) {
                $('#explorer-active-chart').css('min-width', dynamicWidth + 'px');
                $('#canvas-scroll-hint').removeClass('d-none');
            } else {
                $('#explorer-active-chart').css('min-width', '');
                $('#canvas-scroll-hint').addClass('d-none');
            }

            const rotateDeg = (chartType !== 'horizontal-bar' && n > 12) ? (n > 50 ? -60 : -45) : 0;
            const colWidth  = n > 80 ? '80%' : (n > 40 ? '75%' : (n > 15 ? '65%' : '50%'));

            let chartOptions = {
                chart: {
                    type: chartType === 'horizontal-bar' ? 'bar' : (chartType === 'donut' ? 'donut' : chartType),
                    height: dynamicHeight,
                    toolbar: { show: true, tools: { download: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true } },
                    background: 'transparent',
                    animations: { enabled: n <= 200 }
                },
                theme: { mode: theme },
                series: [],
                xaxis: {
                    categories: categories,
                    title: {
                        text: xLabel || '',
                        style: { color: labelColor, fontFamily: 'Outfit', fontSize: '12px', fontWeight: 600 }
                    },
                    labels: {
                        style: { colors: labelColor, fontFamily: 'Outfit', fontSize: n > 30 ? '10px' : '12px' },
                        rotate: rotateDeg,
                        trim: false,
                        maxHeight: rotateDeg !== 0 ? 90 : 60
                    },
                    tickPlacement: 'on'
                },
                yaxis: {
                    labels: { style: { colors: labelColor, fontFamily: 'Outfit' } },
                    title: {
                        text: yLabel || '',
                        style: { color: labelColor, fontFamily: 'Outfit', fontSize: '12px', fontWeight: 600 },
                        offsetX: -4
                    }
                },
                grid: { borderColor: gridColor },
                legend: { labels: { colors: labelColor }, position: 'bottom', fontSize: '12px' },
                dataLabels: { enabled: n <= 30 },
                tooltip: { theme: theme }
            };

            if (chartType === 'bar') {
                chartOptions.series = [{ name: 'Metrik', data: values }];
                chartOptions.plotOptions = { bar: { borderRadius: 4, columnWidth: colWidth } };
            } else if (chartType === 'horizontal-bar') {
                chartOptions.chart.type = 'bar';
                chartOptions.series = [{ name: 'Metrik', data: values }];
                chartOptions.plotOptions = { bar: { horizontal: true, borderRadius: 4, barHeight: n > 50 ? '60%' : '70%' } };
                chartOptions.dataLabels = { enabled: n <= 50 };
                chartOptions.xaxis = {
                    labels: { style: { colors: labelColor, fontFamily: 'Outfit' } },
                    title: {
                        text: yLabel || '',
                        style: { color: labelColor, fontFamily: 'Outfit', fontSize: '12px', fontWeight: 600 }
                    }
                };
                chartOptions.yaxis = {
                    categories: categories,
                    labels: { style: { colors: labelColor, fontFamily: 'Outfit', fontSize: n > 60 ? '9px' : '11px' }, maxWidth: 200 },
                    title: {
                        text: xLabel || '',
                        style: { color: labelColor, fontFamily: 'Outfit', fontSize: '12px', fontWeight: 600 }
                    }
                };
            } else if (chartType === 'line') {
                chartOptions.series = [{ name: 'Metrik', data: values }];
                chartOptions.stroke = { curve: 'smooth', width: 3 };
                chartOptions.markers = { size: n <= 50 ? 4 : 0 };
            } else if (chartType === 'area') {
                chartOptions.series = [{ name: 'Metrik', data: values }];
                chartOptions.stroke = { curve: 'smooth', width: 3 };
                chartOptions.fill = { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05 } };
                chartOptions.markers = { size: n <= 50 ? 4 : 0 };
            } else if (chartType === 'pie' || chartType === 'donut') {
                chartOptions.series = values;
                chartOptions.labels = categories;
                delete chartOptions.xaxis;
                delete chartOptions.yaxis;
                delete chartOptions.grid;
                chartOptions.legend = { labels: { colors: labelColor }, position: n > 10 ? 'bottom' : 'right', fontSize: n > 15 ? '10px' : '12px' };
                chartOptions.dataLabels = { enabled: n <= 10 };
            }

            activeChart = new ApexCharts(document.querySelector('#explorer-active-chart'), chartOptions);
            activeChart.render();
            $('#card-visualization-canvas').find('.loading-overlay').removeClass('active');
        }

        function populateSelectOptions(factTable) {
            const config = schemaMetadata[factTable] || schemaMetadata['fact_sales'];
            
            const groupSelect = $('#vis-group-by');
            groupSelect.empty();
            config.groupOptions.forEach(opt => {
                groupSelect.append(`<option value="${opt.value}">${opt.text}</option>`);
            });
            
            const aggSelect = $('#vis-aggregation');
            aggSelect.empty();
            config.aggregations.forEach(opt => {
                aggSelect.append(`<option value="${opt.value}">${opt.text}</option>`);
            });
        }

        // Initialize selectors
        populateSelectOptions($('#vis-fact-table').val());

        $('#vis-fact-table').change(function() {
            populateSelectOptions($(this).val());
        });

        $('#btn-generate-chart').click(function() {
            const factTable  = $('#vis-fact-table').val();
            const groupBy    = $('#vis-group-by').val();
            const aggregation = $('#vis-aggregation').val();
            lastChartType    = $('#vis-chart-type').val();
            lastTitleText    = $('#vis-aggregation option:selected').text() + ' by ' + $('#vis-group-by option:selected').text();
            // Extract clean label text (strip the parenthesis part for brevity)
            lastYAxisLabel   = $('#vis-aggregation option:selected').text().replace(/\s*\(.*\)\s*$/, '').trim();
            lastXAxisLabel   = $('#vis-group-by option:selected').text().replace(/\s*\(.*\)\s*$/, '').trim();

            $('#card-visualization-canvas').find('.loading-overlay').addClass('active');
            $('#canvas-nodata-state').addClass('d-none');

            const params = { fact_table: factTable, group_by: groupBy, aggregation: aggregation };

            $.getJSON('/DWH/api/charts.php', params, function(data) {
                // Map groupBy key to correct JSON dataset key
                lastFullDataset = [];
                if (groupBy === 'category') lastFullDataset = data.category_comparison || [];
                else if (groupBy === 'period') lastFullDataset = data.monthly_trend || [];
                else if (groupBy === 'region') lastFullDataset = data.region_distribution || [];
                else if (groupBy === 'rating') lastFullDataset = data.rating_distribution || [];
                else if (groupBy === 'title') lastFullDataset = data.top_films || [];
                else if (groupBy === 'actor') lastFullDataset = data.actor_comparison || [];
                else if (groupBy === 'staff') lastFullDataset = data.staff_comparison || [];

                if (lastFullDataset.length === 0) {
                    $('#canvas-chart-wrapper').addClass('d-none');
                    $('#canvas-nodata-state').removeClass('d-none').addClass('d-flex');
                    $('#card-visualization-canvas').find('.loading-overlay').removeClass('active');
                    return;
                }

                $('#canvas-empty-state').addClass('d-none');
                renderChart(lastFullDataset, lastTitleText, lastChartType, lastYAxisLabel, lastXAxisLabel);

            }).fail(function() {
                $('#card-visualization-canvas').find('.loading-overlay').removeClass('active');
                $('#canvas-empty-state').addClass('d-none');
                $('#canvas-nodata-state').removeClass('d-none').addClass('d-flex');
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
