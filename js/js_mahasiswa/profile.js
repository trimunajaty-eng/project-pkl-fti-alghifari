(() => {
  if (window.__MHS_PROFILE__) return;
  window.__MHS_PROFILE__ = true;

  const tabs = document.querySelectorAll(".pf-tab");
  const panels = document.querySelectorAll(".pf-panel");

  function openTab(name) {
    tabs.forEach((tab) => {
      tab.classList.toggle("active", tab.dataset.tab === name);
    });

    panels.forEach((panel) => {
      panel.classList.toggle("active", panel.dataset.panel === name);
    });
  }

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const name = tab.dataset.tab || "profil";
      openTab(name);
    });
  });

  const page = document.getElementById("profilePage");
  const apiUrl = page?.dataset?.api || "";
  const blockedMsg = (page?.dataset?.blockedMsg || "").trim();

  const blocker = document.getElementById("blocker");
  const blockerMsg = document.getElementById("blockerMsg");

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
        setTimeout(() => {
          showBlocker();
        }, 1000);
      }
    } catch (err) {
      // biarkan diam
    }
  }

  checkAccountStatus();
  setInterval(checkAccountStatus, 1);
})();