@extends('admin.layouts.app')
@section('title', 'Quản lý tiền COD')

@section('content')
<div class="container-fluid py-4">
    <!-- THỐNG KÊ TỔNG QUAN -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng COD
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($transactions->sum('cod_amount')) }}đ
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
                                {{ $transactions->where('shipper_payment_status', 'transferred')->count() }}
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
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Chờ trả sender
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->where('sender_payment_status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
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
                                Đã hoàn tất
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->where('sender_payment_status', 'completed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
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
                            <i class="bi bi-wallet2"></i> Quản lý tiền COD
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
                            <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="?tab=all">
                                <i class="bi bi-list"></i> Tất cả
                                <span class="badge bg-secondary ms-1">{{ $transactions->total() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'waiting_confirm' ? 'active' : '' }}" 
                               href="?tab=waiting_confirm">
                                <i class="bi bi-clock"></i> Chờ xác nhận
                                <span class="badge bg-warning ms-1">
                                    {{ $transactions->where('shipper_payment_status', 'transferred')->count() }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'pending_sender' ? 'active' : '' }}" 
                               href="?tab=pending_sender">
                                <i class="bi bi-hourglass-split"></i> Chờ trả sender
                                <span class="badge bg-info ms-1">
                                    {{ $transactions->where('sender_payment_status', 'pending')->count() }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'completed' ? 'active' : '' }}" 
                               href="?tab=completed">
                                <i class="bi bi-check-circle"></i> Đã hoàn tất
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
                                        <th>Shipper</th>
                                        <th>Người gửi</th>
                                        <th>Tiền COD</th>
                                        <th>Tổng thu</th>
                                        <th>Trạng thái Shipper</th>
                                        <th>Trạng thái Sender</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $trans)
                                    <tr>
                                        <td><strong class="text-primary">#{{ $trans->id }}</strong></td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $trans->order_id) }}" 
                                               class="text-decoration-none" 
                                               target="_blank">
                                                <i class="bi bi-box-seam"></i> Đơn #{{ $trans->order_id }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $trans->driver->full_name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-telephone"></i> {{ $trans->driver->phone ?? '' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $trans->sender->full_name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-telephone"></i> {{ $trans->sender->phone ?? '' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                <i class="bi bi-cash"></i> {{ number_format($trans->cod_amount) }}đ
                                            </strong>
                                        </td>
                                        <td>
                                            <strong class="text-primary">{{ number_format($trans->total_collected) }}đ</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $trans->shipper_payment_status === 'confirmed' ? 'success' : ($trans->shipper_payment_status === 'transferred' ? 'warning' : 'secondary') }}">
                                                {{ $trans->shipper_status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $trans->sender_payment_status === 'completed' ? 'success' : ($trans->sender_payment_status === 'pending' ? 'info' : 'secondary') }}">
                                                {{ $trans->sender_status_label }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.cod.show', $trans->id) }}" 
                                                   class="btn btn-outline-info"
                                                   title="Xem chi tiết">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if($trans->shipper_payment_status === 'transferred')
                                                    <button type="button" 
                                                            class="btn btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#confirmModal{{ $trans->id }}"
                                                            title="Xác nhận nhận tiền">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                @endif

                                                @if($trans->shipper_payment_status === 'confirmed' && $trans->sender_payment_status === 'pending')
                                                    <button type="button" 
                                                            class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#transferModal{{ $trans->id }}"
                                                            title="Chuyển tiền cho sender">
                                                        <i class="bi bi-send"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- MODAL XÁC NHẬN NHẬN TIỀN TỪ SHIPPER -->
                                    <div class="modal fade" id="confirmModal{{ $trans->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.cod.confirm', $trans->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-check-circle"></i> Xác nhận nhận tiền
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <h6 class="alert-heading">Thông tin giao dịch</h6>
                                                            <hr>
                                                            <p class="mb-2">
                                                                <strong>Số tiền:</strong> 
                                                                <span class="text-success fs-5">{{ number_format($trans->total_collected) }}đ</span>
                                                            </p>
                                                            <p class="mb-2">
                                                                <strong>Shipper:</strong> {{ $trans->driver->full_name ?? 'N/A' }}
                                                            </p>
                                                            <p class="mb-0">
                                                                <strong>Thời gian chuyển:</strong> 
                                                                {{ $trans->shipper_transfer_time ? $trans->shipper_transfer_time->format('d/m/Y H:i') : 'N/A' }}
                                                            </p>
                                                        </div>

                                                        @if($trans->shipper_transfer_proof)
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">
                                                                    <i class="bi bi-image"></i> Chứng từ shipper:
                                                                </label>
                                                                <div class="text-center">
                                                                    <img src="{{ asset('storage/' . $trans->shipper_transfer_proof) }}" 
                                                                         class="img-thumbnail" 
                                                                         style="max-height: 300px; cursor: pointer;"
                                                                         onclick="window.open(this.src, '_blank')">
                                                                </div>
                                                                <small class="text-muted">Click để xem ảnh lớn</small>
                                                            </div>
                                                        @endif

                                                        @if($trans->shipper_note)
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Ghi chú từ shipper:</label>
                                                                <div class="alert alert-secondary mb-0">
                                                                    {{ $trans->shipper_note }}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Ghi chú của bạn (nếu có)</label>
                                                            <textarea name="note" 
                                                                      class="form-control" 
                                                                      rows="3" 
                                                                      placeholder="VD: Đã nhận tiền mặt lúc 15:30..."></textarea>
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

                                    <!-- MODAL CHUYỂN TIỀN CHO SENDER -->
                                    <div class="modal fade" id="transferModal{{ $trans->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.cod.transfer', $trans->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-send"></i> Chuyển tiền cho người gửi
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <h6 class="alert-heading">Chi tiết thanh toán</h6>
                                                            <hr>
                                                            <p class="mb-2">
                                                                <strong>Người nhận:</strong> {{ $trans->sender->full_name ?? 'N/A' }}
                                                            </p>
                                                            <p class="mb-2">
                                                                <strong>Số điện thoại:</strong> {{ $trans->sender->phone ?? 'N/A' }}
                                                            </p>
                                                            <hr>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span>Số tiền COD:</span>
                                                                <strong class="text-success">{{ number_format($trans->cod_amount) }}đ</strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span>Phí nền tảng (2%):</span>
                                                                <strong class="text-danger">-{{ number_format($trans->cod_amount * 0.02) }}đ</strong>
                                                            </div>
                                                            <hr>
                                                            <div class="d-flex justify-content-between">
                                                                <span><strong>Số tiền chuyển:</strong></span>
                                                                <strong class="text-primary fs-4">{{ number_format($trans->cod_amount * 0.98) }}đ</strong>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">
                                                                Phương thức chuyển <span class="text-danger">*</span>
                                                            </label>
                                                            <select name="method" class="form-select" required>
                                                                <option value="">-- Chọn phương thức --</option>
                                                                <option value="bank_transfer">
                                                                    <i class="bi bi-bank"></i> Chuyển khoản ngân hàng
                                                                </option>
                                                                <option value="wallet">
                                                                    <i class="bi bi-wallet2"></i> Ví điện tử (Momo, ZaloPay...)
                                                                </option>
                                                                <option value="cash">
                                                                    <i class="bi bi-cash"></i> Tiền mặt
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">
                                                                <i class="bi bi-image"></i> Ảnh chứng từ
                                                            </label>
                                                            <input type="file" 
                                                                   name="proof" 
                                                                   class="form-control" 
                                                                   accept="image/*">
                                                            <small class="text-muted">
                                                                Tải lên ảnh chụp biên lai chuyển khoản (nếu có)
                                                            </small>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Ghi chú</label>
                                                            <textarea name="note" 
                                                                      class="form-control" 
                                                                      rows="3"
                                                                      placeholder="VD: Đã chuyển khoản vào STK xxx..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="bi bi-x-circle"></i> Hủy
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-send-check"></i> Xác nhận chuyển tiền
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
</style>

@endsection