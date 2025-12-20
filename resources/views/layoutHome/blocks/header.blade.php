<div class="container-fluid container-xl position-relative d-flex align-items-center">

  <!-- Logo -->
  <a href="{{ url('/') }}" class="logo d-flex align-items-center me-auto">
    <div class="logo-text">
      <div class="logo-icon" style="font-size: 24px; font-weight: bold; color: #0d42f4; margin-right: 8px;">
        🚚
      </div>
      <div>
        <h1 class="sitename" style="margin: 0; font-size: 18px; font-weight: 700;">Giao Hàng 24</h1>
        <small style="color: #666; font-size: 11px;">Hà Nội</small>
      </div>
    </div>
  </a>

  <!-- Menu chính -->
  <nav id="navmenu" class="navmenu">
    <ul>
      <li><a href="{{ url('/') }}" class="active">Trang Chủ</a></li>
      <li><a href="#featured-services">Dịch Vụ</a></li>
      <li><a href="#services">Các Giải Pháp</a></li>
      <li><a href="#pricing">Bảng Giá</a></li>
      <li><a href="#faq">Hỏi Đáp</a></li>
      <li><a href="#about">Về Chúng Tôi</a></li>
    </ul>
    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
  </nav>

  <!-- Menu người dùng -->
  <div class="d-flex align-items-center gap-2 ms-3">
    @php
      $user = Auth::user();
      $firstName = '';
      if ($user && $user->full_name) {
          $parts = explode(' ', trim($user->full_name));
          $firstName = end($parts);
      }
    @endphp

    @if($user)
      <!-- Dropdown người dùng -->
      <nav class="navmenu">
        <ul>
          <li class="dropdown">
            <a href="#">
              <span class="fw-bold text-white">{{ $firstName }}</span>
              @if($user->avatar_url)
                <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="Avatar" width="35" height="35" class="rounded-circle ms-1">
              @else
                <i class="bi bi-person-circle" style="font-size: 24px;"></i>
              @endif
              <i class="bi bi-chevron-down toggle-dropdown"></i>
            </a>
            <ul>
              <li><a href="#">Trang Cá Nhân</a></li>
              @if ($user && $user->role === 'customer')
                <li><a href="{{ url('customer/dashboard') }}">Trang Quản Trị</a></li>
              @elseif($user && $user->role === 'admin')
                <li><a href="{{ url('admin/') }}">Trang Quản Trị</a></li>
              @endif
              <li>
                <a href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  Đăng Xuất
                </a>
              </li>
              <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
              </form>
            </ul>
          </li>
        </ul>
      </nav>
    @else
      <!-- Nút Đăng ký / Đăng nhập -->
      <a class="btn btn-light" href="{{ url('/register') }}">Đăng Ký</a>
      <a href="{{ url('/login') }}" class="btn btn-primary">Đăng Nhập</a>
    @endif
  </div>

</div>

<style>
  .logo-text {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .logo-text h1 {
    color: #0d42f4;
    font-weight: 700;
    margin: 0;
  }

  .logo-text small {
    color: #666;
    font-size: 11px;
    display: block;
  }
</style>