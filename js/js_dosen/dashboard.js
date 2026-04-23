(() => {
  if (window.__DOSEN_DASHBOARD__) return;
  window.__DOSEN_DASHBOARD__ = true;

  const loader = document.getElementById("dosenLoader");
  const app = document.getElementById("dosenApp");

  if (app) {
    app.style.visibility = "hidden";
  }

  function hideLoader() {
    if (app) {
      app.style.visibility = "visible";
    }

    if (!loader) return;

    loader.classList.add("hide");
    loader.setAttribute("aria-hidden", "true");

    setTimeout(() => {
      loader.remove();
    }, 500);
  }

  window.addEventListener("load", () => {
    setTimeout(hideLoader, 900); // animasi loading dashboard dosen
  });

  // fallback
  setTimeout(hideLoader, 1600);
})();