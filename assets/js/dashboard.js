/**
 * Enterprise DWH Dashboard - Main JS Controller
 */

$(document).ready(function () {
    let loadedFactTable = '';
    
    // ApexCharts Instances
    let categoryChart = null;
    let trendChart = null;
    let regionChart = null;
    let ratingChart = null;
    let actorChart = null;
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
                    formatter: function(val, opts) {
                        if (opts && opts.globals && opts.globals.seriesNames) {
                            const seriesName = opts.globals.seriesNames[opts.seriesIndex];
                            if (seriesName === 'Total Rentals' || seriesName === 'Rentals') {
                                return parseFloat(val).toLocaleString('en-US', { maximumFractionDigits: 0 });
                            }
                            if (seriesName === 'Total Sales' || seriesName === 'Sales') {
                                return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
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
                text: 'Data Wilayah Tidak Tersedia',
                align: 'center',
                verticalAlign: 'middle',
                style: { color: labelColor, fontSize: '14px', fontFamily: 'Outfit' }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

        // 5. Actor Chart Options (Horizontal Bar)
        const actorOptions = {
            chart: { type: 'bar', height: 380, toolbar: { show: false }, background: 'transparent' },
            theme: { mode: theme },
            colors: ['#8b5cf6'],
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
            dataLabels: { enabled: true, style: { fontFamily: 'Outfit' } },
            series: [{ name: 'Rentals', data: [] }],
            xaxis: { categories: [], labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            yaxis: { labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
            grid: { borderColor: gridColor },
            noData: noDataConfig,
            tooltip: {
                y: {
                    formatter: function(val) {
                        return parseFloat(val).toLocaleString('en-US') + ' rentals';
                    }
                }
            }
        };
        actorChart = new ApexCharts(document.querySelector("#actor-performance-chart"), actorOptions);
        actorChart.render();
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
        actorChart.updateOptions(updateOptions);
        
        ratingChart.updateOptions({
            theme: { mode: theme },
            legend: { labels: { colors: labelColor } }
        });
    });

    // Populate chart datasets via AJAX
    function updateCharts() {
        const params = getFilterParams();
        
        // 1. Fetch store performance data (for Trend)
        $.getJSON('/api/charts.php', params, function (data) {
            const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';

            // Monthly Trend Chart (Combined Sales & Rentals for Store Performance)
            if (data.monthly_trend && data.monthly_trend.length > 0) {
                const periods = data.monthly_trend.map(item => item.period + ' ' + item.year);
                const activeFact = $('#filter-fact').val();

                if (activeFact === 'fact_store_performance') {
                    const salesVals = data.monthly_trend.map(item => parseFloat(item.val_sales) || 0);
                    const rentalVals = data.monthly_trend.map(item => parseFloat(item.val_rentals) || 0);
                    
                    trendChart.updateOptions({
                        xaxis: { 
                            categories: periods,
                            labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                        },
                        yaxis: [
                            {
                                title: { text: "Sales (USD)", style: { color: '#10b981', fontFamily: 'Outfit' } },
                                labels: { 
                                    style: { colors: '#10b981', fontFamily: 'Outfit' },
                                    formatter: function(val) { return '$' + parseFloat(val).toLocaleString('en-US'); }
                                }
                            },
                            {
                                opposite: true,
                                title: { text: "Rentals", style: { color: '#3b82f6', fontFamily: 'Outfit' } },
                                labels: { 
                                    style: { colors: '#3b82f6', fontFamily: 'Outfit' },
                                    formatter: function(val) { return parseFloat(val).toLocaleString('en-US'); }
                                }
                            }
                        ],
                        colors: ['#10b981', '#3b82f6'],
                        series: [
                            { name: 'Total Sales', data: salesVals },
                            { name: 'Total Rentals', data: rentalVals }
                        ],
                        stroke: { curve: 'smooth', width: [3, 3] },
                        markers: { size: 4, colors: ['#10b981', '#3b82f6'], strokeWidth: 2 }
                    });
                } else {
                    const vals = data.monthly_trend.map(item => parseFloat(item.val) || 0);
                    trendChart.updateOptions({
                        xaxis: { 
                            categories: periods,
                            labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                        },
                        yaxis: {
                            labels: { style: { colors: labelColor, fontFamily: 'Outfit' } },
                            title: { text: '' }
                        },
                        colors: ['#10b981'],
                        series: [{ name: currentMetricLabel, data: vals }],
                        stroke: { curve: 'smooth', width: 3 },
                        markers: { size: 4, colors: ['#10b981'], strokeWidth: 2 }
                    });
                }
                $('#monthly-trend-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                trendChart.updateOptions({
                    series: [{ data: [] }],
                    noData: { text: 'Data Tren Tidak Tersedia Untuk Tabel Fakta Ini' }
                });
            }
        });

        // 2. Fetch film-related data from fact_sales (for Category, Rating, and Region Distribution for ALL countries)
        const filmParams = $.extend({}, params, { fact_table: 'fact_sales' });
        $.getJSON('/api/charts.php', filmParams, function (data) {
            const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';

            // Category Comparison Chart
            if (data.category_comparison && data.category_comparison.length > 0) {
                const cats = data.category_comparison.map(item => item.category);
                const vals = data.category_comparison.map(item => parseFloat(item.val));
                
                $('#card-comparison-chart').find('.card-title').html(`<i class="fa-solid fa-chart-simple text-primary me-2"></i>Perbandingan Kategori`);
                categoryChart.updateOptions({
                    xaxis: { 
                        categories: cats,
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    yaxis: {
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    series: [{ name: 'Total Sales', data: vals }]
                });
                $('#category-comparison-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                categoryChart.updateOptions({
                    series: [{ data: [] }],
                    noData: { text: 'Data Kategori Tidak Tersedia' }
                });
            }

            // Rating Distribution Chart
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
                    noData: { text: 'Data Rating Tidak Tersedia' }
                });
            }

            // Region Distribution Chart (forces fact_sales to list ALL countries)
            if (data.region_distribution && data.region_distribution.length > 0) {
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
                
                regionChart.updateOptions({
                    xaxis: { 
                        categories: regions,
                        title: { text: 'Negara', style: { color: labelColor, fontFamily: 'Outfit' } },
                        labels: { 
                            rotate: -45,
                            rotateAlways: false,
                            style: { colors: labelColor, fontFamily: 'Outfit' } 
                        }
                    },
                    yaxis: {
                        title: { text: 'Total Sales', style: { color: labelColor, fontFamily: 'Outfit' } },
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    series: [{ name: 'Total Sales', data: vals }]
                });
                $('#region-distribution-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                regionChart.updateOptions({
                    xaxis: { categories: [], title: { text: 'Negara', style: { color: labelColor, fontFamily: 'Outfit' } }, labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
                    yaxis: { title: { text: 'Total Sales', style: { color: labelColor, fontFamily: 'Outfit' } }, labels: { style: { colors: labelColor, fontFamily: 'Outfit' } } },
                    series: [{ data: [] }],
                    noData: { text: 'Data Wilayah Tidak Tersedia' }
                });
            }
        });

        // 3. Fetch actor performance data from fact_actor_performance
        const actorParams = $.extend({}, params, { fact_table: 'fact_actor_performance' });
        $.getJSON('/api/charts.php', actorParams, function (data) {
            const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const labelColor = theme === 'dark' ? '#94a3b8' : '#64748b';

            if (data.actor_comparison && data.actor_comparison.length > 0) {
                const topActors = data.actor_comparison.slice(0, 10);
                const actors = topActors.map(item => item.actor);
                const vals = topActors.map(item => parseFloat(item.val));

                actorChart.updateOptions({
                    xaxis: { 
                        categories: actors,
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    yaxis: {
                        labels: { style: { colors: labelColor, fontFamily: 'Outfit' } }
                    },
                    series: [{ name: 'Total Rentals', data: vals }]
                });
                $('#actor-performance-chart').parent().find('.loading-overlay').removeClass('active');
            } else {
                actorChart.updateOptions({
                    series: [{ data: [] }],
                    noData: { text: 'Data Aktor Tidak Tersedia' }
                });
            }
        });
    }

    // Refresh KPIs
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

    // Auto-refresh every 60 seconds (Auto Refresh Dashboard requirement)
    setInterval(function() {
        updateCharts();
    }, 60000);
});
