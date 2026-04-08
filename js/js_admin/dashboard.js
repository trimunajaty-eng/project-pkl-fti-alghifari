(() => {
  if (window.__ADMIN_SIMPLE_DASH__) return;
  window.__ADMIN_SIMPLE_DASH__ = true;

  const app = document.getElementById("app");
  const btnBurger = document.getElementById("btnBurger");
  const overlay = document.getElementById("overlay");
  const nav = document.getElementById("nav");
  const flyout = document.getElementById("flyout");

  // profile
  const profileWrap = document.getElementById("profileWrap");
  const btnProfile = document.getElementById("btnProfile");
  const profileDrop = document.getElementById("profileDrop");

  if (!app) return;

  const KEY_COLLAPSE = "admin_sidebar_collapsed";

  const isMobile = () => window.matchMedia("(max-width: 768px)").matches;

  // =========================
  // Persisted collapse (DESKTOP)
  // =========================
  function applyPersistedCollapse() {
    // di mobile jangan paksa collapse, tapi statusnya tetap disimpan
    if (isMobile()) return;

    const saved = localStorage.getItem(KEY_COLLAPSE);
    if (saved === "1") app.classList.add("collapsed");
    else app.classList.remove("collapsed");
  }

  function saveCollapseState() {
    const v = app.classList.contains("collapsed") ? "1" : "0";
    localStorage.setItem(KEY_COLLAPSE, v);
  }

  // terapkan saat awal load
  applyPersistedCollapse();

  // =========================
  // Mobile: open/close sidebar
  // =========================
  function closeMobile(){ app.classList.remove("mobile-open"); }
  function toggleMobile(){ app.classList.toggle("mobile-open"); }

  // Desktop collapse
  function toggleDesktopCollapse(){
    app.classList.toggle("collapsed");
    saveCollapseState();
    hideFlyout();
  }

  if (btnBurger) {
    btnBurger.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (isMobile()) toggleMobile();
      else toggleDesktopCollapse();
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => closeMobile());
  }

  function hideFlyout(){
    if (!flyout) return;
    flyout.classList.remove("open");
    flyout.setAttribute("aria-hidden", "true");
    flyout.dataset.owner = "";
  }

  function escapeHtml(str){
    return String(str)
      .replaceAll("&","&amp;")
      .replaceAll("<","&lt;")
      .replaceAll(">","&gt;")
      .replaceAll('"',"&quot;")
      .replaceAll("'","&#039;");
  }

  function buildFlyoutHtml(btn, sub){
    const title = (btn.querySelector(".tx")?.textContent || btn.textContent || "Menu").trim();
    let html = `<div class="fly-title">${escapeHtml(title)}</div>`;

    sub.querySelectorAll("a.sub-item").forEach((a) => {
      const href = a.getAttribute("href") || "#";
      const active = a.classList.contains("active") ? "active" : "";
      const label = (a.querySelector(".tx")?.textContent || a.textContent || "").trim();

      html += `<a class="${active}" href="${href}">
                <span class="dot"></span>
                <span class="tx">${escapeHtml(label)}</span>
              </a>`;
    });
    return html;
  }

  function showFlyout(btn){
    if (!flyout) return;

    const sid = btn.getAttribute("data-sub");
    const sub = sid ? document.getElementById(sid) : null;
    if (!sub) return;

    const rect = btn.getBoundingClientRect();
    flyout.style.top = Math.max(12, rect.top - 6) + "px";

    const owner = flyout.dataset.owner || "";
    if (flyout.classList.contains("open") && owner === sid){
      hideFlyout();
      return;
    }

    flyout.dataset.owner = sid;
    flyout.innerHTML = buildFlyoutHtml(btn, sub);
    flyout.classList.add("open");
    flyout.setAttribute("aria-hidden", "false");
  }

  // =========================
  // Submenu toggle (accordion)
  // =========================
  function openSub(sub) {
    sub.classList.add("open");
    sub.style.maxHeight = "0px";
    requestAnimationFrame(() => {
      sub.style.maxHeight = sub.scrollHeight + "px";
    });
  }

  function closeSub(sub) {
    sub.style.maxHeight = sub.scrollHeight + "px";
    requestAnimationFrame(() => {
      sub.style.maxHeight = "0px";
      sub.classList.remove("open");
    });
  }

  function closeAllExcept(keepId) {
    document.querySelectorAll(".nav-item.has-sub[data-sub]").forEach((btn) => {
      const sid = btn.getAttribute("data-sub");
      if (!sid || sid === keepId) return;

      const sub = document.getElementById(sid);
      if (sub && sub.classList.contains("open")) closeSub(sub);

      btn.classList.remove("open");
      btn.setAttribute("aria-expanded", "false");
    });
  }

  function handleToggle(e) {
    const btn = e.target.closest(".nav-item.has-sub[data-sub]");
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const sid = btn.getAttribute("data-sub");
    const sub = sid ? document.getElementById(sid) : null;
    if (!sub) return;

    // DESKTOP + collapsed -> flyout
    if (!isMobile() && app.classList.contains("collapsed")) {
      showFlyout(btn);
      return;
    } else {
      hideFlyout();
    }

    const willOpen = !sub.classList.contains("open");
    closeAllExcept(sid);

    if (willOpen) {
      btn.classList.add("open");
      btn.setAttribute("aria-expanded", "true");
      openSub(sub);
      setTimeout(() => sub.scrollIntoView({ behavior: "smooth", block: "nearest" }), 60);
    } else {
      btn.classList.remove("open");
      btn.setAttribute("aria-expanded", "false");
      closeSub(sub);
    }
  }

  if (nav && !nav.dataset.bound) {
    nav.addEventListener("click", handleToggle);
    nav.dataset.bound = "1";
  }

  // klik submenu mobile -> tutup sidebar
  document.addEventListener("click", (e) => {
    const a = e.target.closest?.(".sub-item");
    if (!a) return;
    if (isMobile()) closeMobile();
  });

  // klik luar flyout -> tutup
  document.addEventListener("click", (e) => {
    if (!flyout || !flyout.classList.contains("open")) return;
    if (flyout.contains(e.target)) return;
    if (e.target.closest?.(".nav-item.has-sub[data-sub]")) return;
    hideFlyout();
  });

  // rapihin maxHeight submenu open dari PHP
  window.addEventListener("load", () => {
    document.querySelectorAll(".sub.open").forEach((sub) => {
      sub.style.maxHeight = sub.scrollHeight + "px";
    });
  });

  // resize rules:
  // - saat pindah ke desktop, apply persisted collapse
  // - saat pindah ke mobile, tutup drawer
  window.addEventListener("resize", () => {
    if (!isMobile()) {
      closeMobile();
      applyPersistedCollapse();
    } else {
      hideFlyout();
    }

    document.querySelectorAll(".sub.open").forEach((sub) => {
      sub.style.maxHeight = sub.scrollHeight + "px";
    });
  });

  // Bell placeholder
  const bell = document.getElementById("btnBell");
  if (bell) {
    bell.addEventListener("click", () => alert("Halaman pesan belum tersedia (placeholder)."));
  }

  // =========================
  // Profile dropdown
  // =========================
  function openProfile() {
    if (!profileWrap) return;
    profileWrap.classList.add("open");
    profileDrop?.setAttribute("aria-hidden", "false");
  }
  function closeProfile() {
    if (!profileWrap) return;
    profileWrap.classList.remove("open");
    profileDrop?.setAttribute("aria-hidden", "true");
  }
  function toggleProfile() {
    if (!profileWrap) return;
    const isOpen = profileWrap.classList.contains("open");
    if (isOpen) closeProfile();
    else openProfile();
  }

  if (btnProfile) {
    btnProfile.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleProfile();
    });
  }

  document.addEventListener("click", (e) => {
    if (!profileWrap) return;
    if (profileWrap.contains(e.target)) return;
    closeProfile();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeProfile();
  });

})();