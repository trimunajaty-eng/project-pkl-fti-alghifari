(function(){
  const tabs = document.querySelectorAll('.im-tab');

  const panels = {
    profil: document.getElementById('tab-profil'),
    alamat: document.getElementById('tab-alamat'),
    ortu: document.getElementById('tab-ortu'),
    wali: document.getElementById('tab-wali'),
    asalsekolah: document.getElementById('tab-asalsekolah'),
    asalpt: document.getElementById('tab-asalpt'),
  };

  function openTab(key){
    tabs.forEach(t => {
      const active = t.dataset.tab === key;
      t.classList.toggle('active', active);
      t.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    Object.keys(panels).forEach(k => {
      if (panels[k]) panels[k].classList.toggle('active', k === key);
    });
  }

  tabs.forEach(t => t.addEventListener('click', () => openTab(t.dataset.tab)));
  openTab('profil');

  const toast = document.getElementById("toast");
  const toastCard = document.getElementById("toastCard");
  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");
  let toastTimer = null;

  function showToast(type, title, msg){
    if (!toast) return;
    toastCard.classList.remove("ok","err");
    if (type === "success") toastCard.classList.add("ok");
    else toastCard.classList.add("err");

    toastTitle.textContent = title || "Info";
    toastMsg.textContent = msg || "";
    toast.setAttribute("aria-hidden","false");

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toast.setAttribute("aria-hidden","true");
    }, 3200);
  }

  const ft = window.__FLASH_TIPE__ || "";
  const fp = window.__FLASH_PESAN__ || "";
  if (ft && fp){
    showToast(ft === "success" ? "success" : "error", ft === "success" ? "Berhasil" : "Gagal", fp);
  }

  const loading = document.getElementById("loading");
  function openLoading(){ loading?.setAttribute("aria-hidden","false"); }
  function closeLoading(){ loading?.setAttribute("aria-hidden","true"); }

  const OPTIONAL = new Set([
    'penerima_kps','no_kps',
    'nama_wali','tanggal_lahir_wali','pendidikan_wali','pekerjaan_wali','penghasilan_wali',
    'sks_diakui','asal_perguruan_tinggi','asal_program_studi',
  ]);

  function getLabel(el){
    const wrap = el.closest('.im-field');
    const lbl = wrap ? wrap.querySelector('label') : null;
    return lbl ? (lbl.textContent || "").replace('*','').trim() : (el.name || "Field");
  }

  function findTabKeyFromElement(el){
    const panel = el.closest('.im-panel');
    if (!panel) {
      const wrap = el.closest('.im-field');
      if (wrap && wrap.closest('.panel-body')) return 'profil';
      return null;
    }
    const id = panel.getAttribute('id') || '';
    if (id === 'tab-profil') return 'profil';
    if (id === 'tab-alamat') return 'alamat';
    if (id === 'tab-ortu') return 'ortu';
    if (id === 'tab-wali') return 'wali';
    if (id === 'tab-asalsekolah') return 'asalsekolah';
    if (id === 'tab-asalpt') return 'asalpt';
    return null;
  }

  function setTabErrorMarks(missingTabKeys){
    tabs.forEach(t => t.classList.remove('has-err'));
    missingTabKeys.forEach(key => {
      const btn = Array.from(tabs).find(x => x.dataset.tab === key);
      if (btn) btn.classList.add('has-err');
    });
  }

  function validateAllRequired(form){
    const elements = Array.from(form.querySelectorAll('input, select, textarea'));
    const missingTabs = new Set();
    let firstEmpty = null;

    for (const el of elements){
      if (!el.name) continue;
      if (el.disabled) continue;
      if (OPTIONAL.has(el.name)) continue;

      const type = (el.getAttribute('type') || '').toLowerCase();
      if (type === 'button' || type === 'submit' || type === 'reset') continue;

      const tag = el.tagName.toLowerCase();
      let val = (el.value || '').trim();

      if (type === 'number'){
        if (val === ''){
          if (!firstEmpty) firstEmpty = el;
          const tabKey = findTabKeyFromElement(el);
          if (tabKey) missingTabs.add(tabKey);
        }
        continue;
      }

      if (tag === 'select'){
        if (val === ''){
          if (!firstEmpty) firstEmpty = el;
          const tabKey = findTabKeyFromElement(el);
          if (tabKey) missingTabs.add(tabKey);
        }
        continue;
      }

      if (val === ''){
        if (!firstEmpty) firstEmpty = el;
        const tabKey = findTabKeyFromElement(el);
        if (tabKey) missingTabs.add(tabKey);
      }
    }

    return { firstEmpty, missingTabs: Array.from(missingTabs) };
  }

  const btnGetNim = document.getElementById("btnGetNim");
  const inpNim = document.getElementById("nim");
  const inpProdi = document.getElementById("program_studi");
  const inpTgl = document.getElementById("tanggal_registrasi");

  async function getNim(){
    const prodi = (inpProdi?.value || "").trim();
    const tgl = (inpTgl?.value || "").trim();

    if (!tgl){
      showToast("error","Gagal","Tanggal Registrasi wajib diisi.");
      inpTgl?.focus();
      return;
    }
    if (!prodi){
      showToast("error","Gagal","Program Studi wajib dipilih.");
      return;
    }

    openLoading();
    const startTime = Date.now();

    try{
      const res = await fetch("proses_nim.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: new URLSearchParams({ program_studi: prodi, tanggal_registrasi: tgl })
      });

      const data = await res.json().catch(() => null);
      const elapsed = Date.now() - startTime;
      const minDuration = 1200;

      setTimeout(() => closeLoading(), Math.max(minDuration - elapsed, 0));

      if (!data || !data.ok){
        showToast("error","Gagal", (data && data.message) ? data.message : "Respon tidak valid.");
        return;
      }

      inpNim.value = data.nim || "";
      showToast("success","Berhasil","NIM berhasil dibuat.");
      inpNim.focus();
      inpNim.select();

    }catch(err){
      closeLoading();
      showToast("error","Gagal","Tidak bisa terhubung ke server.");
    }
  }

  btnGetNim?.addEventListener("click", getNim);

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

  let currentGroup = '';
  let currentTarget = '';
  let currentTitle = '';
  let currentItems = [];
  let currentPickerButton = null;

  function escapeHtml(str){
    return String(str || '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function syncPickerText(targetId){
    const hidden = document.getElementById(targetId);
    const text = document.getElementById(targetId + '_text');
    if (!hidden || !text) return;

    const val = (hidden.value || '').trim();
    if (val === ''){
      text.textContent = '';
      text.classList.add('is-empty');
    } else {
      text.textContent = val;
      text.classList.remove('is-empty');
    }
  }

  ['periode_pendaftaran','jenis_pendaftaran','jalur_pendaftaran','program_studi','kelas'].forEach(syncPickerText);

  function isProgramStudiGroup(){
    return currentGroup === 'program_studi';
  }

  function toggleProgramStudiFields(show){
    if (wrapKodeRef) wrapKodeRef.hidden = !show;
    if (wrapKodeNim) wrapKodeNim.hidden = !show;
  }

  function openModal(){
    if (!modal) return;
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    setTimeout(() => opsiNamaInput?.focus(), 80);
  }

  function closeModal(){
    if (!modal) return;
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    currentPickerButton?.classList.remove('is-open');
    currentPickerButton = null;
    resetFormOpsi();
  }

  function resetFormOpsi(){
    if (opsiEditId) opsiEditId.value = '';
    if (opsiNamaInput) opsiNamaInput.value = '';
    if (opsiKodeRefInput) opsiKodeRefInput.value = '';
    if (opsiKodeNimInput) opsiKodeNimInput.value = '';
    if (btnSaveOpsi) btnSaveOpsi.textContent = 'Create';
    toggleProgramStudiFields(isProgramStudiGroup());
  }

  function renderOpsiList(items){
    currentItems = Array.isArray(items) ? items : [];

    if (!opsiList) return;

    if (!currentItems.length){
      opsiList.innerHTML = `<div class="im-option-empty">Belum ada opsi.</div>`;
      return;
    }

    opsiList.innerHTML = currentItems.map(item => {
      const meta = (item.group === 'program_studi')
        ? `
          <div class="im-option-meta">
            Kode Awalan: ${escapeHtml(item.kode_ref || '')} · Kode NIM: ${escapeHtml(item.kode_nim || '')}
          </div>
          <div class="im-option-meta">Klik nama untuk memilih</div>
        `
        : `<div class="im-option-meta">Klik nama untuk memilih</div>`;

      return `
        <div class="im-option-item">
          <div
            class="im-option-text"
            data-action="pick"
            data-value="${escapeHtml(item.value)}">
            <div class="im-option-name">${escapeHtml(item.value)}</div>
            ${meta}
          </div>
          <div class="im-option-actions">
            <button
              type="button"
              class="im-option-btn edit"
              data-action="edit"
              data-id="${escapeHtml(item.id)}"
              data-value="${escapeHtml(item.value)}"
              data-kode-ref="${escapeHtml(item.kode_ref || '')}"
              data-kode-nim="${escapeHtml(item.kode_nim || '')}">Edit</button>
            <button
              type="button"
              class="im-option-btn delete"
              data-action="delete"
              data-id="${escapeHtml(item.id)}"
              data-value="${escapeHtml(item.value)}">Delete</button>
          </div>
        </div>
      `;
    }).join('');
  }

  async function loadOptions(group){
    try{
      openLoading();
      const res = await fetch(`opsi_dropdown.php?group=${encodeURIComponent(group)}`, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json().catch(() => null);
      closeLoading();

      if (!data || !data.ok){
        showToast('error', 'Gagal', data?.message || 'Gagal memuat opsi.');
        return false;
      }

      renderOpsiList(data.items || []);
      return true;
    }catch(err){
      closeLoading();
      showToast('error', 'Gagal', 'Tidak bisa terhubung ke server.');
      return false;
    }
  }

  pickers.forEach(btn => {
    btn.addEventListener('click', async () => {
      currentGroup = btn.dataset.group || '';
      currentTarget = btn.dataset.target || '';
      currentTitle = btn.dataset.title || 'Kelola Opsi';
      currentPickerButton = btn;

      document.querySelectorAll('.im-picker').forEach(x => x.classList.remove('is-open'));
      currentPickerButton.classList.add('is-open');

      modalTitle.textContent = currentTitle;
      resetFormOpsi();

      const ok = await loadOptions(currentGroup);
      if (!ok) {
        currentPickerButton?.classList.remove('is-open');
        return;
      }
      openModal();
    });
  });

  btnCloseModal?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => {
    const el = e.target;
    if (el && el.dataset && el.dataset.closeModal === '1') {
      closeModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal?.getAttribute('aria-hidden') === 'false') {
      closeModal();
    }
  });

  btnResetOpsi?.addEventListener('click', resetFormOpsi);

  btnSaveOpsi?.addEventListener('click', async () => {
    const nama = (opsiNamaInput?.value || '').trim();
    const editId = (opsiEditId?.value || '').trim();
    const kodeRef = (opsiKodeRefInput?.value || '').trim().toUpperCase();
    const kodeNim = (opsiKodeNimInput?.value || '').trim();

    if (!currentGroup){
      showToast('error', 'Gagal', 'Grup dropdown tidak ditemukan.');
      return;
    }

    if (!nama){
      showToast('error', 'Gagal', 'Nama opsi wajib diisi.');
      opsiNamaInput?.focus();
      return;
    }

    if (isProgramStudiGroup()){
      if (!kodeRef){
        showToast('error', 'Gagal', 'Kode awalan huruf wajib diisi.');
        opsiKodeRefInput?.focus();
        return;
      }
      if (!kodeNim){
        showToast('error', 'Gagal', 'Kode NIM angka wajib diisi.');
        opsiKodeNimInput?.focus();
        return;
      }
    }

    try{
      openLoading();

      const payload = new URLSearchParams();
      payload.set('action', editId ? 'update' : 'create');
      payload.set('group', currentGroup);
      payload.set('value', nama);
      if (editId) payload.set('id', editId);
      if (isProgramStudiGroup()){
        payload.set('kode_ref', kodeRef);
        payload.set('kode_nim', kodeNim);
      }

      const res = await fetch('opsi_dropdown.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: payload.toString()
      });

      const data = await res.json().catch(() => null);
      closeLoading();

      if (!data || !data.ok){
        showToast('error', 'Gagal', data?.message || 'Proses gagal.');
        return;
      }

      resetFormOpsi();
      renderOpsiList(data.items || []);
      showToast('success', 'Berhasil', data.message || 'Opsi berhasil disimpan.');
    }catch(err){
      closeLoading();
      showToast('error', 'Gagal', 'Tidak bisa terhubung ke server.');
    }
  });

  opsiList?.addEventListener('click', async (e) => {
    const pick = e.target.closest('[data-action="pick"]');
    if (pick){
      const value = (pick.dataset.value || '').trim();
      const hidden = document.getElementById(currentTarget);

      if (hidden){
        hidden.value = value;
        syncPickerText(currentTarget);
      }

      const item = pick.closest('.im-option-item');
      if (item){
        item.style.transform = 'scale(.985)';
        item.style.transition = 'transform .14s ease, box-shadow .16s ease';
      }

      setTimeout(() => {
        closeModal();
      }, 120);

      return;
    }

    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const action = btn.dataset.action || '';
    const id = btn.dataset.id || '';
    const value = btn.dataset.value || '';
    const kodeRef = btn.dataset.kodeRef || '';
    const kodeNim = btn.dataset.kodeNim || '';

    if (action === 'edit') {
      opsiEditId.value = id;
      opsiNamaInput.value = value;
      if (isProgramStudiGroup()){
        opsiKodeRefInput.value = kodeRef;
        opsiKodeNimInput.value = kodeNim;
      }
      btnSaveOpsi.textContent = 'Update';
      opsiNamaInput.focus();
      opsiNamaInput.select();
      return;
    }

    if (action === 'delete') {
      const ok = window.confirm(`Hapus opsi "${value}"?`);
      if (!ok) return;

      try{
        openLoading();

        const payload = new URLSearchParams();
        payload.set('action', 'delete');
        payload.set('group', currentGroup);
        payload.set('id', id);

        const res = await fetch('opsi_dropdown.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: payload.toString()
        });

        const data = await res.json().catch(() => null);
        closeLoading();

        if (!data || !data.ok){
          showToast('error', 'Gagal', data?.message || 'Gagal menghapus opsi.');
          return;
        }

        const hidden = document.getElementById(currentTarget);
        if (hidden && hidden.value === value){
          hidden.value = '';
          syncPickerText(currentTarget);
        }

        resetFormOpsi();
        renderOpsiList(data.items || []);
        showToast('success', 'Berhasil', data.message || 'Opsi berhasil dihapus.');
      }catch(err){
        closeLoading();
        showToast('error', 'Gagal', 'Tidak bisa terhubung ke server.');
      }
    }
  });

  const form = document.getElementById("imForm");
  if (form){
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const { firstEmpty, missingTabs } = validateAllRequired(form);
      setTabErrorMarks(missingTabs);

      if (firstEmpty){
        const label = getLabel(firstEmpty);
        const tabKey = findTabKeyFromElement(firstEmpty);
        if (tabKey) openTab(tabKey);
        showToast("error","Gagal", `Field "${label}" wajib diisi.`);
        setTimeout(() => firstEmpty.focus(), 80);
        return;
      }

      setTabErrorMarks([]);
      openLoading();
      setTimeout(() => form.submit(), 900);
    });
  }
})();