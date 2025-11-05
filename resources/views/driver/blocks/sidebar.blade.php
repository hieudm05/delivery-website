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
            <i class="fas fa-clipboard-list"></i>
            <span class="menu-text">Đơn Hàng</span>
        </a>
    </li>

    <li class="{{ request()->is('driver.delivery.index') ? 'active' : '' }}">
        <a href="{{ route('driver.delivery.index') }}">
            <i class="fas fa-route"></i>
            <span class="menu-text">Giao hàng</span>
        </a>
    </li>

    <li class="{{ request()->is('driver/map*') ? 'active' : '' }}">
        <a href="#">
            <i class="fas fa-map-location-dot"></i>
            <span class="menu-text">Bản Đồ</span>
        </a>
    </li>

    <li class="{{ request()->is('driver/statistic*') ? 'active' : '' }}">
        <a href="#">
            <i class="fas fa-chart-bar"></i>
            <span class="menu-text">Thống Kê</span>
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
            <i class="fas fa-cog"></i>
            <span class="menu-text">Đăng xuất</span>
        </a>
    </li>
</ul>
