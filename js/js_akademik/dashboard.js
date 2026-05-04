(function(){
  'use strict';
  
  // ===== DOM Elements =====
  const body = document.body;
  const html = document.documentElement;
  const sidebar = document.getElementById('sidebar');
  const menuToggle = document.getElementById('menuToggle');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const KEY_COLLAPSE = 'ak_sidebar_collapsed';
  
  // Chart Elements
  const chartContainer = document.getElementById('barChart');
  const tooltip = document.getElementById('chartTooltip');
  const ttYear = document.getElementById('ttYear');
  const ttValue = document.getElementById('ttValue');
  const ttTrend = document.getElementById('ttTrend');
  
  const rawData = Array.isArray(window.__CHART_DATA__) ? window.__CHART_DATA__ : [];
  
  // ===== Sidebar Functions =====
  function isMobile(){ return window.innerWidth <= 860; }
  function syncBurgerIcon(){
    if(!menuToggle) return;
    const isX = (isMobile() && body.classList.contains('sidebar-open')) || (!isMobile() && body.classList.contains('sidebar-collapsed'));
    menuToggle.classList.toggle('is-x', isX);
  }
  function applyPersistedCollapse(){
    if(isMobile()){ body.classList.remove('sidebar-collapsed'); html.classList.remove('sidebar-collapsed-init'); return; }
    const saved = localStorage.getItem(KEY_COLLAPSE);
    if(saved === '1') body.classList.add('sidebar-collapsed'); else body.classList.remove('sidebar-collapsed');
    html.classList.remove('sidebar-collapsed-init'); syncBurgerIcon();
  }
  function saveCollapseState(){
    const v = body.classList.contains('sidebar-collapsed') ? '1' : '0';
    try{ localStorage.setItem(KEY_COLLAPSE, v); }catch(e){}
  }
  function closeMobileSidebar(){ body.classList.remove('sidebar-open'); syncBurgerIcon(); }
  function toggleSidebar(){
    if(isMobile()) body.classList.toggle('sidebar-open');
    else { body.classList.toggle('sidebar-collapsed'); saveCollapseState(); }
    syncBurgerIcon();
  }
  
  // ===== BAR CHART FUNCTIONS =====
  
  function formatNum(n){ return new Intl.NumberFormat('id-ID').format(n || 0); }
  
  function calcTrend(prev, curr){
    if(prev == null || prev === 0) return { text: 'Baru', class: 'stable', icon: '✨' };
    const diff = ((curr - prev) / prev) * 100;
    if(diff > 10) return { text: `+${diff.toFixed(1)}%`, class: 'up', icon: '📈' };
    if(diff > 2) return { text: `+${diff.toFixed(1)}%`, class: 'up', icon: '↑' };
    if(diff < -10) return { text: `${diff.toFixed(1)}%`, class: 'down', icon: '📉' };
    if(diff < -2) return { text: `${diff.toFixed(1)}%`, class: 'down', icon: '↓' };
    return { text: 'Stabil', class: 'stable', icon: '→' };
  }
  
  function renderBarChart(){
    if(!chartContainer) return;
    
    const series = rawData;
    const containerH = 240;
    const padding = { top: 20, bottom: 40 };
    const chartH = containerH - padding.top - padding.bottom;
    
    // Empty state
    if(!series || series.length === 0){
      chartContainer.innerHTML = '<div class="chart-empty">📊 Data chart belum tersedia<br><span style="font-size:10px;opacity:0.7">Data akan muncul setelah ada mahasiswa terdaftar</span></div>';
      return;
    }
    
    // Find max value for scaling
    const maxVal = Math.max(...series.map(s => s.total), 1);
    const scaleMax = maxVal <= 10 ? 10 : Math.ceil(maxVal / 10) * 10;
    
    // Build bars HTML
    const barsHtml = series.map((item, i) => {
      const height = (item.total / scaleMax) * chartH;
      const trend = i > 0 ? calcTrend(series[i-1].total, item.total) : { text: '-', class: 'stable', icon: '•' };
      
      return `
        <div class="bar-item" 
             data-tahun="${item.tahun}" 
             data-value="${item.total}" 
             data-trend="${trend.text}"
             data-trend-class="${trend.class}"
             data-icon="${trend.icon}"
             tabindex="0"
             role="button"
             aria-label="${item.tahun}: ${item.total} mahasiswa">
          <span class="bar-label">${formatNum(item.total)}</span>
          <div class="bar-fill" style="height: ${Math.max(height, 4)}px"></div>
          <span class="bar-year">${item.tahun}</span>
        </div>
      `;
    }).join('');
    
    chartContainer.innerHTML = barsHtml;
    
    // ===== Tooltip & Interaction =====
    const bars = chartContainer.querySelectorAll('.bar-item');
    
    function showTooltip(bar){
      if(!tooltip) return;
      
      const tahun = bar.dataset.tahun;
      const value = parseInt(bar.dataset.value);
      const trendText = bar.dataset.trend;
      const trendClass = bar.dataset.trendClass;
      const trendIcon = bar.dataset.icon;
      
      ttYear.textContent = tahun;
      ttValue.textContent = `${formatNum(value)} Mahasiswa`;
      ttTrend.textContent = `${trendIcon} ${trendText}`;
      ttTrend.className = `tooltip-trend ${trendClass}`;
      
      // Position tooltip above bar
      const rect = bar.getBoundingClientRect();
      const containerRect = chartContainer.getBoundingClientRect();
      
      const tooltipX = rect.left - containerRect.left + rect.width/2 - 75;
      const tooltipY = rect.top - containerRect.top - 90;
      
      tooltip.style.left = `${Math.max(10, Math.min(tooltipX, containerRect.width - 160))}px`;
      tooltip.style.top = `${Math.max(10, tooltipY)}px`;
      tooltip.classList.add('show');
      tooltip.setAttribute('aria-hidden', 'false');
      
      // Highlight bar
      bar.querySelector('.bar-fill').style.filter = 'brightness(1.15)';
    }
    
    function hideTooltip(){
      if(!tooltip) return;
      tooltip.classList.remove('show');
      tooltip.setAttribute('aria-hidden', 'true');
      
      // Reset bar highlight
      bars.forEach(bar => {
        const fill = bar.querySelector('.bar-fill');
        if(fill) fill.style.filter = '';
      });
    }
    
    // Mouse events
    bars.forEach(bar => {
      bar.addEventListener('mouseenter', () => showTooltip(bar));
      bar.addEventListener('mouseleave', hideTooltip);
      bar.addEventListener('focus', () => showTooltip(bar));
      bar.addEventListener('blur', hideTooltip);
      
      // Click to keep tooltip (for mobile)
      bar.addEventListener('click', (e) => {
        e.preventDefault();
        if(tooltip.classList.contains('show')){
          hideTooltip();
        } else {
          showTooltip(bar);
          // Auto hide after 3 seconds
          setTimeout(hideTooltip, 3000);
        }
      });
    });
    
    // Hide tooltip when clicking outside
    document.addEventListener('click', (e) => {
      if(!chartContainer.contains(e.target) && !tooltip.contains(e.target)){
        hideTooltip();
      }
    });
  }
  
  // ===== Init =====
  applyPersistedCollapse();
  
  if(menuToggle) menuToggle.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); toggleSidebar(); });
  if(sidebarOverlay) sidebarOverlay.addEventListener('click', closeMobileSidebar);
  
  document.addEventListener('click', e => {
    if(isMobile() && body.classList.contains('sidebar-open')){
      const inside = sidebar && sidebar.contains(e.target);
      const toggle = menuToggle && menuToggle.contains(e.target);
      if(!inside && !toggle) closeMobileSidebar();
    }
  });
  
  window.addEventListener('resize', () => {
    if(!isMobile()){ closeMobileSidebar(); applyPersistedCollapse(); } else syncBurgerIcon();
    // Debounced re-render
    clearTimeout(renderBarChart._timer);
    renderBarChart._timer = setTimeout(renderBarChart, 150);
  });
  
  document.addEventListener('keydown', e => { if(e.key === 'Escape') { closeMobileSidebar(); if(tooltip) hideTooltip(); } });
  
  // Render chart on load
  document.addEventListener('DOMContentLoaded', () => {
    renderBarChart();
    syncBurgerIcon();
  });
  
  // Debug export
  if(window.location.hostname === 'localhost'){
    window.__DashboardApp = { renderBarChart, toggleSidebar, calcTrend };
  }
  
})();