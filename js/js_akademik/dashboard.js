(function () {
  const menuToggle = document.getElementById("menuToggle");
  const sidebar = document.getElementById("sidebar");

  if (menuToggle && sidebar) {
    menuToggle.addEventListener("click", function () {
      sidebar.classList.toggle("show");
    });

    document.addEventListener("click", function (e) {
      const isMobile = window.innerWidth <= 860;
      if (!isMobile) return;

      const clickInsideSidebar = sidebar.contains(e.target);
      const clickToggle = menuToggle.contains(e.target);

      if (!clickInsideSidebar && !clickToggle) {
        sidebar.classList.remove("show");
      }
    });
  }
})();