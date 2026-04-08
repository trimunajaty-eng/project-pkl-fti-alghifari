(function(){
  const q = document.getElementById('q');
  const tbody = document.getElementById('tbody');
  const pager = document.getElementById('pager');
  const totalText = document.getElementById('totalText');

  // Toast
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
    toastTimer = setTimeout(() => toast.setAttribute("aria-hidden","true"), 3200);
  }

  const ft = window.__FLASH_TIPE__ || "";
  const fp = window.__FLASH_PESAN__ || "";
  if (ft && fp){
    showToast(ft === "success" ? "success" : "error", ft === "success" ? "Berhasil" : "Gagal", fp);
  }

  let timer = null;
  function debounce(fn, ms){
    clearTimeout(timer);
    timer = setTimeout(fn, ms);
  }

  async function loadData({page = 1, query = ""}){
    const url = new URL(window.location.href);
    url.searchParams.set('ajax', '1');
    url.searchParams.set('page', String(page));
    url.searchParams.set('q', query);

    try{
      const res = await fetch(url.toString(), { headers: { "X-Requested-With": "fetch" } });
      const data = await res.json().catch(() => null);

      if (!data || !data.ok){
        showToast("error","Gagal", data && data.message ? data.message : "Respon server tidak valid.");
        return;
      }

      tbody.innerHTML = data.tbody || "";
      pager.innerHTML = data.pager || "";
      if (totalText) totalText.textContent = String(data.total ?? "");

      // update URL (tanpa reload)
      const url2 = new URL(window.location.href);
      url2.searchParams.set('page', String(data.page || page));
      if (query) url2.searchParams.set('q', query);
      else url2.searchParams.delete('q');
      url2.searchParams.delete('ajax');
      history.replaceState({}, "", url2.toString());

    } catch (e){
      showToast("error","Gagal","Tidak bisa terhubung ke server.");
    }
  }

  // typing search (langsung bergerak)
  if (q){
    q.addEventListener('input', () => {
      const val = q.value.trim();
      debounce(() => loadData({page: 1, query: val}), 250);
    });
  }

  // pagination click (delegation)
  document.addEventListener('click', (ev) => {
    const a = ev.target.closest('a.pg-btn');
    if (!a) return;

    // kalau disabled / #
    const p = parseInt(a.getAttribute('data-page') || "0", 10);
    if (!p || a.classList.contains('disabled')) {
      ev.preventDefault();
      return;
    }

    ev.preventDefault();
    const val = (q?.value || "").trim();
    loadData({page: p, query: val});
  });

  // konfirmasi hapus (placeholder)
  document.addEventListener('click', (ev) => {
    const del = ev.target.closest('a[data-del="1"]');
    if (!del) return;

    const name = del.getAttribute('data-name') || 'data ini';
    const ok = confirm(`Yakin ingin menghapus ${name}?`);
    if (!ok) {
      ev.preventDefault();
    }
  });
})();