<div class="sidenav-header">
  <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
  <a class="navbar-brand px-4 py-3 m-0" href="{{ route('customer.dashboard.index') }}" target="_blank">
    <img src="{{asset('assets2/img/logo-ct-dark.png')}}" class="navbar-brand-img" width="26" height="26" alt="main_logo">
    <span class="ms-1 text-sm text-dark">Trang quản trị</span>
  </a>
</div>
<hr class="horizontal dark mt-0 mb-2">
<div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('customer.dashboard.index') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('customer.dashboard.index') }}">
          <i class="material-symbols-rounded opacity-5">dashboard</i>
          <span class="nav-link-text ms-1">Trang chủ</span>
      </a>
    </li>
    <li class="nav-item mt-3">
      <h5 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Đơn hàng</h5>
    </li>
    <li class="nav-item">
      <a class="nav-link {{request()->routeIs('customer.orders.create') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('customer.orders.create') }}">
        <i class="material-symbols-rounded opacity-5">add_box</i>
        <span class="nav-link-text ms-1">Tạo đơn</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link {{request()->routeIs('customer.orderManagent.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('customer.orderManagent.index') }}">
        <i class="material-symbols-rounded opacity-5">inventory_2</i>
        <span class="nav-link-text ms-1">Vận đơn</span>
      </a>
    </li>
     <li class="nav-item">
      <a class="nav-link {{request()->routeIs('customer.bank-accounts.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('customer.bank-accounts.index') }}">
        <i class="material-symbols-rounded opacity-5">account_balance</i>
        <span class="nav-link-text ms-1">Tài khoản ngân hàng</span>
      </a>
    </li>
     <li class="nav-item">
      <a class="nav-link {{request()->routeIs('customer.cod.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('customer.cod.index') }}">
        <i class="material-symbols-rounded opacity-5">payments</i>
        <span class="nav-link-text ms-1">Đóng tiền Hub</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{request()->routeIs('customer.income.index') ? 'active bg-gradient-dark text-white' : 'text-dark'}}" href="{{ route('customer.income.index') }}">
        <i class="material-symbols-rounded opacity-5">payments</i>
        <span class="nav-link-text ms-1">Thu chi</span>
      </a>
    </li>
     <li class="nav-item mt-3">
      <h5 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Cài đặt tài khoản</h5>
    </li>
    <li class="nav-item">
     <a class="nav-link {{ request()->routeIs('customer.account.index') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('customer.account.index') }}">
        <i class="material-symbols-rounded opacity-5">account_circle</i>
        <span class="nav-link-text ms-1">Thông tin tài khoản</span>
    </a>
    </li>
    <li class="nav-item">
      <a class="nav-link text-dark" href="{{ route('home') }}">
        <i class="material-symbols-rounded opacity-5">home</i>
        <span class="nav-link-text ms-1">Về trang client</span>
      </a>
    </li>
    
  
  </ul>
</div>