<?php
    $user = auth()->user();
?>
<div class="d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
        <button id="toggleSidebar" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-bars"></i>
        </button>
        <input type="text" class="form-control form-control-sm search-input" style="width: 260px;"
            placeholder="Tìm kiếm đơn hàng...">
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="position-relative">
            <i class="fas fa-bell fa-lg text-secondary" style="cursor: pointer;"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                style="font-size: 0.6rem;">3</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="avatar">TN</div>
            <div class="d-none d-sm-block">
                <div class="fw-semibold small">{{ $user->full_name }}</div>
                <div class="text-secondary" style="font-size: 0.75rem;">Shipper</div>
            </div>
        </div>
    </div>
</div>
