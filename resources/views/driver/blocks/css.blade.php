<style>
     * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #f8f9fa;
      overflow-x: hidden;
    }

    /* Sidebar */
    #sidebar {
      width: 260px;
      background-color: #212529;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      z-index: 1050;
      overflow-y: auto;
      overflow-x: hidden;
      transition: transform 0.3s ease, width 0.3s ease;
    }

    #sidebar::-webkit-scrollbar {
      width: 6px;
    }

    #sidebar::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.2);
      border-radius: 3px;
    }

    .sidebar-header {
      padding: 1rem;
      border-bottom: 1px solid #343a40;
      text-align: center;
      min-height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .sidebar-header i {
      font-size: 1.5rem;
      transition: all 0.3s ease;
    }

    .sidebar-header span {
      font-size: 1.25rem;
      font-weight: 600;
      transition: opacity 0.2s ease;
      white-space: nowrap;
    }

    #sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    #sidebar ul li {
      position: relative;
    }

    #sidebar ul li.active {
      border-left: 4px solid #0d6efd;
      background-color: rgba(13, 110, 253, 0.15);
    }

    #sidebar ul li a {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.5rem;
      color: #fff;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    #sidebar ul li a:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }

    #sidebar ul li a i {
      font-size: 1.1rem;
      min-width: 20px;
      text-align: center;
    }

    .menu-text {
      white-space: nowrap;
      transition: opacity 0.2s ease;
    }

    /* Main Content */
    #main-content {
      margin-left: 260px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      transition: margin-left 0.3s ease;
    }

    /* Topbar */
    .topbar {
      background-color: #fff;
      border-bottom: 1px solid #dee2e6;
      padding: 0.75rem 1rem;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    /* Avatar */
    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #0d6efd;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 0.9rem;
    }

    /* Card */
    .card {
      border-radius: 0.5rem;
      border: none;
    }

    .table th, .table td {
      vertical-align: middle;
    }

    /* Overlay for mobile */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.6);
      z-index: 1040;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    /* Desktop: Collapsed sidebar */
    @media (min-width: 769px) {
      #sidebar.collapsed {
        width: 80px;
      }

      #sidebar.collapsed .sidebar-header span {
        opacity: 0;
        display: none;
      }

      #sidebar.collapsed .menu-text {
        opacity: 0;
        display: none;
      }

      #sidebar.collapsed ~ #main-content {
        margin-left: 80px;
      }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      #sidebar {
        transform: translateX(-100%);
      }

      #sidebar.show {
        transform: translateX(0);
      }

      .sidebar-overlay.active {
        display: block;
        opacity: 1;
      }

      #main-content {
        margin-left: 0 !important;
      }

      .search-input {
        width: 150px !important;
      }

      .stat-card-title {
        font-size: 0.7rem !important;
      }

      .stat-card-value {
        font-size: 1.5rem !important;
      }
    }

    @media (max-width: 576px) {
      .search-input {
        display: none !important;
      }

      .stat-icon {
        display: none;
      }
    }
</style>