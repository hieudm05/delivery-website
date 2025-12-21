@extends('hub.layouts.app')

@section('title', 'Quản lý hoàn hàng')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-undo text-warning"></i> Quản lý hoàn hàng</h4>
            <p class="text-muted small mb-0">Theo dõi và xử lý các đơn hàng cần hoàn về sender</p>
        </div>
        <div>
            <a href="{{ route('hub.issues.index') }}" class="btn btn-outline-danger me-2">
                <i class="fas fa-exclamation-triangle"></i> Quản lý Issues
            </a>
            <a href="{{ route('hub.returns.statistics') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar"></i> Thống kê
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Chờ phân công</h6>
                            <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Đã phân tài xế</h6>
                            <h3 class="mb-0">{{ $stats['assigned'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Đang hoàn về</h6>
                            <h3 class="mb-0">{{ $stats['returning'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Hoàn thành hôm nay</h6>
                            <h3 class="mb-0">{{ $stats['completed_today'] }}</h3>
                            <small class="text-white-50">
                                Phí: {{ number_format($stats['total_return_fee']) }}đ
                            </small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Trạng thái</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>
                            Chờ phân công ({{ $stats['pending'] }})
                        </option>
                        <option value="assigned" {{ $status == 'assigned' ? 'selected' : '' }}>
                            Đã phân tài xế ({{ $stats['assigned'] }})
                        </option>
                        <option value="returning" {{ $status == 'returning' ? 'selected' : '' }}>
                            Đang hoàn về ({{ $stats['returning'] }})
                        </option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>
                            Hoàn thành
                        </option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>
                            Đã hủy
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Mã đơn, tên sender, SĐT..." 
                           value="{{ $search }}">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                    @if($stats['pending'] > 0)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#batchAssignModal">
                            <i class="fas fa-users"></i> Phân hàng loạt
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Returns List -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách đơn hoàn ({{ $returns->total() }})</h6>
                @if($returns->isNotEmpty() && $status == 'pending')
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSelectAll()">
                        <i class="fas fa-check-square"></i> Chọn tất cả
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($returns->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Không có đơn hoàn nào</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                @if($status == 'pending')
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                    </th>
                                @endif
                                <th>Đơn hàng</th>
                                <th>Sender</th>
                                <th>Địa chỉ hoàn</th>
                                <th>Lý do hoàn</th>
                                <th>Trạng thái</th>
                                <th>Tài xế</th>
                                <th>Chi phí</th>
                                <th>Thời gian</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returns as $return)
                            <tr>
                                @if($status == 'pending')
                                    <td>
                                        <input type="checkbox" class="form-check-input return-checkbox" 
                                               value="{{ $return->id }}">
                                    </td>
                                @endif
                                <td>
                                    <div>
                                        <strong class="text-primary">#{{ $return->order->id }}</strong>
                                        @if($return->failed_attempts >= 3)
                                            <span class="badge bg-danger ms-1" title="Thất bại {{ $return->failed_attempts }} lần">
                                                {{ $return->failed_attempts }}x
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $return->initiated_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $return->sender_name }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-phone"></i> {{ $return->sender_phone }}
                                    </small>
                                </td>
                                <td>
                                    <small style="max-width: 200px; display: block; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $return->sender_address }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $return->reason_type === 'auto_failed' ? 'danger' : 'warning' }} text-wrap" style="max-width: 150px;">
                                        {{ $return->reason_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $return->status_badge }}">
                                        {{ $return->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($return->driver)
                                        <div class="small">
                                            <i class="fas fa-user"></i> {{ $return->driver->name }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-danger fw-bold">{{ number_format($return->return_fee) }}đ</div>
                                    @if($return->cod_amount > 0)
                                        <small class="text-warning">COD: {{ number_format($return->cod_amount) }}đ</small>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        @if($return->isPending())
                                            Khởi tạo: {{ $return->initiated_at->format('d/m H:i') }}
                                        @elseif($return->isAssigned())
                                            Phân: {{ $return->assigned_at->format('d/m H:i') }}
                                        @elseif($return->isReturning())
                                            Bắt đầu: {{ $return->started_at->format('d/m H:i') }}
                                        @elseif($return->isCompleted())
                                            Hoàn: {{ $return->completed_at->format('d/m H:i') }}
                                        @endif
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('hub.returns.show', $return->id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       >
                                       Chi tiết
                                    </a>

                                    @if($return->isPending())
                                        <a href="{{ route('hub.returns.assign-form', $return->id) }}" 
                                           class="btn btn-sm btn-success" 
                                           >
                                           Phân tài xế
                                        </a>
                                    @endif

                                    @if(!$return->isCompleted() && !$return->isCancelled())
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="cancelReturn({{ $return->id }})">
                                            Hủy hoàn
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        @if($returns->hasPages())
        <div class="card-footer bg-white">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>

<!-- ✅ INCLUDE MODAL MỚI -->
@include('hub.returns.partials.batch-assign-modal')

<!-- Cancel Return Modal -->
<div class="modal fade" id="cancelReturnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="cancelReturnForm">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Hủy hoàn hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Xác nhận hủy hoàn hàng?
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Xác nhận hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Select All Toggle
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.checked = !selectAllCheckbox.checked;
    
    const checkboxes = document.querySelectorAll('.return-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
}

document.getElementById('selectAllCheckbox')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.return-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Cancel Return
function cancelReturn(returnId) {
    const form = document.getElementById('cancelReturnForm');
    form.action = `/hub/returns/${returnId}/cancel`;
    new bootstrap.Modal(document.getElementById('cancelReturnModal')).show();
}
</script>
@endsection