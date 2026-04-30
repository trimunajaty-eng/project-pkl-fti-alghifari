/**
 * Dashboard Akademik - Trading Style Wave Chart
 * Version: 5.0
 * Fitur: Smooth wave animation, interactive tooltip, cursor tracking
 */

(function () {
    'use strict';

    // ===== DOM Elements =====
    const body = document.body;
    const html = document.documentElement;
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const chartCanvas = document.getElementById('chartCanvas');
    const yMaxEl = document.getElementById('yMax');
    const yMidEl = document.getElementById('yMid');
    const chartXLabelsEl = document.getElementById('chartXLabels');
    
    const KEY_COLLAPSE = 'ak_sidebar_collapsed';
    const rawData = Array.isArray(window.__CHART_DATA__) ? window.__CHART_DATA__ : [];
    
    let tooltip = null;
    let cursorLine = null;
    let cursorDot = null;

    console.log('[Dashboard] Chart data loaded:', rawData);

    // ===== Utility Functions =====
    function isMobile() { 
        return window.innerWidth <= 860; 
    }

    function formatNum(n) { 
        return new Intl.NumberFormat('id-ID').format(n || 0); 
    }

    function calculateTrend(prev, curr) {
        if (prev == null || prev === 0) {
            return { text: 'Baru', class: 'up', icon: '✨', diff: 0 };
        }
        const diff = ((curr - prev) / prev) * 100;
        if (diff > 10) return { text: `+${diff.toFixed(1)}%`, class: 'up', icon: '📈', diff };
        if (diff > 2) return { text: `+${diff.toFixed(1)}%`, class: 'up', icon: '↑', diff };
        if (diff < -10) return { text: `${diff.toFixed(1)}%`, class: 'down', icon: '📉', diff };
        if (diff < -2) return { text: `${diff.toFixed(1)}%`, class: 'down', icon: '↓', diff };
        return { text: 'Stabil', class: 'stable', icon: '→', diff };
    }

    // Create smooth curve path using Catmull-Rom spline
    function createSmoothPath(points, tension = 0.4) {
        if (!points || points.length === 0) return '';
        if (points.length === 1) {
            return `M ${points[0].x} ${points[0].y}`;
        }
        if (points.length === 2) {
            return `M ${points[0].x} ${points[0].y} L ${points[1].x} ${points[1].y}`;
        }
        
        let d = `M ${points[0].x} ${points[0].y}`;
        
        for (let i = 0; i < points.length - 1; i++) {
            const p0 = points[Math.max(0, i - 1)];
            const p1 = points[i];
            const p2 = points[i + 1];
            const p3 = points[Math.min(points.length - 1, i + 2)];
            
            const cp1x = p1.x + (p2.x - p0.x) * tension / 3;
            const cp1y = p1.y + (p2.y - p0.y) * tension / 3;
            const cp2x = p2.x - (p3.x - p1.x) * tension / 3;
            const cp2y = p2.y - (p3.y - p1.y) * tension / 3;
            
            d += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2.x} ${p2.y}`;
        }
        
        return d;
    }

    // Create or get tooltip element
    function getTooltip() {
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.id = 'chartTooltip';
            tooltip.className = 'chart-tooltip';
            tooltip.setAttribute('aria-hidden', 'true');
            tooltip.innerHTML = `
                <div class="tooltip-header">
                    <span class="tooltip-year">----</span>
                    <span class="tooltip-value">---</span>
                </div>
                <div class="tooltip-trend">
                    <span class="trend-badge stable">
                        <span class="trend-icon">→</span> Stabil
                    </span>
                </div>
            `;
            if (chartCanvas && chartCanvas.parentElement) {
                chartCanvas.parentElement.appendChild(tooltip);
            }
        }
        return tooltip;
    }

    // ===== SIDEBAR FUNCTIONS =====
    function syncBurgerIcon() {
        if (!menuToggle) return;
        const isX = (isMobile() && body.classList.contains('sidebar-open')) ||
                    (!isMobile() && body.classList.contains('sidebar-collapsed'));
        menuToggle.classList.toggle('is-x', isX);
    }

    function applyPersistedCollapse() {
        if (isMobile()) {
            body.classList.remove('sidebar-collapsed');
            html.classList.remove('sidebar-collapsed-init');
            return;
        }
        const saved = localStorage.getItem(KEY_COLLAPSE);
        if (saved === '1') {
            body.classList.add('sidebar-collapsed');
        } else {
            body.classList.remove('sidebar-collapsed');
        }
        html.classList.remove('sidebar-collapsed-init');
        syncBurgerIcon();
    }

    function saveCollapseState() {
        const v = body.classList.contains('sidebar-collapsed') ? '1' : '0';
        try { localStorage.setItem(KEY_COLLAPSE, v); } catch (e) {}
    }

    function closeMobileSidebar() { 
        body.classList.remove('sidebar-open'); 
        syncBurgerIcon(); 
    }

    function toggleSidebar() {
        if (isMobile()) {
            body.classList.toggle('sidebar-open');
        } else {
            body.classList.toggle('sidebar-collapsed');
            saveCollapseState();
        }
        syncBurgerIcon();
    }

    // ===== CHART RENDERING =====
    function renderChart() {
        console.log('[Dashboard] renderChart() called');
        console.log('[Dashboard] chartCanvas element:', chartCanvas);
        console.log('[Dashboard] rawData:', rawData);
        
        if (!chartCanvas) {
            console.error('[Dashboard] chartCanvas element not found!');
            return;
        }

        const tooltip = getTooltip();
        const series = rawData;
        
        // Get dimensions
        const width = Math.max(chartCanvas.clientWidth, 340);
        const height = window.innerWidth <= 640 ? 220 : 260;
        
        console.log('[Dashboard] Chart dimensions:', { width, height });

        // Empty state
        if (!series || series.length === 0) {
            console.log('[Dashboard] No chart data available');
            chartCanvas.innerHTML = `
                <div style="display:flex;align-items:center;justify-content:center;height:${height}px;color:#64748b;font-size:11px;gap:8px;">
                    <span style="font-size:24px;opacity:0.5;">📊</span>
                    <div>
                        <div style="font-weight:600;margin-bottom:4px;">Belum ada data chart</div>
                        <div style="font-size:9.5px;opacity:0.8;">Data pendaftaran akan muncul setelah ada mahasiswa terdaftar</div>
                    </div>
                </div>
            `;
            if (yMaxEl) yMaxEl.textContent = '0';
            if (yMidEl) yMidEl.textContent = '0';
            if (chartXLabelsEl) chartXLabelsEl.innerHTML = '';
            return;
        }

        // Chart configuration
        const padding = { top: 24, right: 24, bottom: 36, left: 4 };
        const innerW = width - padding.left - padding.right;
        const innerH = height - padding.top - padding.bottom;
        
        const values = series.map(s => s.total);
        const maxVal = Math.max(...values, 1);
        const maxValue = maxVal <= 20 ? 20 : Math.ceil(maxVal / 10) * 10;
        const midValue = Math.round(maxValue / 2);

        // Update Y-axis labels
        if (yMaxEl) yMaxEl.textContent = formatNum(maxValue);
        if (yMidEl) yMidEl.textContent = formatNum(midValue);

        // Generate points
        const points = series.map((item, i) => {
            const x = padding.left + (series.length === 1 ? innerW / 2 : (i * (innerW / (series.length - 1))));
            const y = padding.top + innerH - ((item.total / maxValue) * innerH);
            return { ...item, x, y, index: i };
        });

        console.log('[Dashboard] Generated points:', points);

        // Create wave path
        const wavePath = createSmoothPath(points, 0.4);
        
        // Create area path
        const areaPath = wavePath + 
            ` L ${points[points.length-1].x} ${padding.top + innerH}` +
            ` L ${points[0].x} ${padding.top + innerH} Z`;

        // Grid lines
        const gridLines = [0, 0.5, 1].map(step => {
            const y = padding.top + innerH - (step * innerH);
            return `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" 
                    stroke="rgba(148,163,184,0.12)" stroke-dasharray="5 5" stroke-width="1"></line>`;
        }).join('');

        // Data point circles
        const pointCircles = points.map((p, i) => `
            <circle class="chart-point ${i === points.length - 1 ? 'active' : ''}" 
                    cx="${p.x}" cy="${p.y}" r="5"
                    data-tahun="${p.tahun}" data-value="${p.total}" data-index="${i}"
                    style="animation-delay: ${0.3 + (i * 0.08)}s">
            </circle>
        `).join('');

        // X-axis labels (SVG)
        const xLabelsSvg = points.map(p => `
            <text x="${p.x}" y="${height - 12}" 
                  text-anchor="middle" fill="#64748b" 
                  font-size="9.5" font-weight="500" font-family="Poppins">
                ${p.tahun}
            </text>
        `).join('');

        // X-axis labels (HTML container)
        if (chartXLabelsEl) {
            chartXLabelsEl.innerHTML = points.map(p => `
                <div class="chart-x-label" data-tahun="${p.tahun}">${p.tahun}</div>
            `).join('');
        }

        // Hover zones
        const hoverZones = points.map((p, i) => {
            const zoneWidth = series.length > 1 ? (innerW / (series.length - 1)) * 0.8 : 60;
            return `<rect x="${p.x - zoneWidth/2}" y="${padding.top}" 
                    width="${zoneWidth}" height="${innerH}" 
                    fill="transparent" class="chart-hover-zone"
                    data-tahun="${p.tahun}" data-value="${p.total}" 
                    data-index="${i}" data-x="${p.x}" data-y="${p.y}">
            </rect>`;
        }).join('');

        // Cursor elements
        const cursorSVG = `
            <line class="chart-cursor-line" x1="0" y1="${padding.top}" x2="0" y2="${padding.top + innerH}"></line>
            <circle class="chart-cursor-dot" cx="0" cy="0" r="4"></circle>
        `;

        // Render SVG
        chartCanvas.innerHTML = `
            <svg class="chart-svg" viewBox="0 0 ${width} ${height}" preserveAspectRatio="none" style="width:100%;height:${height}px;">
                <defs>
                    <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#b91c1c"/>
                        <stop offset="50%" stop-color="#dc2626"/>
                        <stop offset="100%" stop-color="#ea580c"/>
                    </linearGradient>
                    <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="rgba(185,28,28,0.35)"/>
                        <stop offset="40%" stop-color="rgba(220,38,38,0.15)"/>
                        <stop offset="100%" stop-color="rgba(234,88,12,0.02)"/>
                    </linearGradient>
                    <filter id="waveGlow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur stdDeviation="3" result="blur"/>
                        <feFlood flood-color="#b91c1c" flood-opacity="0.4" result="glowColor"/>
                        <feComposite in="glowColor" in2="blur" operator="in" result="softGlow"/>
                        <feMerge>
                            <feMergeNode in="softGlow"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
                
                ${gridLines}
                <path class="chart-wave-area ${points.length > 1 ? 'animated' : ''}" d="${areaPath}"></path>
                <path class="chart-wave-line ${points.length > 1 ? 'animated' : ''}" d="${wavePath}" filter="url(#waveGlow)"></path>
                ${cursorSVG}
                ${pointCircles}
                ${xLabelsSvg}
                ${hoverZones}
            </svg>
        `;

        console.log('[Dashboard] SVG rendered successfully');

        // ===== Interactive Logic =====
        cursorLine = chartCanvas.querySelector('.chart-cursor-line');
        cursorDot = chartCanvas.querySelector('.chart-cursor-dot');
        const hoverZonesEl = chartCanvas.querySelectorAll('.chart-hover-zone');
        const pointCirclesEl = chartCanvas.querySelectorAll('.chart-point');

        function updateTooltip(e, data) {
            if (!tooltip) return;
            
            const rect = chartCanvas.getBoundingClientRect();
            const canvasX = e.clientX - rect.left;
            const canvasY = e.clientY - rect.top;
            
            const prevIdx = data.index - 1;
            const prevVal = prevIdx >= 0 ? series[prevIdx]?.total : null;
            const trend = calculateTrend(prevVal, data.value);
            
            // Update tooltip content
            tooltip.querySelector('.tooltip-year').textContent = data.tahun;
            tooltip.querySelector('.tooltip-value').textContent = `${formatNum(data.value)} Mahasiswa`;
            const trendBadge = tooltip.querySelector('.trend-badge');
            trendBadge.className = `trend-badge ${trend.class}`;
            trendBadge.innerHTML = `<span class="trend-icon">${trend.icon}</span> ${trend.text}`;
            
            // Position tooltip
            const tooltipW = 160, tooltipH = 90;
            let tipX = canvasX - tooltipW / 2;
            let tipY = canvasY - tooltipH - 12;
            
            if (tipX < 8) tipX = 8;
            if (tipX + tooltipW > rect.width - 8) tipX = rect.width - tooltipW - 8;
            if (tipY < 8) tipY = canvasY + 20;
            
            tooltip.style.left = `${tipX}px`;
            tooltip.style.top = `${tipY}px`;
            tooltip.classList.add('show');
            tooltip.setAttribute('aria-hidden', 'false');
            
            // Update cursor
            if (cursorLine) {
                cursorLine.setAttribute('x1', data.x);
                cursorLine.setAttribute('x2', data.x);
                cursorLine.classList.add('active');
            }
            if (cursorDot) {
                cursorDot.setAttribute('cx', data.x);
                cursorDot.setAttribute('cy', data.y);
                cursorDot.classList.add('active');
            }
            
            // Highlight active point
            pointCirclesEl.forEach((el, idx) => {
                el.classList.toggle('active', idx === data.index);
            });
        }

        function hideTooltip() {
            if (!tooltip) return;
            tooltip.classList.remove('show');
            tooltip.setAttribute('aria-hidden', 'true');
            if (cursorLine) cursorLine.classList.remove('active');
            if (cursorDot) cursorDot.classList.remove('active');
            pointCirclesEl.forEach(el => el.classList.remove('active'));
            if (pointCirclesEl.length) {
                pointCirclesEl[pointCirclesEl.length - 1]?.classList.add('active');
            }
        }

        // Mouse events
        hoverZonesEl.forEach(zone => {
            zone.addEventListener('mouseenter', (e) => {
                const data = {
                    tahun: zone.dataset.tahun,
                    value: parseInt(zone.dataset.value),
                    index: parseInt(zone.dataset.index),
                    x: parseFloat(zone.dataset.x),
                    y: parseFloat(zone.dataset.y)
                };
                updateTooltip(e, data);
            });
            
            zone.addEventListener('mousemove', (e) => {
                const data = {
                    tahun: zone.dataset.tahun,
                    value: parseInt(zone.dataset.value),
                    index: parseInt(zone.dataset.index),
                    x: parseFloat(zone.dataset.x),
                    y: parseFloat(zone.dataset.y)
                };
                updateTooltip(e, data);
            });
            
            zone.addEventListener('mouseleave', hideTooltip);
        });

        // Point click
        pointCirclesEl.forEach(point => {
            point.addEventListener('click', (e) => {
                e.stopPropagation();
                const data = {
                    tahun: point.dataset.tahun,
                    value: parseInt(point.dataset.value),
                    index: parseInt(point.dataset.index),
                    x: parseFloat(point.getAttribute('cx')),
                    y: parseFloat(point.getAttribute('cy'))
                };
                updateTooltip({ clientX: e.clientX, clientY: e.clientY }, data);
            });
        });

        // Touch support
        hoverZonesEl.forEach(zone => {
            zone.addEventListener('touchstart', (e) => {
                e.preventDefault();
                const touch = e.touches[0];
                const data = {
                    tahun: zone.dataset.tahun,
                    value: parseInt(zone.dataset.value),
                    index: parseInt(zone.dataset.index),
                    x: parseFloat(zone.dataset.x),
                    y: parseFloat(zone.dataset.y)
                };
                updateTooltip(touch, data);
            }, { passive: false });
        });

        // Hide on outside click
        document.addEventListener('click', (e) => {
            if (!chartCanvas.contains(e.target) && tooltip && !tooltip.contains(e.target)) {
                hideTooltip();
            }
        });
    }

    // ===== SIDEBAR EVENT LISTENERS =====
    if (menuToggle) {
        menuToggle.addEventListener('click', (e) => { 
            e.preventDefault(); 
            e.stopPropagation(); 
            toggleSidebar(); 
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeMobileSidebar);
    }
    
    document.addEventListener('click', (e) => {
        if (isMobile() && body.classList.contains('sidebar-open')) {
            const inside = sidebar && sidebar.contains(e.target);
            const toggle = menuToggle && menuToggle.contains(e.target);
            if (!inside && !toggle) closeMobileSidebar();
        }
    });
    
    window.addEventListener('resize', () => {
        if (!isMobile()) { 
            closeMobileSidebar(); 
            applyPersistedCollapse(); 
        } else {
            syncBurgerIcon();
        }
        // Debounced chart re-render
        clearTimeout(renderChart._resizeTimer);
        renderChart._resizeTimer = setTimeout(renderChart, 150);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileSidebar();
            if (tooltip && tooltip.classList.contains('show')) {
                hideTooltip();
            }
        }
    });

    // ===== INITIALIZATION =====
    function init() {
        console.log('[Dashboard] Initializing...');
        applyPersistedCollapse();
        
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                console.log('[Dashboard] DOMContentLoaded');
                renderChart();
                syncBurgerIcon();
            });
        } else {
            console.log('[Dashboard] DOM already ready');
            renderChart();
            syncBurgerIcon();
        }
    }

    // Run initialization
    init();

    // Export for debugging
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        window.__DashboardApp = { 
            renderChart, 
            toggleSidebar, 
            calculateTrend,
            rawData,
            chartCanvas
        };
        console.log('[Dashboard] Debug tools available at window.__DashboardApp');
    }

})();