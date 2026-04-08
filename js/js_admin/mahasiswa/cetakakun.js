(function(){
  const q = (sel) => document.querySelector(sel);

  // Toast
  const toast = q("#toast");
  const toastCard = q("#toastCard");
  const toastTitle = q("#toastTitle");
  const toastMsg = q("#toastMsg");
  let toastTimer = null;

  function showToast(type, title, msg){
    if (!toast) return;
    toastCard.classList.remove("ok","err");
    if (type === "success") toastCard.classList.add("ok");
    if (type === "error") toastCard.classList.add("err");

    toastTitle.textContent = title || "Info";
    toastMsg.textContent = msg || "";

    toast.classList.add("open");
    toast.setAttribute("aria-hidden","false");

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toast.classList.remove("open");
      toast.setAttribute("aria-hidden","true");
    }, 3200);
  }

  // Flash dari URL
  const ft = window.__FLASH_TIPE__ || "";
  const fp = window.__FLASH_PESAN__ || "";
  if (ft && fp){
    showToast(ft === "success" ? "success" : "error", ft === "success" ? "Berhasil" : "Gagal", fp);
  }

  // Loading
  const loading = q("#loading");
  const loadingText = q("#loadingText");
  function openLoading(text){
    if (loadingText && text) loadingText.textContent = text;
    loading?.classList.add("open");
    loading?.setAttribute("aria-hidden","false");
  }
  function closeLoading(){
    loading?.classList.remove("open");
    loading?.setAttribute("aria-hidden","true");
  }

  // Modal confirm
  const modal = q("#confirmModal");
  const confirmMsg = q("#confirmMsg");
  const btnCancel = q("#btnCancelConfirm");
  const btnOk = q("#btnOkConfirm");

  let confirmHandler = null;

  function openConfirm(message, onOk){
    confirmMsg.textContent = message || "Anda yakin?";
    confirmHandler = onOk || null;
    modal.classList.add("open");
    modal.setAttribute("aria-hidden","false");
  }
  function closeConfirm(){
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden","true");
    confirmHandler = null;
  }

  btnCancel?.addEventListener("click", closeConfirm);
  modal?.addEventListener("click", (e) => {
    if (e.target === modal) closeConfirm();
  });
  btnOk?.addEventListener("click", () => {
    if (typeof confirmHandler === "function") confirmHandler();
    closeConfirm();
  });

  // API helper
  async function api(payload){
    const res = await fetch("proses_cetakakun.php", {
      method: "POST",
      headers: {"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},
      body: new URLSearchParams(payload)
    });
    return await res.json().catch(()=>null);
  }

  // =========================
  // COUNTS state
  // =========================
  let remaining = { total: 0, by: {} }; // belum dicetak
  let made = { total: 0, by: {} };      // sudah dicetak

  function initFromServerData(data){
    remaining.total = parseInt(data?.remaining_total ?? 0, 10) || 0;
    remaining.by = data?.remaining_by_prodi || {};

    made.total = parseInt(data?.made_total ?? 0, 10) || 0;
    made.by = data?.made_by_prodi || {};
  }

  // =========================
  // Single search
  // =========================
  const caQuery = q("#caQuery");
  const caSuggest = q("#caSuggest");
  const caPicked = q("#caPicked");
  const pickedNim = q("#pickedNim");
  const pickedNama = q("#pickedNama");
  const pickedProdi = q("#pickedProdi");
  const btnCetakSingle = q("#btnCetakSingle");

  let selected = null; // {id,nim,nama,prodi}
  let tmr = null;

  function setSelected(item){
    selected = item;
    if (!item){
      caPicked?.classList.remove("show");
      caPicked?.setAttribute("aria-hidden","true");
      btnCetakSingle.disabled = true;
      return;
    }
    pickedNim.textContent = item.nim || "-";
    pickedNama.textContent = item.nama || "-";
    pickedProdi.textContent = item.prodi || "-";

    caPicked?.classList.add("show");
    caPicked?.setAttribute("aria-hidden","false");
    btnCetakSingle.disabled = false;
  }

  function closeSuggest(){
    caSuggest?.classList.remove("open");
    caSuggest?.setAttribute("aria-hidden","true");
    if (caSuggest) caSuggest.innerHTML = "";
  }

  function renderSuggest(items){
    if (!caSuggest) return;
    if (!items || items.length === 0){
      closeSuggest();
      return;
    }

    caSuggest.innerHTML = items.map(it => {
      const nim = (it.nim || "").replace(/"/g,'&quot;');
      const nama = (it.nama || "").replace(/"/g,'&quot;');
      const prodi = (it.prodi || "").replace(/"/g,'&quot;');
      return `
        <div class="ca-s-item" data-id="${it.id}" data-nim="${nim}" data-nama="${nama}" data-prodi="${prodi}">
          <div class="ca-s-top">
            <div class="ca-s-nim">${it.nim || "-"}</div>
            <div class="ca-s-name">${it.nama || "-"}</div>
          </div>
          <div class="ca-s-prodi">${it.prodi || "-"}</div>
        </div>
      `;
    }).join("");

    caSuggest.classList.add("open");
    caSuggest.setAttribute("aria-hidden","false");
  }

  caSuggest?.addEventListener("click", (e) => {
    const item = e.target.closest(".ca-s-item");
    if (!item) return;

    const it = {
      id: parseInt(item.dataset.id || "0", 10),
      nim: item.dataset.nim || "",
      nama: item.dataset.nama || "",
      prodi: item.dataset.prodi || ""
    };
    setSelected(it);
    caQuery.value = `${it.nim} - ${it.nama}`;
    closeSuggest();
  });

  document.addEventListener("click", (e) => {
    if (!caSuggest) return;
    const wrap = e.target.closest(".ca-search");
    if (!wrap) closeSuggest();
  });

  caQuery?.addEventListener("input", () => {
    const val = (caQuery.value || "").trim();
    setSelected(null);

    clearTimeout(tmr);
    if (val.length < 2){
      closeSuggest();
      return;
    }

    tmr = setTimeout(async () => {
      const data = await api({action:"search", q: val});
      if (!data || !data.ok){
        closeSuggest();
        return;
      }
      renderSuggest(data.items || []);
    }, 220);
  });

  // Single print action
  btnCetakSingle?.addEventListener("click", () => {
    if (!selected || !selected.id) return;

    openConfirm(`Anda yakin cetak akun: ${selected.nama}?`, async () => {
      openLoading("Sedang mencetak akun...");
      try{
        const data = await api({action:"single", id: String(selected.id)});
        closeLoading();

        if (!data || !data.ok){
          showToast("error","Gagal", (data && data.message) ? data.message : "Respon tidak valid.");
          return;
        }

        showToast("success","Berhasil", data.message || `Berhasil cetak akun: ${selected.nama}`);

        caQuery.value = "";
        setSelected(null);
        closeSuggest();
        await refreshCounts();
      }catch(err){
        closeLoading();
        showToast("error","Gagal","Tidak bisa terhubung ke server.");
      }
    });
  });

  // =========================
  // Massal
  // =========================
  const massProdi = q("#massProdi");
  const massCount = q("#massCount");
  const btnCetakMassal = q("#btnCetakMassal");
  const btnRefreshCount = q("#btnRefreshCount");
  const countAll = q("#countAll");

  function remainingCountFor(prodi){
    if (prodi === "ALL") return remaining.total || 0;
    return parseInt(remaining.by?.[prodi] ?? 0, 10) || 0;
  }

  function updateMassCount(){
    const prodi = (massProdi?.value || "ALL");
    const c = remainingCountFor(prodi);
    if (massCount) massCount.textContent = String(c);
  }

  function renderMadeCountsUI(){
    // update total made
    if (countAll) countAll.textContent = String(made.total ?? 0);

    // hapus item prodi lama (kecuali "Semua")
    const items = document.querySelectorAll(".ca-count-item[data-prodi]");
    items.forEach(el => el.remove());

    // render prodi baru
    const wrap = document.querySelector(".ca-counts");
    if (!wrap) return;

    const master = window.__PRODI_MASTER__ || [];
    const keys = (master.length ? master : Object.keys(made.by || {}));

    keys.forEach(p => {
      const n = parseInt(made.by?.[p] ?? 0, 10) || 0;
      const div = document.createElement("div");
      div.className = "ca-count-item";
      div.setAttribute("data-prodi", p);
      div.innerHTML = `<div class="k">${p} (Akun Dibuat)</div><div class="v">${n}</div>`;
      wrap.appendChild(div);
    });
  }

  async function refreshCounts(){
    const data = await api({action:"counts"});
    if (!data || !data.ok) return;

    initFromServerData(data);
    renderMadeCountsUI();
    updateMassCount();
  }

  massProdi?.addEventListener("change", updateMassCount);

  btnRefreshCount?.addEventListener("click", async () => {
    openLoading("Refresh jumlah...");
    await refreshCounts();
    closeLoading();
    showToast("success","Berhasil","Jumlah data diperbarui.");
  });

  btnCetakMassal?.addEventListener("click", () => {
    const prodi = (massProdi?.value || "ALL");
    const label = (prodi === "ALL") ? "Semua Program Studi" : prodi;
    const c = remainingCountFor(prodi);

    if (c <= 0){
      showToast("error","Gagal","Tidak ada data yang bisa dicetak untuk pilihan ini.");
      return;
    }

    openConfirm(`Anda yakin cetak akun massal: ${label}? (Total: ${c})`, async () => {
      openLoading("Sedang mencetak akun massal...");
      try{
        const data = await api({action:"massal", prodi});
        closeLoading();

        if (!data || !data.ok){
          showToast("error","Gagal", (data && data.message) ? data.message : "Respon tidak valid.");
          return;
        }

        showToast("success","Berhasil", data.message || "Berhasil cetak akun massal.");
        await refreshCounts();

      }catch(err){
        closeLoading();
        showToast("error","Gagal","Tidak bisa terhubung ke server.");
      }
    });
  });

  // init
  (async () => {
    await refreshCounts();
  })();

})();