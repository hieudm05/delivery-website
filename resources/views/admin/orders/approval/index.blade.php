@extends('admin.layouts.app')

@section('title', 'Duyệt Đơn Hàng')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-check-circle"></i> Duyệt Đơn Hàng</h2>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="triggerAutoApproval()">
                <i class="bi bi-robot"></i> Chạy Duyệt Tự Động
            </button>
            <a href="{{ route('admin.orders.approval.statistics') }}" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up"></i> Thống Kê
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Chờ Duyệt</h6>
                            <h3 class="mb-0">{{ $stats['total_pending'] }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-clock-history fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Có Thể Auto</h6>
                            <h3 class="mb-0">{{ $stats['can_auto_approve'] }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-robot fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Rủi Ro Cao</h6>
                            <h3 class="mb-0">{{ $stats['high_risk'] }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Đã Duyệt Hôm Nay</h6>
                            <h3 class="mb-0">{{ $stats['today_approved'] }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.approval.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Bộ lọc</label>
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="auto" {{ $filter === 'auto' ? 'selected' : '' }}>Có thể Auto Approve</option>
                        <option value="manual" {{ $filter === 'manual' ? 'selected' : '' }}>Cần duyệt thủ công</option>
                        <option value="high_risk" {{ $filter === 'high_risk' ? 'selected' : '' }}>Rủi ro cao</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Mã đơn, tên, số điện thoại..." 
                           value="{{ $search }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                        <a href="{{ route('admin.orders.approval.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Actions -->
    @if($orders->count() > 0)
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <input type="checkbox" id="selectAll" class="pb-1" style="cursor: pointer;">
                <label for="selectAll" class="form-check-label" style="cursor: pointer;">Chọn tất cả</label>
                <button type="button" class="btn btn-success" onclick="submitBatchApproval()">
                    <i class="bi bi-check-all"></i> Duyệt hàng loạt (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            @if($orders->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">Không có đơn hàng nào chờ duyệt</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="30"></th>
                                <th>Mã ĐH</th>
                                <th>Người gửi</th>
                                <th>Người nhận</th>
                                <th>COD</th>
                                <th>Phí ship</th>
                                <th>Rủi ro</th>
                                <th>Thời gian</th>
                                <th>Auto?</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>
                                    <input type="checkbox" class="order-checkbox" 
                                           value="{{ $order->id }}"
                                           data-order-id="{{ $order->id }}"
                                           style="cursor: pointer;">
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.approval.show', $order->id) }}">
                                        #{{ $order->id }}
                                    </a>
                                    @if($order->orderGroup)
                                        <br><small class="text-muted">Nhóm: #{{ $order->order_group_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $order->sender_name }}</div>
                                    <small class="text-muted">{{ $order->sender_phone }}</small>
                                </td>
                                <td>
                                    <div>{{ $order->recipient_name }}</div>
                                    <small class="text-muted">{{ $order->recipient_phone }}</small>
                                </td>
                                <td>
                                    @if($order->cod_amount > 0)
                                        <span class="badge bg-warning text-dark">
                                            {{ number_format($order->cod_amount) }}đ
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ number_format($order->shipping_fee) }}đ</td>
                                <td>
                                    @php
                                        $risk = $order->risk_level;
                                    @endphp
                                    <span class="badge bg-{{ $risk['color'] }}">
                                        {{ $risk['label'] }} ({{ $order->risk_score }})
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $order->created_at->diffForHumans() }}</small>
                                    @if($order->delivery_time && $order->delivery_time < now()->addHours(12))
                                        <br><span class="badge bg-danger">Gấp!</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $canAuto = method_exists($order, 'canAutoApprove') 
                                            ? $order->canAutoApprove() 
                                            : (is_null($order->risk_score) || $order->risk_score < 30);
                                    @endphp
                                    @if($canAuto)
                                        <i class="bi bi-robot text-success" title="Có thể auto approve"></i>
                                    @else
                                        <i class="bi bi-person text-warning" title="Cần duyệt thủ công"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.orders.tracking.show', $order->id) }}" 
                                           class="btn btn-outline-primary" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                       <button type="button" class="btn btn-outline-success" 
                                            onclick="quickApprove(event, {{ $order->id }})" title="Duyệt nhanh">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            orderCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Individual checkbox change
    orderCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelectedCount();
            
            // Update select all checkbox state
            const allChecked = Array.from(orderCheckboxes).every(checkbox => checkbox.checked);
            const someChecked = Array.from(orderCheckboxes).some(checkbox => checkbox.checked);
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            }
        });
    });
    
    // Initialize count
    updateSelectedCount();
});

function updateSelectedCount() {
    const count = document.querySelectorAll('.order-checkbox:checked').length;
    const countElement = document.getElementById('selectedCount');
    if (countElement) {
        countElement.textContent = count;
    }
}

function getSelectedOrderIds() {
    const checkboxes = document.querySelectorAll('.order-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Quick approve single order
function quickApprove(event, orderId) {
    if (!confirm('Xác nhận duyệt đơn hàng #' + orderId + '?')) {
        return;
    }

    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/admin/orders/approval/${orderId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            note: 'Duyệt nhanh'
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Đã duyệt đơn #' + orderId);
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.message || '❌ Lỗi duyệt đơn');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(() => {
        alert('❌ Lỗi kết nối server');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}


// Batch approval
function submitBatchApproval() {
    const selectedIds = getSelectedOrderIds();
    
    if (selectedIds.length === 0) {
        showAlert('warning', 'Vui lòng chọn ít nhất 1 đơn hàng');
        return;
    }
    
    if (!confirm(`Xác nhận duyệt ${selectedIds.length} đơn hàng?`)) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
    
    fetch('/admin/orders/approval/batch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            order_ids: selectedIds,
            note: 'Duyệt hàng loạt'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || `Đã duyệt thành công ${selectedIds.length} đơn hàng`);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message || 'Có lỗi xảy ra!');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Có lỗi xảy ra khi duyệt hàng loạt!');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// Auto approval trigger
function triggerAutoApproval() {
    if (!confirm('Chạy duyệt tự động cho các đơn rủi ro thấp?')) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
    
    fetch('/admin/orders/approval/auto-approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message || 'Có lỗi xảy ra!');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Có lỗi xảy ra khi chạy duyệt tự động!');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// Helper function to show alerts
function showAlert(type, message) {
    // Check if Bootstrap alert exists
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        // Use Bootstrap toast if available
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        // Show toast (you'd need a toast container in your layout)
    }
    
    // Fallback to alert
    alert(message);
}
</script>

<style>
.order-checkbox:hover,
#selectAll:hover {
    transform: scale(1.1);
}

.btn-group-sm .btn {
    transition: all 0.2s;
}

.btn-group-sm .btn:hover {
    transform: translateY(-2px);
}
</style>
@endsection