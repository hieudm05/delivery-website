<div class="sidebar-header">
    <i class="fas fa-box text-primary"></i>
    <span class="text-white">ShipHub</span>
</div>

<ul>
    <li class="{{ request()->is('driver/') ? 'active' : '' }}">
        <a href="{{ url('driver/') }}">
            <i class="fas fa-gauge-high"></i>
            <span class="menu-text">Trang chủ</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('driver.pickup.index') ? 'active' : '' }}">
        <a href="{{ route('driver.pickup.index') }}">
            <i class="fas fa-box-open"></i>
            <span class="menu-text">Đơn Hàng</span>
        </a>
    </li>

    <li class="{{ request()->is('driver.delivery.index') ? 'active' : '' }}">
        <a href="{{ route('driver.delivery.index') }}">
            <i class="fas fa-truck-fast"></i>
            <span class="menu-text">Giao hàng</span>
        </a>
    </li>

    <li class="{{ request()->is('driver.bank-accounts.index') ? 'active' : '' }}">
        <a href="{{ route('driver.bank-accounts.index') }}">
            <i class="fas fa-building-columns"></i>
            <span class="menu-text">Tài khoản ngân hàng</span>
        </a>
    </li>

    <li class="{{ request()->is('driver.cod.index') ? 'active' : '' }}">
        <a href="{{ route('driver.cod.index') }}">
            <i class="fas fa-money-bill-wave"></i>
            <span class="menu-text">Thanh toán COD</span>
        </a>
    </li>

    <li class="{{ request()->is('driver/wallet*') ? 'active' : '' }}">
        <a href="#">
            <i class="fas fa-wallet"></i>
            <span class="menu-text">Thu Nhập</span>
        </a>
    </li>

    <li class="{{ request()->is('driver/review*') ? 'active' : '' }}">
        <a href="#">
            <i class="fas fa-star"></i>
            <span class="menu-text">Đánh Giá</span>
        </a>
    </li>

    <li class="{{ request()->is('logout') ? 'active' : '' }}">
        <a href="{{ url('logout') }}">
            <i class="fas fa-right-from-bracket"></i>
            <span class="menu-text">Đăng xuất</span>
        </a>
    </li>
</ul>