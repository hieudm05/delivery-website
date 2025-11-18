@extends('admin.layouts.app')
@section('title', 'Quản lý Platform Fee (Admin)')

@section('content')
<div class="container-fluid py-4">
    <!-- THỐNG KÊ TỔNG QUAN -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tổng Platform Fee
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($transactions->sum('hub_system_amount')) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ xác nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->where('hub_system_status', 'transferred')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->where('hub_system_status', 'confirmed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Số tiền đã nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($transactions->where('hub_system_status', 'confirmed')->sum('hub_system_amount')) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-wallet2 fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG GIAO DỊCH -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-bank"></i> Quản lý Platform Fee
                        </h5>
                        <a href="{{ route('admin.cod.statistics') }}" class="btn btn-sm btn-info">
                            <i class="bi bi-graph-up"></i> Thống kê chi tiết
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- TABS -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'waiting_system_confirm' ? 'active' : '' }}" 
                               href="?tab=waiting_system_confirm">
                                <i class="bi bi-clock"></i> Chờ xác nhận
                                <span class="badge bg-warning ms-1">
                                    {{ $transactions->where('hub_system_status', 'transferred')->count() }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'system_confirmed' ? 'active' : '' }}" 
                               href="?tab=system_confirmed">
                                <i class="bi bi-check-circle"></i> Đã xác nhận
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="?tab=all">
                                <i class="bi bi-list"></i> Tất cả
                                <span class="badge bg-secondary ms-1">{{ $transactions->total() }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- BẢNG DỮ LIỆU -->
                    @if($transactions->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Không có giao dịch nào trong mục này
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#ID</th>
                                        <th>Đơn hàng</th>
                                        <th>Hub</th>
                                        <th>Platform Fee</th>
                                        <th>Trạng thái Hub→System</th>
                                        <th>Thời gian chuyển</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $trans)
                                    <tr>
                                        <td><strong class="text-primary">#{{ $trans->id }}</strong></td>
                                        <td>
                                            <a href="#" 
                                               class="text-decoration-none">
                                                <i class="bi bi-box-seam"></i> Đơn #{{ $trans->order_id }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $trans->hub->full_name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-telephone"></i> {{ $trans->hub->phone ?? '' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                <i class="bi bi-cash"></i> {{ number_format($trans->hub_system_amount) }}đ
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $trans->hub_system_status === 'confirmed' ? 'success' : ($trans->hub_system_status === 'transferred' ? 'warning' : 'secondary') }}">
                                                {{ $trans->system_status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($trans->hub_system_transfer_time)
                                                {{ $trans->hub_system_transfer_time->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-muted">Chưa chuyển</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.cod.show', $trans->id) }}" 
                                                   class="btn btn-outline-info"
                                                   title="Xem chi tiết">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($trans->hub_system_status === 'transferred')
                                                    <button type="button" 
                                                            class="btn btn-outline-success" 
                                                            onclick="openConfirmSystemModal({{ $trans->id }}, '{{ $trans->hub->full_name ?? 'N/A' }}', '{{ number_format($trans->hub_system_amount) }}', '{{ $trans->hub_system_method }}', '{{ $trans->hub_system_transfer_time ? $trans->hub_system_transfer_time->format('d/m/Y H:i') : '' }}', '{{ $trans->hub_system_proof ? asset('storage/' . $trans->hub_system_proof) : '' }}', '{{ addslashes($trans->hub_system_note ?? '') }}')"
                                                            title="Xác nhận đã nhận">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>

                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="openDisputeModal({{ $trans->id }})"
                                                            title="Báo tranh chấp">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->
                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Hiển thị {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} 
                                trong tổng số {{ $transactions->total() }} giao dịch
                            </div>
                            <div>
                                {{ $transactions->appends(['tab' => $tab])->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL XÁC NHẬN NHẬN PLATFORM FEE - DUY NHẤT -->
<!-- ============================================== -->
<div class="modal fade" id="confirmSystemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="confirmSystemForm" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Xác nhận nhận Platform Fee
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Thông tin chuyển tiền</h6>
                        <hr>
                        <p class="mb-2">
                            <strong>Hub:</strong> <span id="hubNameDisplay"></span>
                        </p>
                        <p class="mb-2">
                            <strong>Số tiền Platform Fee:</strong> 
                            <span class="text-success fs-5" id="feeAmountDisplay"></span>
                        </p>
                        <p class="mb-2">
                            <strong>Phương thức:</strong> <span id="paymentMethodDisplay"></span>
                        </p>
                        <p class="mb-0">
                            <strong>Thời gian chuyển:</strong> <span id="transferTimeDisplay"></span>
                        </p>
                    </div>

                    <div class="mb-3" id="proofImageContainer" style="display: none;">
                        <label class="form-label fw-bold">
                            <i class="bi bi-image"></i> Chứng từ Hub:
                        </label>
                        <div class="text-center">
                            <img id="proofImage" 
                                 class="img-thumbnail" 
                                 style="max-height: 300px; cursor: pointer;"
                                 onclick="window.open(this.src, '_blank')">
                        </div>
                        <small class="text-muted">Click để xem ảnh lớn</small>
                    </div>

                    <div class="mb-3" id="hubNoteContainer" style="display: none;">
                        <label class="form-label fw-bold">Ghi chú từ Hub:</label>
                        <div class="alert alert-secondary mb-0" id="hubNoteDisplay"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú của bạn (nếu có)</label>
                        <textarea name="note" 
                                  id="adminNote"
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="VD: Đã kiểm tra và xác nhận nhận đủ tiền..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Xác nhận đã nhận tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================= -->
<!-- MODAL TRANH CHẤP - DUY NHẤT -->
<!-- ========================================= -->
<div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="disputeForm" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Báo tranh chấp
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Lưu ý:</strong> Chức năng này chỉ dùng khi có vấn đề với giao dịch.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Lý do tranh chấp <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" 
                                  id="disputeReason"
                                  class="form-control" 
                                  rows="4" 
                                  required
                                  placeholder="VD: Số tiền không đúng, chứng từ không hợp lệ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-exclamation-triangle"></i> Xác nhận tranh chấp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}

/* FIX MODAL NHẤP NHÁY */
.modal {
    pointer-events: none;
}
.modal.show {
    pointer-events: auto;
}
.modal.fade .modal-dialog {
    transition: transform 0.15s ease-out;
    transform: translate(0, -50px);
}
.modal.show .modal-dialog {
    transform: none;
}
</style>

<script>
// Hàm mở modal xác nhận Platform Fee
function openConfirmSystemModal(transId, hubName, feeAmount, paymentMethod, transferTime, proofUrl, hubNote) {
    const form = document.getElementById('confirmSystemForm');
    form.action = "{{ url('admin/cod') }}/" + transId + "/confirm-system";
    
    // Cập nhật thông tin
    document.getElementById('hubNameDisplay').textContent = hubName;
    document.getElementById('feeAmountDisplay').textContent = feeAmount + 'đ';
    document.getElementById('transferTimeDisplay').textContent = transferTime;
    
    // Hiển thị phương thức thanh toán
    let methodText = '';
    switch(paymentMethod) {
        case 'bank_transfer':
            methodText = 'Chuyển khoản';
            break;
        case 'wallet':
            methodText = 'Ví điện tử';
            break;
        case 'cash':
            methodText = 'Tiền mặt';
            break;
        default:
            methodText = paymentMethod;
    }
    document.getElementById('paymentMethodDisplay').textContent = methodText;
    
    // Hiển thị chứng từ nếu có
    if (proofUrl) {
        document.getElementById('proofImage').src = proofUrl;
        document.getElementById('proofImageContainer').style.display = 'block';
    } else {
        document.getElementById('proofImageContainer').style.display = 'none';
    }
    
    // Hiển thị ghi chú từ Hub nếu có
    if (hubNote) {
        document.getElementById('hubNoteDisplay').textContent = hubNote;
        document.getElementById('hubNoteContainer').style.display = 'block';
    } else {
        document.getElementById('hubNoteContainer').style.display = 'none';
    }
    
    // Reset ghi chú admin
    document.getElementById('adminNote').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('confirmSystemModal'));
    modal.show();
}

// Hàm mở modal tranh chấp
function openDisputeModal(transId) {
    const form = document.getElementById('disputeForm');
    form.action = "{{ url('admin/cod') }}/" + transId + "/dispute-system";
    
    // Reset textarea
    document.getElementById('disputeReason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('disputeModal'));
    modal.show();
}

// Reset form khi đóng modal
document.getElementById('confirmSystemModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('confirmSystemForm').reset();
    document.getElementById('proofImageContainer').style.display = 'none';
    document.getElementById('hubNoteContainer').style.display = 'none';
});

document.getElementById('disputeModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('disputeForm').reset();
});
</script>

@endsection