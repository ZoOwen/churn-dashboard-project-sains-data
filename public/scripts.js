// public/js/dashboard.js

document.addEventListener("DOMContentLoaded", () => {
  const sidebarItems = document.querySelectorAll(".sidebar-nav ul li");
  const sidebarToggle = document.getElementById("toggleSidebar");
  const sidebar = document.querySelector(".sidebar");

  sidebarItems.forEach((item) => {
    item.addEventListener("click", () => {
      sidebarItems.forEach((i) => i.classList.remove("active"));
      item.classList.add("active");
    });
  });

  // Toggle sidebar collapse
  sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
  });
});
document.querySelectorAll('.submenu-toggle').forEach(item => {
  item.addEventListener('click', function (e) {
    e.preventDefault();
    const parent = this.parentElement;
    parent.classList.toggle('open');
  });
});