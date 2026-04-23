(() => {
  if (window.__MHS_NILAI__) return;
  window.__MHS_NILAI__ = true;

  const app = document.getElementById("app");
  if (!app) return;

  // blocker akun nonaktif
  const page = document.getElementById("nilaiPage");
  const apiUrl = page?.dataset?.api || "";
  const blockedMsg = (page?.dataset?.blockedMsg || "").trim();

  const blocker = document.getElementById("blocker");
  const blockerMsg = document.getElementById("blockerMsg");
  const blocked = (app.dataset.accountBlocked || "0").trim() === "1";

  let blockerQueued = false;
  let blockerShown = false;

  function showBlocker() {
    if (!blocker || blockerShown) return;
    blockerShown = true;
    if (blockedMsg && blockerMsg) blockerMsg.textContent = blockedMsg;
    blocker.classList.add("show");
    blocker.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  if (blocked) {
    blockerQueued = true;
    setTimeout(showBlocker, 1000);
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
        setTimeout(showBlocker, 1000);
      }
    } catch (err) {}
  }

  checkAccountStatus();
  setInterval(checkAccountStatus, 1000);
})();