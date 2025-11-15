   <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href=" https://demos.creative-tim.com/material-dashboard/pages/dashboard " target="_blank">
        <img src="{{asset('assets2/img/logo-ct-dark.png')}}" class="navbar-brand-img" width="26" height="26" alt="main_logo">
        <span class="ms-1 text-sm text-dark">Trang quản trị</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.index') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('admin.index') }}">
              <i class="material-symbols-rounded opacity-5">dashboard</i>
              <span class="nav-link-text ms-1">Trang chủ</span>
          </a>
        </li>
        <li class="nav-item mt-3">
          <h5 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Quản lý</h5>
        </li>
        <li class="nav-item">
          <a class="nav-link {{request()->routeIs('admin.driver.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('admin.driver.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Hồ sơ</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('admin.cod.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('admin.cod.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Quản lý tiền COD</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('admin.orders.approval.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('admin.orders.approval.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Duyệt đơn hàng</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('admin.orders.tracking.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('admin.orders.tracking.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Lịch sử vận đơn</span>
          </a>
        </li>
         <li class="nav-item">
          <a class="nav-link {{request()->routeIs('admin.bank-accounts.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('admin.bank-accounts.index') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
            <span class="nav-link-text ms-1">Tài khoản ngân hàng </span>
          </a>
        </li>
        <li class="nav-item">
           <a href="{{ route('logout') }}"  class="nav-link {{request()->routeIs('logout') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('logout') }}">
            <i class="material-symbols-rounded opacity-5">person</i>
                <span class="nav-link-text ms-1">Đăng xuất</span>
              </a>
        </li>
      </ul>
    </div>