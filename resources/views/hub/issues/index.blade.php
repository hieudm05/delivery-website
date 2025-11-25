@extends('hub.layouts.app')

@section('title', 'Quản lý vấn đề giao hàng')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-exclamation-triangle text-danger"></i> Quản lý vấn đề giao hàng</h4>
            <p class="text-muted small mb-0">Xử lý các vấn đề giao hàng thất bại và quyết định hành động</p>
        </div>
        <a href="{{ route('hub.returns.index') }}" class="btn btn-warning">
            <i class="fas fa-undo"></i> Quản lý hoàn hàng
        </a>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Chờ xử lý</h6>
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
                            <h6 class="text-white-50 mb-1">Đã quyết định thử lại</h6>
                            <h3 class="mb-0">{{ $stats['retry'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-redo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-gradient text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-dark-50 mb-1">Đã chuyển hoàn</h6>
                            <h3 class="mb-0">{{ $stats['return'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-undo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Đang giữ tại hub</h6>
                            <h3 class="mb-0">{{ $stats['hold'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-pause"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Trạng thái xử lý</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>
                            Chờ xử lý ({{ $stats['pending'] }})
                        </option>
                        <option value="retry" {{ $status == 'retry' ? 'selected' : '' }}>
                            Thử lại ({{ $stats['retry'] }})
                        </option>
                        <option value="return" {{ $status == 'return' ? 'selected' : '' }}>
                            Hoàn về ({{ $stats['return'] }})
                        </option>
                        <option value="hold_at_hub" {{ $status == 'hold_at_hub' ? 'selected' : '' }}>
                            Giữ tại hub ({{ $stats['hold'] }})
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold">Loại vấn đề</label>
                    <select name="issue_type" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        <option value="recipient_not_home" {{ $issueType == 'recipient_not_home' ? 'selected' : '' }}>
                            Người nhận không có nhà
                        </option>
                        <option value="wrong_address" {{ $issueType == 'wrong_address' ? 'selected' : '' }}>
                            Địa chỉ sai
                        </option>
                        <option value="refused_package" {{ $issueType == 'refused_package' ? 'selected' : '' }}>
                            Từ chối nhận
                        </option>
                        <option value="unable_to_contact" {{ $issueType == 'unable_to_contact' ? 'selected' : '' }}>
                            Không liên lạc được
                        </option>
                        <option value="address_too_far" {{ $issueType == 'address_too_far' ? 'selected' : '' }}>
                            Địa chỉ quá xa
                        </option>
                        <option value="dangerous_area" {{ $issueType == 'dangerous_area' ? 'selected' : '' }}>
                            Khu vực nguy hiểm
                        </option>
                        <option value="other" {{ $issueType == 'other' ? 'selected' : '' }}>
                            Lý do khác
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Mã đơn, người nhận..." 
                           value="{{ $search }}">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                    @if($stats['pending'] > 0)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#batchResolveModal">
                            <i class="fas fa-tasks"></i>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Issues List -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách vấn đề ({{ $issues->total() }})</h6>
                @if($issues->isNotEmpty() && $status == 'pending')
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                        <i class="fas fa-check-square"></i> Chọn tất cả
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($issues->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">Không có vấn đề nào cần xử lý</p>
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
                                <th>Người nhận</th>
                                <th>Loại vấn đề</th>
                                <th>Chi tiết</th>
                                <th>Tài xế báo cáo</th>
                                <th>Thời gian</th>
                                <th>Xử lý</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($issues as $issue)
                            <tr>
                                @if($status == 'pending')
                                    <td>
                                        <input type="checkbox" class="form-check-input issue-checkbox" 
                                               value="{{ $issue->id }}">
                                    </td>
                                @endif
                                <td>
                                    <strong class="text-primary">#{{ $issue->order->id }}</strong>
                                    @if($issue->order->delivery_attempt_count >= 2)
                                        <span class="badge bg-danger ms-1">
                                            {{ $issue->order->delivery_attempt_count }}x
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $issue->order->recipient_name }}</div>
                                    <small class="text-muted">{{ $issue->order->recipient_phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-danger">
                                        {{ $issue->issue_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <small style="max-width: 250px; display: block; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $issue->issue_note }}
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-user"></i> {{ $issue->reporter->name }}
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $issue->issue_time->format('H:i d/m') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $issue->issue_time->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $issue->resolution_badge }}">
                                        {{ $issue->resolution_action_label }}
                                    </span>
                                    @if($issue->isResolved())
                                        <br>
                                        <small class="text-muted">{{ $issue->resolved_at->format('d/m H:i') }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('hub.issues.show', $issue->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if(!$issue->isResolved())
                                        <button type="button" 
                                                class="btn btn-sm btn-success" 
                                                onclick="showResolveModal({{ $issue->id }}, '{{ $issue->order->id }}')"
                                                title="Xử lý">
                                            <i class="fas fa-check"></i>
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
        
        @if($issues->hasPages())
        <div class="card-footer bg-white">
            {{ $issues->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="resolveForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Xử lý vấn đề giao hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-box"></i>
                        Đơn hàng: <strong id="resolveOrderId"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Quyết định xử lý <span class="text-danger">*</span>
                        </label>
                        <select name="action" class="form-select" required>
                            <option value="">-- Chọn hành động --</option>
                            <option value="retry">
                                🔄 Thử giao lại (Giữ đơn tại hub, phân lại cho tài xế)
                            </option>
                            <option value="return">
                                📦 Hoàn về sender (Khởi tạo hoàn hàng)
                            </option>
                            <option value="hold_at_hub">
                                ⏸️ Giữ tại hub (Chờ xử lý thêm/liên hệ khách)
                            </option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3" 
                                  placeholder="Lý do quyết định, hướng xử lý..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Batch Resolve Modal -->
<div class="modal fade" id="batchResolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('hub.issues.batch-resolve') }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Xử lý hàng loạt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Đã chọn <strong id="selectedCount">0</strong> vấn đề
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hành động <span class="text-danger">*</span></label>
                        <select name="action" class="form-select" required>
                            <option value="">-- Chọn hành động --</option>
                            <option value="retry">Thử giao lại tất cả</option>
                            <option value="return">Hoàn về tất cả</option>
                            <option value="hold_at_hub">Giữ tất cả tại hub</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>

                    <input type="hidden" name="issue_ids" id="batchIssueIds">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Select All
function selectAll() {
    const checkboxes = document.querySelectorAll('.issue-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
    updateSelectedCount();
}

document.getElementById('selectAllCheckbox')?.addEventListener('change', selectAll);

// Update Selected Count
function updateSelectedCount() {
    const checked = document.querySelectorAll('.issue-checkbox:checked');
    document.getElementById('selectedCount').textContent = checked.length;
    
    const ids = Array.from(checked).map(cb => cb.value);
    document.getElementById('batchIssueIds').value = JSON.stringify(ids);
}

document.querySelectorAll('.issue-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

// Show Resolve Modal
function showResolveModal(issueId, orderId) {
    document.getElementById('resolveOrderId').textContent = '#' + orderId;
    document.getElementById('resolveForm').action = `/hub/issues/${issueId}/resolve`;
    new bootstrap.Modal(document.getElementById('resolveModal')).show();
}
</script>
@endsection