(function () {
  const sidebar = document.getElementById("sidebar");
  const menuToggle = document.getElementById("menuToggle");

  if (sidebar && menuToggle) {
    menuToggle.addEventListener("click", function () {
      sidebar.classList.toggle("show");
    });

    document.addEventListener("click", function (e) {
      if (window.innerWidth > 860) return;

      const insideSidebar = sidebar.contains(e.target);
      const clickToggle = menuToggle.contains(e.target);

      if (!insideSidebar && !clickToggle) {
        sidebar.classList.remove("show");
      }
    });
  }
})();