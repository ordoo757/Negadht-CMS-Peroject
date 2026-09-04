document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') { lucide.createIcons(); }

  const sidebar = document.getElementById('adminSidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebarClose = document.getElementById('sidebarClose');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const langDropdownToggle = document.getElementById('langDropdownToggle');
  const langDropdown = document.getElementById('langDropdown');
  const fullscreenToggle = document.getElementById('fullscreenToggle');

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.add('open');
      sidebarOverlay.classList.add('show');
      document.body.style.overflow = 'hidden';
    });
  }

  if (sidebarClose) {
    sidebarClose.addEventListener('click', () => {
      sidebar.classList.remove('open');
      sidebarOverlay.classList.remove('show');
      document.body.style.overflow = '';
    });
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      sidebarOverlay.classList.remove('show');
      document.body.style.overflow = '';
    });
  }

  if (langDropdownToggle && langDropdown) {
    langDropdownToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      langDropdown.classList.toggle('show');
    });
    document.addEventListener('click', (e) => {
      if (!langDropdownToggle.contains(e.target)) {
        langDropdown.classList.remove('show');
      }
    });
  }

  document.querySelectorAll('.section-header').forEach(header => {
    header.addEventListener('click', () => {
      header.closest('.sidebar-section').classList.toggle('collapsed');
    });
  });

  document.querySelectorAll('.menu-item.has-children').forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      item.classList.toggle('expanded');
      const submenu = item.nextElementSibling;
      if (submenu && submenu.classList.contains('submenu')) {
        submenu.classList.toggle('open');
      }
    });
  });

  if (fullscreenToggle) {
    fullscreenToggle.addEventListener('click', () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
      } else {
        document.exitFullscreen();
      }
    });
  }

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) {
      sidebar.classList.remove('open');
      sidebarOverlay.classList.remove('show');
      document.body.style.overflow = '';
    }
  });

  window.reinitIcons = function() {
    if (typeof lucide !== 'undefined') { lucide.createIcons(); }
  };
});
