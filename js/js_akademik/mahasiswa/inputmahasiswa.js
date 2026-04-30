(function(){
  'use strict';
  
  // ===== DOM Elements =====
  const body = document.body;
  const html = document.documentElement;
  const sidebar = document.getElementById('sidebar');
  const menuToggle = document.getElementById('menuToggle');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const KEY_COLLAPSE = 'ak_sidebar_collapsed';
  
  // Tabs
  const tabs = document.querySelectorAll('.im-tab');
  const panels = {
    profil: document.getElementById('tab-profil'),
    alamat: document.getElementById('tab-alamat'),
    ortu: document.getElementById('tab-ortu'),
    wali: document.getElementById('tab-wali'),
    asalsekolah: document.getElementById('tab-asalsekolah'),
    asalpt: document.getElementById('tab-asalpt')
  };
  
  // Toast & Loading
  const toast = document.getElementById('toast');
  const toastCard = document.getElementById('toastCard');
  const toastTitle = document.getElementById('toastTitle');
  const toastMsg = document.getElementById('toastMsg');
  const loading = document.getElementById('loading');
  let toastTimer = null;
  
  // Modal Opsi
  const modal = document.getElementById('opsiModal');
  const modalTitle = document.getElementById('opsiModalTitle');
  const btnCloseModal = document.getElementById('btnCloseOpsiModal');
  const opsiList = document.getElementById('opsiList');
  const opsiNamaInput = document.getElementById('opsiNamaInput');
  const opsiEditId = document.getElementById('opsiEditId');
  const btnSaveOpsi = document.getElementById('btnSaveOpsi');
  const btnResetOpsi = document.getElementById('btnResetOpsi');
  const pickers = document.querySelectorAll('[data-picker="1"]');
  const wrapKodeRef = document.getElementById('wrapKodeRef');
  const wrapKodeNim = document.getElementById('wrapKodeNim');
  const opsiKodeRefInput = document.getElementById('opsiKodeRefInput');
  const opsiKodeNimInput = document.getElementById('opsiKodeNimInput');
  
  let currentGroup = '', currentTarget = '', currentPickerButton = null;
  
  // Form
  const form = document.getElementById('imForm');
  const btnGetNim = document.getElementById('btnGetNim');
  const inpNim = document.getElementById('nim');
  const inpProdi = document.getElementById('program_studi');
  const inpTgl = document.getElementById('tanggal_registrasi');
  
  const OPTIONAL = new Set(['penerima_kps','no_kps','nama_wali','tanggal_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali','sks_diakui','asal_perguruan_tinggi','asal_program_studi']);
  
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
  
  // ===== Toast Functions =====
  function showToast(type, title, msg){
    if(!toast) return;
    toastCard.classList.remove('ok','err');
    if(type === 'success') toastCard.classList.add('ok'); else toastCard.classList.add('err');
    toastTitle.textContent = title || 'Info';
    toastMsg.textContent = msg || '';
    toast.setAttribute('aria-hidden','false');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.setAttribute('aria-hidden','true'), 3200);
  }
  
  // ===== Loading Functions =====
  function openLoading(){ if(loading) loading.setAttribute('aria-hidden','false'); }
  function closeLoading(){ if(loading) loading.setAttribute('aria-hidden','true'); }
  
  // ===== Tab Functions =====
  function openTab(key){
    tabs.forEach(t => {
      const active = t.dataset.tab === key;
      t.classList.toggle('active', active);
      t.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    Object.keys(panels).forEach(k => { if(panels[k]) panels[k].classList.toggle('active', k === key); });
  }
  tabs.forEach(t => t.addEventListener('click', () => openTab(t.dataset.tab)));
  openTab('profil');
  
  // ===== Validation =====
  function getLabel(el){
    const wrap = el.closest('.im-field');
    const lbl = wrap ? wrap.querySelector('label') : null;
    return lbl ? lbl.textContent.replace('*','').trim() : (el.name || 'Field');
  }
  function findTabKeyFromElement(el){
    const panel = el.closest('.im-panel');
    if(!panel) return 'profil';
    const id = panel.getAttribute('id') || '';
    const map = {'tab-profil':'profil','tab-alamat':'alamat','tab-ortu':'ortu','tab-wali':'wali','tab-asalsekolah':'asalsekolah','tab-asalpt':'asalpt'};
    return map[id] || 'profil';
  }
  function setTabErrorMarks(missingTabKeys){
    tabs.forEach(t => t.classList.remove('has-err'));
    missingTabKeys.forEach(key => { const btn = Array.from(tabs).find(x => x.dataset.tab === key); if(btn) btn.classList.add('has-err'); });
  }
  function validateAllRequired(f){
    const elements = Array.from(f.querySelectorAll('input, select, textarea'));
    const missingTabs = new Set(); let firstEmpty = null;
    for(const el of elements){
      if(!el.name || el.disabled || OPTIONAL.has(el.name)) continue;
      const type = (el.getAttribute('type')||'').toLowerCase();
      if(['button','submit','reset'].includes(type)) continue;
      const val = (el.value||'').trim();
      if(val === ''){ if(!firstEmpty) firstEmpty = el; const tabKey = findTabKeyFromElement(el); if(tabKey) missingTabs.add(tabKey); }
    }
    return { firstEmpty, missingTabs: Array.from(missingTabs) };
  }
  
  // ===== NIM Generator =====
  async function getNim(){
    const prodi = (inpProdi?.value||'').trim();
    const tgl = (inpTgl?.value||'').trim();
    if(!tgl){ showToast('error','Gagal','Tanggal Registrasi wajib diisi.'); inpTgl?.focus(); return; }
    if(!prodi){ showToast('error','Gagal','Program Studi wajib dipilih.'); return; }
    openLoading();
    const startTime = Date.now();
    try{
      const res = await fetch('proses_nim.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({ program_studi: prodi, tanggal_registrasi: tgl })
      });
      const data = await res.json().catch(()=>null);
      const elapsed = Date.now() - startTime;
      setTimeout(() => closeLoading(), Math.max(1200 - elapsed, 0));
      if(!data || !data.ok){ showToast('error','Gagal', data?.message || 'Respon tidak valid.'); return; }
      inpNim.value = data.nim || '';
      showToast('success','Berhasil','NIM berhasil dibuat.');
      inpNim.focus(); inpNim.select();
    }catch(err){ closeLoading(); showToast('error','Gagal','Tidak bisa terhubung ke server.'); }
  }
  if(btnGetNim) btnGetNim.addEventListener('click', getNim);
  
  // ===== Modal Opsi Functions =====
  function escapeHtml(str){ return String(str||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;'); }
  
  function syncPickerText(targetId){
    const hidden = document.getElementById(targetId), text = document.getElementById(targetId+'_text');
    if(!hidden || !text) return;
    const val = (hidden.value||'').trim();
    if(val===''){ text.textContent=''; text.classList.add('is-empty'); }
    else { text.textContent=val; text.classList.remove('is-empty'); }
  }
  ['periode_pendaftaran','jenis_pendaftaran','jalur_pendaftaran','program_studi','kelas'].forEach(syncPickerText);
  
  function isProgramStudiGroup(){ return currentGroup === 'program_studi'; }
  function toggleProgramStudiFields(show){ if(wrapKodeRef) wrapKodeRef.hidden = !show; if(wrapKodeNim) wrapKodeNim.hidden = !show; }
  
  function openModal(){ if(!modal) return; modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; setTimeout(()=> { if(opsiNamaInput) opsiNamaInput.focus(); }, 80); }
  function closeModal(){ if(!modal) return; modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; if(currentPickerButton) currentPickerButton.classList.remove('is-open'); currentPickerButton=null; resetFormOpsi(); }
  function resetFormOpsi(){ if(opsiEditId) opsiEditId.value=''; if(opsiNamaInput) opsiNamaInput.value=''; if(opsiKodeRefInput) opsiKodeRefInput.value=''; if(opsiKodeNimInput) opsiKodeNimInput.value=''; if(btnSaveOpsi) btnSaveOpsi.textContent='Create'; toggleProgramStudiFields(isProgramStudiGroup()); }
  
  function renderOpsiList(items){
    if(!opsiList) return;
    const list = Array.isArray(items) ? items : [];
    if(!list.length){ opsiList.innerHTML = '<div class="im-option-empty">Belum ada opsi.</div>'; return; }
    opsiList.innerHTML = list.map(item => {
      const meta = (item.group==='program_studi') 
        ? `<div class="im-option-meta">Kode: ${escapeHtml(item.kode_ref||'')} · NIM: ${escapeHtml(item.kode_nim||'')}</div><div class="im-option-meta">Klik untuk memilih</div>` 
        : '<div class="im-option-meta">Klik untuk memilih</div>';
      return `<div class="im-option-item"><div class="im-option-text" data-action="pick" data-value="${escapeHtml(item.value)}"><div class="im-option-name">${escapeHtml(item.value)}</div>${meta}</div><div class="im-option-actions"><button type="button" class="im-option-btn edit" data-action="edit" data-id="${escapeHtml(item.id)}" data-value="${escapeHtml(item.value)}" data-kode-ref="${escapeHtml(item.kode_ref||'')}" data-kode-nim="${escapeHtml(item.kode_nim||'')}">Edit</button><button type="button" class="im-option-btn delete" data-action="delete" data-id="${escapeHtml(item.id)}" data-value="${escapeHtml(item.value)}">Delete</button></div></div>`;
    }).join('');
  }
  
  // Load options dari window.__DROPDOWN_OPTIONS__ (tanpa API)
  function loadOptions(group){
    const opts = window.__DROPDOWN_OPTIONS__ || {};
    const items = (opts[group] || []).map((val, idx) => ({
      id: idx + 1,
      value: val,
      group: group,
      kode_ref: group === 'program_studi' ? (val.includes('Teknik Informatika') ? 'FTI' : 'FTI') : null,
      kode_nim: group === 'program_studi' ? (val.includes('Teknik Informatika') ? '55202' : '57201') : null
    }));
    renderOpsiList(items);
    return true;
  }
  
  // Picker button click
  pickers.forEach(btn => {
    btn.addEventListener('click', () => {
      currentGroup = btn.dataset.group||''; currentTarget = btn.dataset.target||''; currentPickerButton = btn;
      if(modalTitle) modalTitle.textContent = btn.dataset.title||'Kelola Opsi'; resetFormOpsi();
      document.querySelectorAll('.im-picker').forEach(x=>x.classList.remove('is-open'));
      if(currentPickerButton) currentPickerButton.classList.add('is-open');
      loadOptions(currentGroup);
      openModal();
    });
  });
  
  // Close modal
  if(btnCloseModal) btnCloseModal.addEventListener('click', closeModal);
  if(modal) modal.addEventListener('click', e => { if(e.target?.dataset?.closeModal==='1') closeModal(); });
  document.addEventListener('keydown', e => { if(e.key==='Escape' && modal?.getAttribute('aria-hidden')==='false') closeModal(); });
  if(btnResetOpsi) btnResetOpsi.addEventListener('click', resetFormOpsi);
  
  // Save/Create button (disabled for akademik - read only)
  if(btnSaveOpsi){
    btnSaveOpsi.addEventListener('click', () => {
      showToast('info','Info','Pengelolaan opsi hanya tersedia untuk Admin.');
    });
  }
  
  // Option list click (pick only)
  if(opsiList){
    opsiList.addEventListener('click', e => {
      const pick = e.target.closest('[data-action="pick"]');
      if(pick){
        const value = (pick.dataset.value||'').trim();
        const hidden = document.getElementById(currentTarget);
        if(hidden){ hidden.value = value; syncPickerText(currentTarget); }
        closeModal();
        return;
      }
      // Edit/Delete disabled for akademik
      const btn = e.target.closest('button[data-action]');
      if(btn){ showToast('info','Info','Edit/Delete hanya tersedia untuk Admin.'); }
    });
  }
  
  // ===== Form Submit =====
  if(form){
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const { firstEmpty, missingTabs } = validateAllRequired(form);
      setTabErrorMarks(missingTabs);
      if(firstEmpty){
        const label = getLabel(firstEmpty);
        const tabKey = findTabKeyFromElement(firstEmpty);
        if(tabKey) openTab(tabKey);
        showToast('error','Gagal',`Field "${label}" wajib diisi.`);
        setTimeout(()=> { if(firstEmpty) firstEmpty.focus(); }, 80);
        return;
      }
      setTabErrorMarks([]);
      openLoading();
      try{
        const formData = new FormData(form);
        const res = await fetch(form.action, { method:'POST', body: formData });
        const data = await res.json().catch(()=>null);
        closeLoading();
        if(data && data.ok){
          let msg = data.message||'Data mahasiswa dan akun berhasil dibuat!';
          if(data.password_temp){ msg += `\n\nUsername: ${data.username}\nPassword: ${data.password_temp}\n\nSIMPAN PASSWORD INI!`; }
          showToast('success','Berhasil', msg);
          setTimeout(()=>{ form.reset(); ['periode_pendaftaran','jenis_pendaftaran','jalur_pendaftaran','program_studi','kelas'].forEach(id=>{ const h=document.getElementById(id),t=document.getElementById(id+'_text'); if(h)h.value=''; if(t){t.textContent='';t.classList.add('is-empty');}}); openTab('profil'); }, 2000);
        }else{ showToast('error','Gagal', data?.message||'Terjadi kesalahan saat menyimpan data.'); }
      }catch(err){ closeLoading(); showToast('error','Gagal','Tidak bisa terhubung ke server.'); }
    });
  }
  
  // ===== Init =====
  applyPersistedCollapse();
  if(menuToggle) menuToggle.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); toggleSidebar(); });
  if(sidebarOverlay) sidebarOverlay.addEventListener('click', closeMobileSidebar);
  document.addEventListener('click', e => { if(isMobile() && body.classList.contains('sidebar-open')){ const inside = sidebar && sidebar.contains(e.target); const toggle = menuToggle && menuToggle.contains(e.target); if(!inside && !toggle) closeMobileSidebar(); }});
  window.addEventListener('resize', () => { if(!isMobile()){ closeMobileSidebar(); applyPersistedCollapse(); } else syncBurgerIcon(); });
  document.addEventListener('keydown', e => { if(e.key==='Escape') closeMobileSidebar(); });
  
  // Flash message
  const ft = window.__FLASH_TIPE__||'', fp = window.__FLASH_PESAN__||'';
  if(ft && fp) showToast(ft==='success'?'success':'error', ft==='success'?'Berhasil':'Gagal', fp);
  
  // Debug
  if(window.location.hostname==='localhost') window.__InputMhsApp = { showToast, toggleSidebar, renderOpsiList, openModal, closeModal };
  
})();