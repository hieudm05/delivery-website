  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    toggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        // Mobile: Toggle sidebar slide
        sidebar.classList.toggle('show');
        overlay.classList.toggle('active');
      } else {
        // Desktop: Toggle collapsed
        sidebar.classList.toggle('collapsed');
      }
    });

    // Click overlay to close sidebar on mobile
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('show');
      overlay.classList.remove('active');
    });

    // Handle window resize
    window.addEventListener('resize', () => {
      if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
        overlay.classList.remove('active');
      } else {
        sidebar.classList.remove('collapsed');
      }
    });

    // Close sidebar when clicking on menu items on mobile
    const menuLinks = document.querySelectorAll('#sidebar a');
    menuLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('show');
          overlay.classList.remove('active');
        }
      });
    });
  </script>