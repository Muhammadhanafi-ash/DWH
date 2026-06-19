<?php
/**
 * Enterprise DWH Dashboard - Searchable Reports & Exports Panel
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
<div class="page-header mb-4">
    <h1 class="page-title">Export & Reports</h1>
    <p class="page-subtitle">Cari, saring, dan ekspor dataset DWH ke berbagai format laporan (PDF, Excel, Print)</p>
</div>

<!-- Filters Panel -->
<div class="filter-section">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-file-export text-primary me-2"></i>Filter Laporan</h6>
        <button class="btn btn-link btn-sm p-0 text-decoration-none" id="clear-report-filters">Clear All</button>
    </div>
    
    <div class="row g-3">
        <!-- Active Fact Table -->
        <div class="col-md-3 col-sm-6">
            <label class="filter-label" for="report-fact">Select Fact Table</label>
            <select class="filter-input form-select" id="report-fact">
                <?php foreach ($factTables as $ft): ?>
                    <option value="<?php echo htmlspecialchars($ft['name']); ?>" <?php echo $activeFactTable === $ft['name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ft['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Year -->
        <div class="col-md-2 col-sm-6">
            <label class="filter-label" for="report-year">Tahun</label>
            <select class="filter-input form-select" id="report-year">
                <option value="">Semua Tahun</option>
                <?php foreach ($filterYears as $year): ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Category -->
        <div class="col-md-2 col-sm-6">
            <label class="filter-label" for="report-category">Kategori Film</label>
            <select class="filter-input form-select" id="report-category">
                <option value="">Semua Kategori</option>
                <?php foreach ($filterCategories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Region -->
        <div class="col-md-3 col-sm-6">
            <label class="filter-label" for="report-region">Wilayah / Negara</label>
            <select class="filter-input form-select" id="report-region">
                <option value="">Semua Wilayah</option>
                <?php foreach ($filterRegions as $reg): ?>
                    <option value="<?php echo htmlspecialchars($reg); ?>"><?php echo htmlspecialchars($reg); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-2 col-sm-6 d-flex align-items-end">
            <button class="btn btn-primary w-100 btn-sm py-2" id="btn-refresh-report" style="border-radius: var(--radius-sm);"><i class="fa-solid fa-search me-1"></i>Terapkan</button>
        </div>
    </div>
</div>

<!-- Report Dataset Panel -->
<div class="custom-card position-relative mb-4" id="report-dataset-card">
    <div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div></div>
    
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 pb-2 border-bottom">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-table text-primary me-2"></i>Dataset Laporan Terfilter</h5>
        <!-- DataTable Exports Buttons render slot -->
        <div id="report-exports-slot"></div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="report-dt-element" style="width: 100%;">
            <thead>
                <tr id="report-headers">
                    <!-- Dynamic headers -->
                </tr>
            </thead>
            <tbody>
                <!-- Dynamic rows -->
            </tbody>
        </table>
    </div>
</div>

<!-- Javascript Controller for Reports panel -->
<script>
    $(document).ready(function() {
        let reportTableInstance = null;
        let loadedFactTable = '';

        function getReportFilters() {
            return {
                fact_table: $('#report-fact').val(),
                year: $('#report-year').val(),
                category: $('#report-category').val(),
                region: $('#report-region').val()
            };
        }

        function toggleReportLoading(show) {
            if (show) {
                $('#report-dataset-card').find('.loading-overlay').addClass('active');
            } else {
                $('#report-dataset-card').find('.loading-overlay').removeClass('active');
            }
        }

        function initReportDataTable(columns) {
            if (reportTableInstance) {
                reportTableInstance.destroy();
                $('#report-headers').empty();
                $('#report-dt-element tbody').empty();
                $('#report-exports-slot').empty();
            }

            // Draw headers
            columns.forEach(col => {
                let prettyName = col.replace(/_/g, ' ').toUpperCase();
                if (prettyName.startsWith('D ')) prettyName = prettyName.substring(2);
                $('#report-headers').append(`<th>${prettyName}</th>`);
            });

            const currentParams = getReportFilters();
            const colsConfig = columns.map(c => { return { data: c }; });

            // Initialize serverSide DataTable with complete button extensions
            reportTableInstance = $('#report-dt-element').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 15,
                ajax: {
                    url: '/api/tables.php',
                    type: 'POST',
                    data: function(d) {
                        return $.extend({}, d, getReportFilters());
                    }
                },
                columns: colsConfig,
                dom: "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    {
                        extend: 'copy',
                        className: 'btn btn-outline-secondary btn-sm',
                        text: '<i class="fa-solid fa-copy me-1"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-outline-secondary btn-sm',
                        text: '<i class="fa-solid fa-file-csv me-1"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm text-white',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm text-white',
                        text: '<i class="fa-solid fa-file-pdf me-1"></i> PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'DWH Laporan - ' + currentParams.fact_table
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-primary btn-sm text-white',
                        text: '<i class="fa-solid fa-print me-1"></i> Print'
                    }
                ],
                language: {
                    search: "Pencarian:",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total)",
                    paginate: {
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            // Move the generated export buttons to our dedicated navbar container slot
            new $.fn.dataTable.Buttons(reportTableInstance, {
                buttons: reportTableInstance.settings()[0].aButtons
            });
            reportTableInstance.buttons().container().appendTo('#report-exports-slot');

            loadedFactTable = currentParams.fact_table;
            toggleReportLoading(false);
        }

        function loadReportDataset() {
            toggleReportLoading(true);
            const params = getReportFilters();
            
            // Query api/kpis.php to fetch headers metadata first
            $.getJSON('/api/kpis.php', params, function(data) {
                initReportDataTable(data.columns);
            }).fail(function() {
                toggleReportLoading(false);
            });
        }

        // Trigger on load
        loadReportDataset();

        // Bind buttons
        $('#btn-refresh-report').click(function() {
            loadReportDataset();
        });

        $('#report-fact').change(function() {
            loadReportDataset();
        });

        $('#clear-report-filters').click(function() {
            $('#report-year').val('');
            $('#report-category').val('');
            $('#report-region').val('');
            loadReportDataset();
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
