(() => {
  if (window.__MHS_SETTINGS__) return;
  window.__MHS_SETTINGS__ = true;

  const page = document.getElementById("settingsPage");
  const apiUrl = page?.dataset?.api || "";
  const blockedMsg = (page?.dataset?.blockedMsg || "").trim();

  const blocker = document.getElementById("blocker");
  const blockerMsg = document.getElementById("blockerMsg");

  const form = document.getElementById("formSettings");
  const btnSave = document.getElementById("btnSave");
  const btnSpin = document.getElementById("btnSpin");
  const btnText = document.getElementById("btnText");

  const oldPassword = document.getElementById("old_password");
  const newPassword = document.getElementById("new_password");
  const confirmPassword = document.getElementById("confirm_password");
  const hintMatch = document.getElementById("hintMatch");

  let blockerQueued = false;
  let blockerShown = false;

  function disableFormBecauseBlocked() {
    if (!form) return;

    form.classList.add("form-blocked");

    form.querySelectorAll("input, button").forEach((el) => {
      if (el && el.id !== "blockerBtn") {
        el.disabled = true;
      }
    });
  }

  function showBlocker() {
    if (!blocker || blockerShown) return;
    blockerShown = true;

    if (blockedMsg && blockerMsg) blockerMsg.textContent = blockedMsg;

    blocker.classList.add("show");
    blocker.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";

    disableFormBecauseBlocked();
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
      // diamkan saja agar UX tidak terganggu
    }
  }

  checkAccountStatus();
  setInterval(checkAccountStatus, 1000);

  document.querySelectorAll(".set-eye[data-eye]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-eye");
      const input = document.getElementById(id);
      if (!input || input.disabled) return;

      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      btn.classList.toggle("is-show", isPassword);
    });
  });

  function updateMatchHint() {
    if (!hintMatch || !newPassword || !confirmPassword) return;

    const newVal = newPassword.value.trim();
    const conVal = confirmPassword.value.trim();

    hintMatch.classList.remove("ok", "err");
    hintMatch.textContent = "";

    if (newVal === "" && conVal === "") return;

    if (conVal === "") {
      hintMatch.textContent = "Masukkan konfirmasi password baru.";
      return;
    }

    if (newVal === conVal) {
      hintMatch.classList.add("ok");
      hintMatch.textContent = "Konfirmasi password sudah cocok.";
    } else {
      hintMatch.classList.add("err");
      hintMatch.textContent = "Konfirmasi password belum sama.";
    }
  }

  if (newPassword) newPassword.addEventListener("input", updateMatchHint);
  if (confirmPassword) confirmPassword.addEventListener("input", updateMatchHint);

  if (form) {
    form.addEventListener("submit", () => {
      if (btnSave) btnSave.disabled = true;
      if (btnSpin) btnSpin.style.display = "inline-block";
      if (btnText) btnText.textContent = "Menyimpan...";
    });
  }
})();