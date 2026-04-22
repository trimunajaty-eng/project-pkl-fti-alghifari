(() => {
  if (window.__MHS_NILAI__) return;
  window.__MHS_NILAI__ = true;

  const app = document.getElementById("app");
  const btnBurger = document.getElementById("btnBurger");
  const overlay = document.getElementById("overlay");

  const bellWrap = document.getElementById("bellWrap");
  const btnBell = document.getElementById("btnBell");
  const bellDrop = document.getElementById("bellDrop");

  const profileWrap = document.getElementById("profileWrap");
  const btnProfile = document.getElementById("btnProfile");
  const profileDrop = document.getElementById("profileDrop");

  const pageLoader = document.getElementById("pageLoader");

  const toast = document.getElementById("toast");
  const toastClose = document.getElementById("toastClose");
  const toastTitle = document.getElementById("toastTitle");
  const toastMsg = document.getElementById("toastMsg");
  const toastIcon = document.getElementById("toastIcon");

  if (!app) return;

  const KEY_COLLAPSE = "mhs_sidebar_collapsed";
  const isMobile = () => window.matchMedia("(max-width: 768px)").matches;

  function syncBurgerIcon() {
    if (!btnBurger) return;
    const isX =
      (isMobile() && app.classList.contains("mobile-open")) ||
      (!isMobile() && app.classList.contains("collapsed"));

    btnBurger.classList.toggle("is-x", isX);
  }

  function applyPersistedCollapse() {
    if (isMobile()) return;
    const saved = localStorage.getItem(KEY_COLLAPSE);
    if (saved === "1") app.classList.add("collapsed");
    else app.classList.remove("collapsed");
    syncBurgerIcon();
  }

  function saveCollapseState() {
    const v = app.classList.contains("collapsed") ? "1" : "0";
    localStorage.setItem(KEY_COLLAPSE, v);
  }

  applyPersistedCollapse();

  function closeMobile() {
    app.classList.remove("mobile-open");
    syncBurgerIcon();
  }

  function toggleMobile() {
    app.classList.toggle("mobile-open");
    syncBurgerIcon();
  }

  function toggleDesktopCollapse() {
    app.classList.toggle("collapsed");
    saveCollapseState();
    syncBurgerIcon();
  }

  if (btnBurger) {
    btnBurger.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (isMobile()) toggleMobile();
      else toggleDesktopCollapse();
    });
  }

  if (overlay) overlay.addEventListener("click", closeMobile);

  function openDrop(wrap, drop) {
    if (!wrap) return;
    wrap.classList.add("open");
    if (drop) drop.setAttribute("aria-hidden", "false");
  }

  function closeDrop(wrap, drop) {
    if (!wrap) return;
    wrap.classList.remove("open");
    if (drop) drop.setAttribute("aria-hidden", "true");
  }

  function toggleDrop(wrap, drop) {
    if (!wrap) return;
    const isOpen = wrap.classList.contains("open");
    if (isOpen) closeDrop(wrap, drop);
    else openDrop(wrap, drop);
  }

  if (btnBell) {
    btnBell.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeDrop(profileWrap, profileDrop);
      toggleDrop(bellWrap, bellDrop);
    });
  }

  if (btnProfile) {
    btnProfile.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeDrop(bellWrap, bellDrop);
      toggleDrop(profileWrap, profileDrop);
    });
  }

  document.addEventListener("click", (e) => {
    if (bellWrap && bellWrap.contains(e.target)) return;
    if (profileWrap && profileWrap.contains(e.target)) return;
    closeDrop(bellWrap, bellDrop);
    closeDrop(profileWrap, profileDrop);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeDrop(bellWrap, bellDrop);
      closeDrop(profileWrap, profileDrop);
      closeMobile();
    }
  });

  function showLoader(ms = 700) {
    if (!pageLoader) return;
    pageLoader.classList.add("show");
    pageLoader.setAttribute("aria-hidden", "false");

    window.setTimeout(() => {
      pageLoader.classList.remove("show");
      pageLoader.setAttribute("aria-hidden", "true");
    }, ms);
  }

  window.addEventListener("load", () => {
    syncBurgerIcon();
    showLoader(550);
  });

  let toastTimer = null;

  function showToast(type, title, msg, duration = 2400) {
    if (!toast) return;

    toast.classList.remove("success", "error");
    if (type) toast.classList.add(type);

    if (toastTitle) toastTitle.textContent = title || (type === "error" ? "Gagal" : "Berhasil");
    if (toastMsg) toastMsg.textContent = msg || "";
    if (toastIcon) toastIcon.textContent = type === "error" ? "!" : "✓";

    toast.classList.add("show");
    toast.setAttribute("aria-hidden", "false");

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(hideToast, duration);
  }

  function hideToast() {
    if (!toast) return;
    toast.classList.remove("show");
    toast.setAttribute("aria-hidden", "true");
  }

  if (toastClose) toastClose.addEventListener("click", hideToast);

  window.addEventListener("resize", () => {
    if (!isMobile()) {
      closeMobile();
      applyPersistedCollapse();
    } else {
      syncBurgerIcon();
    }
  });

  // blokir akun nonaktif
  const blocker = document.getElementById("blocker");
  const blockerMsg = document.getElementById("blockerMsg");
  const blocked = (app.dataset.accountBlocked || "0").trim() === "1";
  const blockedMsg = (app.dataset.blockedMsg || "").trim();

  const nilaiPage = document.getElementById("nilaiPage");
  const apiUrl = nilaiPage?.dataset?.api || "";
  const liveBlockedMsg = (nilaiPage?.dataset?.blockedMsg || blockedMsg || "").trim();

  let blockerQueued = false;
  let blockerShown = false;

  function showBlocker() {
    if (!blocker || blockerShown) return;
    blockerShown = true;

    const msg = liveBlockedMsg || blockedMsg;
    if (msg && blockerMsg) blockerMsg.textContent = msg;

    blocker.classList.add("show");
    blocker.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  if (blocked) {
    blockerQueued = true;
    closeDrop(bellWrap, bellDrop);
    closeDrop(profileWrap, profileDrop);
    closeMobile();

    setTimeout(() => {
      showBlocker();
    }, 1000);
  }

  async function checkAccountStatus() {
    if (!apiUrl || blockerShown || blockerQueued) return;

    try {
      const res = await fetch(apiUrl, {
        method: "GET",
        cache: "no-store",
        headers: { "X-Requested-With": "XMLHttpRequest" }
      });

      const data = await res.json();

      if (data && data.ok && data.status === "nonaktif") {
        blockerQueued = true;

        closeDrop(bellWrap, bellDrop);
        closeDrop(profileWrap, profileDrop);
        closeMobile();

        setTimeout(() => {
          showBlocker();
        }, 1000);
      }
    } catch (err) {
      // diamkan
    }
  }

  checkAccountStatus();
  setInterval(checkAccountStatus, 1000);
})();