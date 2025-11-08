<!DOCTYPE html>
<html lang="vi">
<head>
    @include('driver.blocks.head')
    @include('driver.blocks.css')
</head>

<body>
  <!-- Sidebar Overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <nav id="sidebar">
    @include('driver.blocks.sidebar')
  </nav>

  <!-- Main Content -->
  <div id="main-content">
    <!-- Topbar -->
    <nav class="topbar">
      @include('driver.blocks.topbar')
    </nav>

    <!-- Content -->
    <main class="flex-grow-1 p-3 p-md-4">
        @yield('content')
    </main>
  </div>
  @include('driver.blocks.alert')
  @include('driver.blocks.script')
</body>
</html>