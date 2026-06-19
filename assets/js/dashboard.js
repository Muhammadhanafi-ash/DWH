/**
 * Enterprise DWH Dashboard - Main JS Controller
 */

$(document).ready(function () {
    let loadedFactTable = '';
    let dataTableInstance = null;
    
    // ApexCharts Instances
    let categoryChart = null;
    let trendChart = null;
    let regionChart = null;
    let ratingChart = null;
    let currentMetricLabel = 'Total';

    // Helper: Format values based on active metric
    function formatMetricValue(value) {
        if (value === null || value === undefined || isNaN(value)) return '-';
        const activeFact = $('#filter-fact').val();
        if (currentMetricLabel.toLowerCase().includes('sales') || currentMetricLabel.toLowerCase().includes('amount') || activeFact === 'fact_sales' || activeFact === 'fact_store_performance') {
            return '$' + parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return parseFloat(value).toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    // Helper: Generate request query parameters from filters
    function getFilterParams() {
        return {
            fact_table: $('#filter-fact').val(),
            year: $('#filter-year').val(),
            month: $('#filter-month').val(),
            category: $('#filter-category').val(),
            region: $('#filter-region').val(),
            start_date: $('#filter-start-date').val(),
            end_date: $('#filter-end-date').val()
        };
    }

    // Helper: Show/Hide Loading states
    function toggleLoading(show) {
        if (show) {
            $('.loading-overlay').addClass('active');
        } else {
            $('.loading-overlay').removeClass('active');
        }
    }

    // Rebuild Datatable dynamically based on column headers
    function initDataTable(columns) {
        if (dataTableInstance) {
            dataTableInstance.destroy();
            $('#table-headers').empty();
            $('#dwh-data-table tbody').empty();
        }

        // Draw headers
        columns.forEach(col => {
            // Make column names pretty (uppercase, replace underscores)
            let prettyName = col.replace(/_/g, ' ').toUpperCase();
            if (prettyName.startsWith('D ')) prettyName = prettyName.substring(2);
            $('#table-headers').append(`<th>${prettyName}</th>`);
        });

        const params = getFilterParams();
        const columnsConfig = columns.map(col => {
            return { data: col };
        });

        // Initialize DataTable
        dataTableInstance = $('#dwh-data-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: '/api/tables.php',
                type: 'POST',
                data: function (d) {
                    // Merge filter parameters
                    return $.extend({}, d, getFilterParams());
                }
            },
            columns: columnsConfig,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                search: "Cari Data:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ record",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 record",
                infoFiltered: "(disaring dari _MAX_ total record)",
                processing: "<div class='spinner-border spinner-border-sm text-primary'></div> Memuat data...",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Lanjut",
                    previous: "Kembali"
                }
            }
        });

        // Reconnect Table Buttons if exports are triggered elsewhere
        loadedFactTable = params.fact_table;
    }

    // Initialize Empty ApexCharts
    function initCharts() {
        const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';
        const gridColor = theme === 'dark' ? '#1f2937' : '#e2e8f0';

        const noDataConfig = {
            text: 'Memuat data...',
            align: 'center',
            verticalAlign: 'middle',
            style: { color: labelColor, fontSize: '14px', fontFamily: 'Outfit' }
        };

        // 1. Category Chart Options (Bar)
        const catOptions = {
            chart: { type: 'bar', height: 320, toolbar: { show: false }, background: 'transparent' },
            theme: { mode: theme },
            colors: ['#1e3a8a', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e', '#14b8a6'],
            plotOptions: { bar: { distributed: true, borderRadius: 6, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            series: [{ name: 'Total', data: [] }],
            xaxis: { categories: [], labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            yaxis: { labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            grid: { borderColor: gridColor },
            legend: { show: false },
            noData: noDataConfig,
            tooltip: {
                y: {
                    formatter: function(val) {
                        return formatMetricValue(val);
                    }
                }
            }
        };
        categoryChart = new ApexCharts(document.querySelector("#category-comparison-chart"), catOptions);
        categoryChart.render();

        // 2. Trend Chart Options (Line)
        const trendOptions = {
            chart: { type: 'line', height: 320, toolbar: { show: false }, background: 'transparent' },
            theme: { mode: theme },
            colors: ['#10b981'],
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4, colors: ['#10b981'], strokeWidth: 2, hover: { size: 6 } },
            dataLabels: { enabled: false },
            series: [{ name: 'Aktivitas', data: [] }],
            xaxis: { categories: [], labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            yaxis: { labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            grid: { borderColor: gridColor },
            noData: noDataConfig,
            tooltip: {
                y: {
                    formatter: function(val) {
                        return formatMetricValue(val);
                    }
                }
            }
        };
        trendChart = new ApexCharts(document.querySelector("#monthly-trend-chart"), trendOptions);
        trendChart.render();

        // 3. Region Chart Options (Area)
        const regOptions = {
            chart: { type: 'area', height: 320, toolbar: { show: false }, background: 'transparent' },
            theme: { mode: theme },
            colors: ['#06b6d4'],
            stroke: { curve: 'straight', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
            dataLabels: { enabled: false },
            series: [{ name: 'Transaksi', data: [] }],
            xaxis: { categories: [], labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            yaxis: { labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            grid: { borderColor: gridColor },
            noData: {
                text: 'Data Wilayah/Store Tidak Tersedia Untuk Tabel Fakta Ini',
                align: 'center',
                verticalAlign: 'middle',
                style: { color: labelColor, fontSize: '14px', fontFamily: 'Outfit' }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return formatMetricValue(val);
                    }
                }
            }
        };
        regionChart = new ApexCharts(document.querySelector("#region-distribution-chart"), regOptions);
        regionChart.render();

        // 4. Rating Chart Options (Doughnut)
        const ratingOptions = {
            chart: { type: 'donut', height: 320, background: 'transparent' },
            theme: { mode: theme },
            colors: ['#1e3a8a', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6'],
            dataLabels: { enabled: true, style: { fontFamily: 'Outfit' } },
            series: [],
            labels: [],
            legend: { position: 'bottom', labels: { colors: labelColor, useSeriesColors: false } },
            noData: noDataConfig,
            tooltip: {
                y: {
                    formatter: function(val) {
                        return formatMetricValue(val);
                    }
                }
            }
        };
        ratingChart = new ApexCharts(document.querySelector("#rating-distribution-chart"), ratingOptions);
        ratingChart.render();
    }

    // Refresh charts styling when theme toggles
    window.addEventListener('theme-changed', (e) => {
        const theme = e.detail.theme;
        const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';
        const gridColor = theme === 'dark' ? '#1f2937' : '#e2e8f0';

        const updateOptions = {
            theme: { mode: theme },
            xaxis: { labels: { style: { colors: labelColor } } },
            yaxis: { labels: { style: { colors: labelColor } } },
            grid: { borderColor: gridColor }
        };

        categoryChart.updateOptions(updateOptions);
        trendChart.updateOptions(updateOptions);
        regionChart.updateOptions(updateOptions);
        
        ratingChart.updateOptions({
            theme: { mode: theme },
            legend: { labels: { colors: labelColor } }
        });
    });

    // Populate chart datasets via AJAX
    function updateCharts() {
        const params = getFilterParams();
        $.getJSON('/api/charts.php', params, function (data) {
            const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';
            
            // 1. Category Chart
            if (data.category_comparison && data.category_comparison.length > 0) {
                const cats = data.category_comparison.map(item => item.category);
                const vals = data.category_comparison.map(item => parseFloat(item.val));
                categoryChart.updateOptions({
                    xaxis: { 
                        categories: cats,
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    yaxis: {
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    series: [{ name: currentMetricLabel, data: vals }]
                });
                $('#category-comparison-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                categoryChart.updateOptions({
                    series: [{ data: [] }],
                    noData: { text: 'Data Kategori Tidak Tersedia Untuk Tabel Fakta Ini' }
                });
            }

            // 2. Monthly Trend Chart
            if (data.monthly_trend && data.monthly_trend.length > 0) {
                const periods = data.monthly_trend.map(item => item.period + ' ' + item.year);
                const vals = data.monthly_trend.map(item => parseFloat(item.val));
                trendChart.updateOptions({
                    xaxis: { 
                        categories: periods,
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    yaxis: {
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    series: [{ name: currentMetricLabel, data: vals }]
                });
                $('#monthly-trend-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                trendChart.updateOptions({
                    series: [{ data: [] }],
                    noData: { text: 'Data Tren Tidak Tersedia Untuk Tabel Fakta Ini' }
                });
            }

            // 3. Region Distribution Chart
            if (data.region_distribution && data.region_distribution.length > 0) {
                // Slice to Top 15 countries to prevent overlap
                const topRegions = data.region_distribution.slice(0, 15);
                
                const shortNames = {
                    'Russian Federation': 'Russia',
                    'United States': 'USA',
                    'United Kingdom': 'UK',
                    'Congo, The Democratic Republic Of The': 'DR Congo',
                    'Holy See (Vatican City State)': 'Vatican City'
                };
                
                const regions = topRegions.map(item => {
                    const name = item.region || 'Unknown';
                    return shortNames[name] || name;
                });
                const vals = topRegions.map(item => parseFloat(item.val) || 0);
                
                const isFiltered = $('#filter-region').val() !== '';
                const xAxisTitle = isFiltered ? 'Kota (Drilldown)' : 'Negara';
                
                regionChart.updateOptions({
                    xaxis: { 
                        categories: regions,
                        title: { text: xAxisTitle, style: { color: labelColor, fontFamily: 'Outfit' } },
                        labels: { 
                            rotate: -45,
                            rotateAlways: false,
                            style: { colors: labelColor, fontFamily: 'Outfit' } 
                        }
                    },
                    yaxis: {
                        title: { text: currentMetricLabel, style: { color: labelColor, fontFamily: 'Outfit' } },
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    series: [{ name: currentMetricLabel, data: vals }]
                });
                $('#region-distribution-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                regionChart.updateOptions({
                    xaxis: { categories: [], title: { text: 'Negara', style: { color: labelColor, fontFamily: 'Outfit' } }, labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
                    yaxis: { title: { text: currentMetricLabel, style: { color: labelColor, fontFamily: 'Outfit' } }, labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
                    series: [{ data: [] }],
                    noData: { text: 'Data Wilayah/Store Tidak Tersedia Untuk Tabel Fakta Ini' }
                });
            }

            // 4. Rating Distribution Chart
            if (data.rating_distribution && data.rating_distribution.length > 0) {
                const ratingDescriptions = {
                    'G': 'G (Semua Umur)',
                    'PG': 'PG (Bimbingan Orang Tua)',
                    'PG-13': 'PG-13 (Remaja 13+)',
                    'R': 'R (Dewasa 17+)',
                    'NC-17': 'NC-17 (Khusus Dewasa)'
                };
                const ratings = data.rating_distribution.map(item => {
                    const rating = item.rating || 'Unknown';
                    return ratingDescriptions[rating] || rating;
                });
                const vals = data.rating_distribution.map(item => parseFloat(item.val));
                ratingChart.updateOptions({
                    labels: ratings,
                    series: vals
                });
                $('#rating-distribution-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                ratingChart.updateOptions({
                    series: [],
                    noData: { text: 'Data Rating Tidak Tersedia Untuk Tabel Fakta Ini' }
                });
            }
        });
    }

    // Refresh KPIs and DataTable
    function loadDashboardData() {
        toggleLoading(true);
        const params = getFilterParams();

        // Fetch KPIs
        $.getJSON('/api/kpis.php', params, function (data) {
            // Update Card values
            $('#kpi-total-data').text(data.total_data.toLocaleString());
            $('#kpi-total-fact').text(data.total_fact.toLocaleString());
            $('#kpi-total-dim').text(data.total_dim.toLocaleString());
            $('#kpi-total-trans').text(data.total_transactions.toLocaleString());
            $('#kpi-last-update').text(data.last_update);
            $('#kpi-query-process').text(data.query_process.toLocaleString());
            
            // Adjust card transaction label dynamically
            if (data.metric_label) {
                currentMetricLabel = data.metric_label;
                $('#kpi-label-trans').text(data.metric_label);
                if (data.metric_value > 0) {
                    $('#kpi-total-trans').text(data.metric_label.includes('Sales') ? '$' + data.metric_value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : data.metric_value.toLocaleString());
                }
            }

            // Initialize or reload Datatable
            if (loadedFactTable !== params.fact_table || !dataTableInstance) {
                initDataTable(data.columns);
            } else {
                dataTableInstance.ajax.reload();
            }
            
            // Update Charts
            updateCharts();
            
            // Remove Loading animation
            setTimeout(() => {
                toggleLoading(false);
            }, 500);
        }).fail(function() {
            toggleLoading(false);
        });
    }

    // Initialize core elements
    initCharts();
    loadDashboardData();

    // Event Listeners for filters
    $('#filter-fact').change(function () {
        loadDashboardData();
    });

    $('.filter-input').change(function () {
        // Only trigger if not the fact table dropdown itself
        if ($(this).attr('id') !== 'filter-fact') {
            toggleLoading(true);
            dataTableInstance.ajax.reload();
            updateCharts();
            
            // Update KPIs count
            $.getJSON('/api/kpis.php', getFilterParams(), function (data) {
                $('#kpi-total-trans').text(data.total_transactions.toLocaleString());
                if (data.metric_label) {
                    if (data.metric_value > 0) {
                        $('#kpi-total-trans').text(data.metric_label.includes('Sales') ? '$' + data.metric_value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : data.metric_value.toLocaleString());
                    }
                }
                setTimeout(() => toggleLoading(false), 300);
            });
        }
    });

    // Clear filters
    $('#clear-filters').click(function () {
        $('#filter-year').val('');
        $('#filter-month').val('');
        $('#filter-category').val('');
        $('#filter-region').val('');
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        loadDashboardData();
    });

    // Manual Refresh button
    $('#btn-refresh').click(function () {
        loadDashboardData();
    });
    
    // Auto-refresh every 60 seconds (Auto Refresh Dashboard requirement)
    setInterval(function() {
        if (dataTableInstance) {
            dataTableInstance.ajax.reload(null, false); // Reload without pagination reset
            updateCharts();
        }
    }, 60000);
});
