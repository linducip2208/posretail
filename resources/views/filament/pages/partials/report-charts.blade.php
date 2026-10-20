{{--
    Helper chart untuk semua halaman laporan.
    Chart.js + tema "PosChart" (gradient, glow, tooltip kaca, center label,
    otomatis sadar dark mode). Panggil: PosChart.bar|line|doughnut(id, {...}).
--}}
<script src="{{ asset('vendor/chart.js/chart.umd.min.js') }}"></script>
<script>
if (!window.PosChart) {
    window.PosChart = (function () {
        'use strict';

        var FONT = "'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif";
        /* Palet brand POS Retail: Primary #2563EB, Secondary #3B82F6,
           Success #10B981, Warning #F59E0B, Error #EF4444, Neutral #64748B. */
        var PALETTE = ['#2563eb', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#14b8a6', '#f97316', '#64748b'];
        var registry = new Set();

        function dark() {
            return document.documentElement.classList.contains('dark');
        }

        function num(v) {
            var n = typeof v === 'number' ? v : parseFloat(v);
            return isFinite(n) ? n : 0;
        }

        function hexToRgb(hex) {
            var h = String(hex).replace('#', '');
            if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
            var n = parseInt(h, 16);
            if (isNaN(n)) return { r: 99, g: 102, b: 241 };
            return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
        }

        function alpha(hex, a) {
            var c = hexToRgb(hex);
            return 'rgba(' + c.r + ', ' + c.g + ', ' + c.b + ', ' + a + ')';
        }

        function gridColor() {
            return dark() ? 'rgba(148, 163, 184, 0.14)' : 'rgba(15, 23, 42, 0.06)';
        }

        function tickColor() {
            return dark() ? '#94a3b8' : '#64748b';
        }

        function legendColor() {
            return dark() ? '#cbd5e1' : '#475569';
        }

        function money(v) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(num(v)));
        }

        function compact(v) {
            var n = num(v);
            var abs = Math.abs(n);
            var sign = n < 0 ? '-' : '';
            if (abs >= 1e12) return sign + 'Rp ' + (abs / 1e12).toFixed(abs >= 1e13 ? 0 : 1) + ' T';
            if (abs >= 1e9) return sign + 'Rp ' + (abs / 1e9).toFixed(abs >= 1e10 ? 0 : 1) + ' M';
            if (abs >= 1e6) return sign + 'Rp ' + (abs / 1e6).toFixed(abs >= 1e7 ? 0 : 1) + ' jt';
            if (abs >= 1e3) return sign + 'Rp ' + (abs / 1e3).toFixed(0) + ' rb';
            return sign + 'Rp ' + Math.round(abs);
        }

        function thousand(v) {
            return new Intl.NumberFormat('id-ID').format(num(v));
        }

        /* Gradient vertikal untuk bar (atau horizontal bila indexAxis = y). */
        function barGradient(context, hex, horizontal) {
            var chart = context.chart;
            var area = chart.chartArea;
            if (!area) return alpha(hex, 0.85);
            var ctx = chart.ctx;
            var g = horizontal
                ? ctx.createLinearGradient(area.left, 0, area.right, 0)
                : ctx.createLinearGradient(0, area.bottom, 0, area.top);
            g.addColorStop(0, alpha(hex, 0.22));
            g.addColorStop(0.55, alpha(hex, 0.65));
            g.addColorStop(1, alpha(hex, 0.98));
            return g;
        }

        /* Gradient area untuk line chart. */
        function areaGradient(hex) {
            return function (context) {
                var area = context.chart.chartArea;
                if (!area) return alpha(hex, 0.2);
                var g = context.chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                g.addColorStop(0, alpha(hex, 0.34));
                g.addColorStop(0.6, alpha(hex, 0.08));
                g.addColorStop(1, alpha(hex, 0.01));
                return g;
            };
        }

        /* Efek glow lembut untuk line chart. */
        function glowPlugin(color) {
            return {
                id: 'posGlow',
                beforeDatasetsDraw: function (chart) {
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.shadowColor = alpha(color, 0.35);
                    ctx.shadowBlur = 16;
                    ctx.shadowOffsetY = 10;
                },
                afterDatasetsDraw: function (chart) {
                    chart.ctx.restore();
                }
            };
        }

        /* Label tengah doughnut. */
        function centerTextPlugin(title, value) {
            return {
                id: 'posCenter',
                afterDraw: function (chart) {
                    var meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || !meta.data[0]) return;
                    var x = meta.data[0].x;
                    var y = meta.data[0].y;
                    var ctx = chart.ctx;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    if (title) {
                        ctx.fillStyle = tickColor();
                        ctx.font = '700 10px ' + FONT;
                        ctx.fillText(String(title).toUpperCase(), x, y - 16);
                    }
                    if (value) {
                        ctx.fillStyle = dark() ? '#f1f5f9' : '#0f172a';
                        ctx.font = '800 17px ' + FONT;
                        ctx.fillText(String(value), x, y + 6);
                    }
                    ctx.restore();
                }
            };
        }

        function tooltipOptions(isMoney) {
            return {
                backgroundColor: dark() ? 'rgba(2, 6, 23, 0.94)' : 'rgba(15, 23, 42, 0.92)',
                titleColor: '#f8fafc',
                bodyColor: '#e2e8f0',
                borderColor: 'rgba(148, 163, 184, 0.28)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 12,
                boxPadding: 6,
                usePointStyle: true,
                titleFont: { family: FONT, weight: '700', size: 12 },
                bodyFont: { family: FONT, weight: '500', size: 12 },
                callbacks: {
                    label: function (c) {
                        var v = c.parsed;
                        if (v && typeof v === 'object') {
                            v = v.y != null ? v.y : (v.x != null ? v.x : v);
                        }
                        var label = c.dataset && c.dataset.label ? c.dataset.label + ': ' : '';
                        return ' ' + label + (isMoney ? money(v) : thousand(v));
                    }
                }
            };
        }

        function legendOptions(display) {
            return {
                display: display !== false,
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    boxWidth: 8,
                    boxHeight: 8,
                    padding: 16,
                    color: legendColor(),
                    font: { family: FONT, size: 12, weight: '600' }
                }
            };
        }

        function scaleOptions(isMoney, horizontal) {
            var moneyTicks = {
                color: tickColor(),
                padding: 8,
                maxTicksLimit: 6,
                precision: 0,
                font: { family: FONT, size: 11, weight: '500' },
                callback: function (v) { return compact(v); }
            };
            var plainTicks = {
                color: tickColor(),
                padding: 6,
                maxRotation: 0,
                autoSkipPadding: 16,
                font: { family: FONT, size: 11, weight: '500' }
            };
            var valueAxis = {
                beginAtZero: true,
                grid: { color: gridColor(), drawTicks: false },
                border: { display: false },
                ticks: isMoney ? moneyTicks : plainTicks
            };
            var labelAxis = {
                grid: { display: false },
                border: { display: false },
                ticks: plainTicks
            };
            return horizontal
                ? { x: valueAxis, y: labelAxis }
                : { x: labelAxis, y: valueAxis };
        }

        function resolve(target) {
            return typeof target === 'string' ? document.getElementById(target) : target;
        }

        function init(target, factory) {
            var el = resolve(target);
            if (!el) return null;
            var run = function () {
                if (el._posChart) {
                    el._posChart.destroy();
                    el._posChart = null;
                }
                el._posChart = new Chart(el.getContext('2d'), factory());
            };
            el._posFactory = run;
            registry.add(el);
            run();
            return el._posChart;
        }

        /* Reinit semua chart saat tema (dark/light) berubah. */
        if (typeof MutationObserver !== 'undefined' && !window.__posChartObserver) {
            window.__posChartObserver = true;
            new MutationObserver(function () {
                registry.forEach(function (el) {
                    if (el.isConnected && el._posFactory) {
                        try { el._posFactory(); } catch (e) { /* noop */ }
                    }
                });
            }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        }

        function bar(target, opts) {
            opts = opts || {};
            var horizontal = !!opts.horizontal;
            var isMoney = opts.money !== false;
            return init(target, function () {
                var datasets = (opts.datasets || []).map(function (d, i) {
                    var color = d.color || PALETTE[i % PALETTE.length];
                    return {
                        label: d.label || '',
                        data: (d.data || []).map(num),
                        backgroundColor: function (c) { return barGradient(c, color, horizontal); },
                        hoverBackgroundColor: alpha(color, 1),
                        borderWidth: 0,
                        borderRadius: 9,
                        borderSkipped: false,
                        maxBarThickness: horizontal ? 22 : 36,
                        barPercentage: 0.72,
                        categoryPercentage: 0.66
                    };
                });
                return {
                    type: 'bar',
                    data: { labels: opts.labels || [], datasets: datasets },
                    options: {
                        indexAxis: horizontal ? 'y' : 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 700, easing: 'easeOutQuart' },
                        layout: { padding: { top: 6, right: 8 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: legendOptions(datasets.length > 1),
                            tooltip: tooltipOptions(isMoney)
                        },
                        scales: scaleOptions(isMoney, horizontal)
                    }
                };
            });
        }

        function line(target, opts) {
            opts = opts || {};
            var isMoney = opts.money !== false;
            var firstColor = (opts.datasets && opts.datasets[0] && opts.datasets[0].color) || PALETTE[0];
            return init(target, function () {
                var datasets = (opts.datasets || []).map(function (d, i) {
                    var color = d.color || PALETTE[i % PALETTE.length];
                    return {
                        label: d.label || '',
                        data: (d.data || []).map(num),
                        borderColor: color,
                        backgroundColor: areaGradient(color),
                        fill: opts.fill !== false,
                        tension: 0.42,
                        borderWidth: 2.6,
                        borderCapStyle: 'round',
                        pointRadius: 0,
                        pointHitRadius: 14,
                        pointHoverRadius: 5,
                        pointHoverBorderWidth: 3,
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: color
                    };
                });
                var plugins = [];
                if (opts.glow !== false) plugins.push(glowPlugin(firstColor));
                return {
                    type: 'line',
                    data: { labels: opts.labels || [], datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800, easing: 'easeOutQuart' },
                        layout: { padding: { top: 8, right: 8 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: legendOptions(datasets.length > 1),
                            tooltip: tooltipOptions(isMoney)
                        },
                        scales: scaleOptions(isMoney, false)
                    },
                    plugins: plugins
                };
            });
        }

        /* Combo bar (uang) + garis (jumlah) dengan dua sumbu. */
        function mixed(target, opts) {
            opts = opts || {};
            var barColor = opts.barColor || PALETTE[0];
            var lineColor = opts.lineColor || PALETTE[3];
            return init(target, function () {
                return {
                    type: 'bar',
                    data: {
                        labels: opts.labels || [],
                        datasets: [
                            {
                                type: 'bar',
                                label: opts.barLabel || '',
                                data: (opts.barData || []).map(num),
                                backgroundColor: function (c) { return barGradient(c, barColor, false); },
                                hoverBackgroundColor: alpha(barColor, 1),
                                borderWidth: 0,
                                borderRadius: 9,
                                borderSkipped: false,
                                maxBarThickness: 36,
                                barPercentage: 0.72,
                                categoryPercentage: 0.66,
                                yAxisID: 'y'
                            },
                            {
                                type: 'line',
                                label: opts.lineLabel || '',
                                data: (opts.lineData || []).map(num),
                                borderColor: lineColor,
                                backgroundColor: areaGradient(lineColor),
                                fill: true,
                                tension: 0.42,
                                borderWidth: 2.6,
                                borderCapStyle: 'round',
                                pointRadius: 0,
                                pointHitRadius: 14,
                                pointHoverRadius: 5,
                                pointHoverBorderWidth: 3,
                                pointHoverBackgroundColor: '#ffffff',
                                pointHoverBorderColor: lineColor,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800, easing: 'easeOutQuart' },
                        layout: { padding: { top: 8, right: 8 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: legendOptions(true),
                            tooltip: tooltipOptions(opts.money !== false)
                        },
                        scales: {
                            x: scaleOptions(false, false).x,
                            y: scaleOptions(true, false).y,
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    color: tickColor(),
                                    padding: 6,
                                    maxTicksLimit: 5,
                                    precision: 0,
                                    font: { family: FONT, size: 11, weight: '500' }
                                }
                            }
                        }
                    }
                };
            });
        }

        function doughnut(target, opts) {
            opts = opts || {};
            var isMoney = opts.money !== false;
            var colors = (opts.colors && opts.colors.length ? opts.colors : PALETTE).slice();
            return init(target, function () {
                var labels = opts.labels || [];
                var data = (opts.data || []).map(num);
                var plugins = [];
                if (opts.centerTitle || opts.centerValue) {
                    plugins.push(centerTextPlugin(opts.centerTitle, opts.centerValue));
                }
                var tooltip = tooltipOptions(isMoney);
                tooltip.callbacks.label = function (c) {
                    var total = (c.dataset.data || []).reduce(function (a, b) { return a + num(b); }, 0);
                    var pct = total > 0 ? (num(c.parsed) / total) * 100 : 0;
                    var value = isMoney ? money(c.parsed) : thousand(c.parsed);
                    return ' ' + c.label + ': ' + value + ' (' + pct.toFixed(1) + '%)';
                };
                return {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: labels.map(function (_, i) { return alpha(colors[i % colors.length], 0.94); }),
                            hoverBackgroundColor: labels.map(function (_, i) { return alpha(colors[i % colors.length], 1); }),
                            borderWidth: 0,
                            borderRadius: 8,
                            spacing: 3,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        animation: { duration: 800, easing: 'easeOutQuart' },
                        layout: { padding: 6 },
                        plugins: {
                            legend: legendOptions(true),
                            tooltip: tooltip
                        }
                    },
                    plugins: plugins
                };
            });
        }

        return {
            palette: PALETTE,
            money: money,
            compact: compact,
            thousand: thousand,
            bar: bar,
            line: line,
            mixed: mixed,
            doughnut: doughnut
        };
    })();
}
</script>
