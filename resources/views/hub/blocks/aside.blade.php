   <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href=" {{ route('hub.index') }} " target="_blank">
        <img src="{{asset('assets2/img/logo-ct-dark.png')}}" class="navbar-brand-img" width="26" height="26" alt="main_logo">
        <span class="ms-1 text-lg text-dark font-bold">Admin bưu cục</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('hub.index') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('hub.index') }}">
              <i class="material-symbols-rounded opacity-5">dashboard</i>
              <span class="nav-link-text ms-1">Trang chủ</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{request()->routeIs('hub.approval') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('hub.approval') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Duyệt đơn</span>
          </a>
        </li>
         <li class="nav-item mt-3">
          <h5 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Quản lý</h5>
        </li>
        <li class="nav-item">
          <a class="nav-link {{request()->routeIs('hub.orders.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('hub.orders.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Lịch sử đơn hàng</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('hub.bank-accounts.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('hub.bank-accounts.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Tài khoản Ngân hàng</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('hub.drivers.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('hub.drivers.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Nhân viên</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('hub.cod.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('hub.cod.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">COD</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('logout') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('logout') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Đăng xuất</span>
          </a>
        </li>
      </ul>
    </div>