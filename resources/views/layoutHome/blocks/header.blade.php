<div class="container-fluid container-xl position-relative d-flex align-items-center">

  <!-- Logo -->
  <a href="{{ url('/') }}" class="logo d-flex align-items-center me-auto">
    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo">
    {{-- <h1 class="sitename">Logis</h1> --}}
  </a>

  <!-- Menu chính -->
  <nav id="navmenu" class="navmenu">
    <ul>
      <li><a href="{{ url('/') }}" class="active">Home</a></li>
      <li><a href="{{ url('/about') }}">About</a></li>
      <li><a href="{{ url('/apply') }}">Ứng tuyển</a></li>
      <li><a href="{{ url('/pricing') }}">Pricing</a></li>

      <li class="dropdown">
        <a href="#"><span>Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
        <ul>
          <li><a href="#">Dropdown 1</a></li>
          <li class="dropdown">
            <a href="#"><span>Deep Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li><a href="#">Deep Dropdown 1</a></li>
              <li><a href="#">Deep Dropdown 2</a></li>
              <li><a href="#">Deep Dropdown 3</a></li>
              <li><a href="#">Deep Dropdown 4</a></li>
              <li><a href="#">Deep Dropdown 5</a></li>
            </ul>
          </li>
          <li><a href="#">Dropdown 2</a></li>
          <li><a href="#">Dropdown 3</a></li>
          <li><a href="#">Dropdown 4</a></li>
        </ul>
      </li>

      <li><a href="{{ url('/contact') }}">Contact</a></li>
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
               <span></span>
              @endif
              <i class="bi bi-chevron-down toggle-dropdown"></i>
            </a>
            <ul>
              <li><a href="#">Trang cá nhân</a></li>
              @if ($user && $user->role ==="customer")
                 <li><a href="{{url('customer/dashboard')}}">Trang quản trị</a></li>
              @elseif($user && $user->role ==="admin")
                 <li><a href="{{url('admin/')}}">Trang quản trị</a></li>
              @else
                <span></span>
              @endif
              <li><a href="{{ route('logout') }}"   
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Đăng xuất</a>
              </li>
              <form id="logout-form" action="{{route('logout')}}">
                @csrf
              </form>
            </ul>
          </li>
        </ul>
      </nav>
    @else
      <!-- Nút Đăng ký / Đăng nhập -->
      <a class="btn btn-light" href="{{ url('/register') }}">Đăng ký</a>
      <form action="{{ route('login') }}" method="get" class="d-inline">
        <button type="submit" class="btn btn-primary">Đăng nhập</button>
      </form>
    @endif
  </div>

</div>
