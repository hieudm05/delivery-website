@extends('customer.dashboard.layouts.app')
@section('title', 'Quản lý tiền COD')

@section('content')
    <div class="container">
      @if(isset($debtStats) && $debtStats['has_debt'])
    <div class="alert  border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-danger"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="alert-heading mb-2">
                    <i class="bi bi-wallet"></i> ⚠️ Bạn đang có công nợ chưa thanh toán
                </h5>
                <p class="mb-2">
                    <strong class="text-danger fs-5">Tổng nợ: {{ number_format($debtStats['total']) }}₫</strong>
                </p>
                
                <div class="mb-3">
                    <p class="mb-2"><strong>Chi tiết theo bưu cục:</strong></p>
                    <ul class="mb-0">
                        @foreach($debtStats['by_hub'] as $debt)
                            <li>
                                <strong>{{ $debt['hub_name'] }}</strong>: 
                                <span class="text-danger">{{ number_format($debt['amount']) }}₫</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="alert alert-info border-0 mb-0 mt-2">
                    <small>
                        <i class="bi bi-info-circle"></i> 
                        <strong>Lưu ý:</strong> Nợ sẽ được tự động trừ vào tiền COD của các đơn hàng tiếp theo. 
                        Bạn cũng có thể thanh toán trực tiếp cho bưu cục tại tab "Chờ thanh toán phí".
                    </small>
                </div>
            </div>
        </div>
    </div>
@endif
        <!-- ==================== THỐNG KÊ TỔNG QUAN ==================== -->
        <div class="row mb-4">
    <!-- Card 1: Phí đã khấu trừ -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                            <i class="bi bi-check-circle"></i> Phí đã khấu trừ
                        </p>
                        <h3 class="text-success fw-bold mb-0">
                            {{ number_format($stats['fee_deducted']) }}₫
                        </h3>
                    </div>
                    <div class="bg-opacity-10 text-success p-3" style="font-size: 1.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    {{ $stats['count_fee_deducted'] }} đơn có COD
                </small>
            </div>
        </div>
    </div>

    <!-- Card 2: Phí chờ thanh toán -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                            <i class="bi bi-exclamation-circle"></i> Phí chờ thanh toán
                        </p>
                        <h3 class="text-danger fw-bold mb-0">
                            {{ number_format($stats['pending_fee'] ?? 0) }}₫
                        </h3>
                    </div>
                    <div class="bg-opacity-10 text-danger p-3" style="font-size: 1.5rem;">
                        <i class="bi bi-credit-card"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    {{ $stats['count_pending_fee'] }} đơn (bao gồm phí hoàn hàng)
                </small>
            </div>
        </div>
    </div>

    <!-- Card 3: COD chờ nhận -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                            <i class="bi bi-hourglass"></i> COD chờ nhận
                        </p>
                        <h3 class="text-warning fw-bold mb-0">
                            {{ number_format($stats['waiting_cod'] ?? 0) }}₫
                        </h3>
                    </div>
                    <div class="bg-opacity-10 text-warning p-3" style="font-size: 1.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    {{ $stats['count_waiting_cod'] }} đơn đang xử lý
                </small>
            </div>
        </div>
    </div>

    <!-- Card 4: COD đã nhận -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase fw-bold mb-2" style="font-size: 0.75rem;">
                            <i class="bi bi-check-circle"></i> COD đã nhận
                        </p>
                        <h3 class="text-success fw-bold mb-0">
                            {{ number_format($stats['received'] ?? 0) }}₫
                        </h3>
                    </div>
                    <div class="bg-opacity-10 text-success p-3" style="font-size: 1.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    {{ $stats['count_received'] }} đơn hoàn tất
                </small>
            </div>
        </div>
    </div>
</div>

        <!-- ==================== DANH SÁCH GIAO DỊCH ==================== -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-wallet2"></i> Danh sách giao dịch COD
                        </h5>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('customer.cod.statistics') }}" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-graph-up"></i> Xem thống kê
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- ==================== TABS ==================== -->
                <ul class="nav nav-tabs mb-4 border-bottom-0" role="tablist">
                    <!-- Tab 1: Tất cả -->
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="?tab=all">
                            <i class="bi bi-list"></i> Tất cả
                            <span class="badge bg-secondary ms-2">{{ $stats['total_transactions'] }}</span>
                        </a>
                    </li>

                    <!-- Tab 2: Phí đã khấu trừ (có COD) -->
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'fee_deducted' ? 'active' : '' }}" href="?tab=fee_deducted">
                            <i class="bi bi-check-circle"></i> Phí đã khấu trừ
                            <span class="badge bg-success ms-2">{{ $stats['count_fee_deducted'] }}</span>
                        </a>
                    </li>

                    <!-- Tab 3: Phí chờ thanh toán (không COD) -->
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'pending_fee' ? 'active' : '' }}" href="?tab=pending_fee">
                            <i class="bi bi-clock-history"></i> Chờ thanh toán phí
                            <span class="badge bg-danger ms-2">{{ $stats['count_pending_fee'] }}</span>
                        </a>
                    </li>

                    <!-- Tab 4: Chờ nhận tiền COD -->
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'waiting_cod' ? 'active' : '' }}" href="?tab=waiting_cod">
                            <i class="bi bi-hourglass-split"></i> Chờ nhận tiền
                            <span class="badge bg-warning text-dark ms-2">{{ $stats['count_waiting_cod'] }}</span>
                        </a>
                    </li>

                    <!-- Tab 5: Đã nhận tiền -->
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'received' ? 'active' : '' }}" href="?tab=received">
                            <i class="bi bi-check-circle"></i> Đã nhận tiền
                            <span class="badge bg-success ms-2">{{ $stats['count_received'] }}</span>
                        </a>
                    </li>
                </ul>

                <!-- ==================== BẢNG DỮ LIỆU ==================== -->
                @if ($transactions->isEmpty())
                    <div class="alert alert-info border-0 mt-3" role="alert">
                        <i class="bi bi-info-circle"></i>
                        <strong>Không có giao dịch</strong> trong mục này
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 18%;">Đơn hàng</th>
                                    <th style="width: 14%;">Tiền COD</th>
                                    <th style="width: 14%;">Phí thanh toán</th>
                                    <th style="width: 14%;">Sẽ nhận</th>
                                    <th style="width: 32%;">Trạng thái</th>
                                    <th style="width: 8%;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                              <!-- resources/views/customer/dashboard/cod/index.blade.php -->

@foreach ($transactions as $trans)
    <tr>
        <!-- Cột 1: Đơn hàng -->
        <td>
            <div>
                <a href="{{ route('customer.orderManagent.show', $trans->order_id) }}"
                    class="fw-bold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-seam"></i> #{{ $trans->order_id }}
                </a>
            </div>
            <small class="text-muted">
                <i class="bi bi-calendar"></i>
                {{ $trans->created_at->format('d/m/Y H:i') }}
            </small>
        </td>

        <!-- Cột 2: Tiền COD -->
        <td>
            @if($trans->is_returned_order)
                <div class="d-flex flex-column gap-1">
                    <span class="badge bg-danger">
                        <i class="bi bi-x-circle"></i> Đơn đã hoàn
                    </span>
                    <small class="text-muted">Không thu được COD</small>
                </div>
            @else
                <span class="bg-opacity-10 text-primary px-3 py-2 fw-bold">
                    {{ number_format($trans->cod_amount) }}₫
                </span>
            @endif
        </td>

        <!-- Cột 3: Phí Thanh toán -->
        <td>
            @if($trans->is_returned_order)
                <div class="d-flex flex-column gap-1">
                    <span class="text-danger fw-bold">
                        {{ number_format($trans->sender_fee_paid) }}₫
                    </span>
                    <small class="text-danger">
                        <i class="bi bi-arrow-down-circle-fill"></i> Đã thành nợ
                    </small>
                </div>
            @elseif($trans->sender_fee_paid > 0)
                <div class="d-flex flex-column gap-1">
                    <span class="{{ $trans->cod_amount > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ number_format($trans->sender_fee_paid) }}₫
                    </span>

                    @if($trans->cod_amount > 0)
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i> Đã khấu trừ từ COD
                        </small>
                    @elseif($trans->sender_fee_paid_at)
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i> Đã thanh toán
                        </small>
                    @else
                        <small class="text-warning">
                            <i class="bi bi-clock-fill"></i> Chờ thanh toán
                        </small>
                    @endif
                </div>
            @else
                <span class="badge bg-secondary">Không có</span>
            @endif
        </td>

        <!-- Cột 4: Sẽ nhận -->
        <td>
            @if($trans->is_returned_order)
                <div class="alert alert-danger border-0 mb-0 p-2">
                    <small class="mb-0">
                        <i class="bi bi-x-octagon-fill"></i> 
                        <strong>Không nhận tiền</strong><br>
                        Phí hoàn đã thành nợ
                    </small>
                </div>
            @else
                <div class="d-flex flex-column gap-1">
                    <span class="badge bg-opacity-10 text-success px-3 py-2 fw-bold">
                        {{ number_format($trans->sender_receive_amount) }}₫
                    </span>
                    @if($trans->sender_debt_deducted > 0)
                        <small class="text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Trừ nợ: {{ number_format($trans->sender_debt_deducted) }}₫
                        </small>
                    @endif
                </div>
            @endif
        </td>

        <!-- Cột 5: Trạng thái -->
        <td>
            @if($trans->is_returned_order)
                <div class="alert alert-warning border-0 mb-0 p-2">
                    <small>
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Đơn đã hoàn về</strong><br>
                        Phí hoàn: {{ number_format($trans->sender_fee_paid) }}₫
                    </small>
                </div>
            @else
                <div class="d-flex flex-column gap-1">
                    {{-- Hiển thị trạng thái bình thường --}}
                    @if($trans->sender_fee_paid > 0)
                        @if($trans->cod_amount > 0)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Phí: ✓ Đã khấu trừ
                            </span>
                        @elseif($trans->sender_fee_paid_at)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Phí: ✓ Đã thanh toán
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-clock"></i> Phí: ⏳ Chờ thanh toán
                            </span>
                        @endif
                    @endif

                    @if($trans->sender_payment_status === 'pending')
                        <span class="badge bg-info">
                            <i class="bi bi-hourglass-split"></i> COD: ⏳ Chờ nhận
                        </span>
                    @elseif($trans->sender_payment_status === 'completed')
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> COD: ✓ Đã nhận
                        </span>
                    @endif
                </div>
            @endif
        </td>

        <!-- Cột 6: Hành động -->
        <td>
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('customer.cod.show', $trans->id) }}"
                    class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                </a>

                {{-- ✅ NÚT THANH TOÁN PHÍ: Chỉ khi KHÔNG hoàn + chưa trả + không có COD --}}
                @if(!$trans->is_returned_order && 
                    $trans->sender_fee_paid > 0 && 
                    !$trans->sender_fee_paid_at && 
                    $trans->cod_amount == 0)
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="openPayFeeModal({{ $trans->id }}, {{ $trans->order_id }}, {{ $trans->sender_fee_paid }}, '{{ $trans->payer_shipping }}')"
                        title="Thanh toán phí">
                        <i class="bi bi-credit-card"></i>
                    </button>
                @endif

                {{-- ✅ NÚT THANH TOÁN NỢ: Chỉ khi BỊ HOÀN + chưa trả nợ --}}
                @if($trans->is_returned_order && $trans->sender_fee_paid > 0)
                    @php
                        $currentDebt = \App\Models\SenderDebt::getTotalUnpaidDebt($trans->sender_id, $trans->hub_id);
                    @endphp
                    
                    @if($currentDebt > 0)
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="openPayDebtModal({{ $trans->id }}, {{ $trans->order_id }}, {{ $currentDebt }})"
                            title="Thanh toán nợ">
                            <i class="bi bi-wallet2"></i> Trả nợ
                        </button>
                    @endif
                @endif

                {{-- NÚT ƯU TIÊN: Chỉ khi KHÔNG hoàn + đang chờ COD --}}
                @if(!$trans->is_returned_order && 
                    $trans->sender_payment_status === 'pending' && 
                    $trans->sender_fee_paid_at)
                    <button type="button" class="btn btn-sm btn-outline-warning"
                        onclick="openPriorityModal({{ $trans->id }}, {{ $trans->order_id }})"
                        title="Yêu cầu ưu tiên">
                        <i class="bi bi-lightning"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== PAGINATION ==================== -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Hiển thị {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }}
                                trong tổng số {{ $transactions->total() }} giao dịch
                            </small>
                            <div>
                                {{ $transactions->appends(['tab' => $tab])->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </nav>
                @endif
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: THANH TOÁN PHÍ ==================== -->
    <div class="modal fade" id="payFeeModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form id="payFeeForm" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Header -->
                    <div class="modal-header bg-gradient text-white border-0"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-credit-card"></i> Thanh toán phí hệ thống
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <div class="row g-4">

                            <!-- CỘT TRÁI -->
                            <div class="col-lg-6">

                                <!-- THÔNG TIN GIAO DỊCH -->
                                <div class="alert alert-info border-0 mb-4"
                                    style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block mb-1">Đơn hàng</small>
                                            <h6 class="mb-0 fw-bold text-primary">#<span id="orderIdDisplay">---</span>
                                            </h6>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted d-block mb-1">Phí cần trả</small>
                                            <h6 class="mb-0 fw-bold text-danger" id="feeAmountDisplay">0₫</h6>
                                        </div>
                                    </div>
                                </div>

                                <!-- CHI TIẾT PHÍ -->
                                <div class="card border-light mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-list-check"></i> Chi tiết phí
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="feeBreakdown" class="space-y-2">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Đang tải...</span>
                                            </div>
                                        </div>

                                        <hr class="my-3">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong>Tổng cộng:</strong>
                                            <h5 class="mb-0 text-danger fw-bold" id="totalFeeDisplay">0₫</h5>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- CỘT PHẢI -->
                            <div class="col-lg-6">

                                <!-- PHƯƠNG THỨC THANH TOÁN -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-credit-card"></i> Phương thức thanh toán
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="payment_method" id="paymentMethodSelect"
                                        class="form-select form-select-lg" required>
                                        <option value="">-- Chọn phương thức --</option>
                                        <option value="bank_transfer">🏦 Chuyển khoản ngân hàng</option>
                                        <option value="wallet">📱 Ví điện tử (Momo, ZaloPay...)</option>
                                        <option value="cash">💵 Tiền mặt (tại bưu cục)</option>
                                    </select>
                                </div>

                                <!-- CHUYỂN KHOẢN -->
                                <div id="bankTransferSection" style="display: none;">
                                    <div class="card border-info mb-3">
                                        <div class="card-header bg-info bg-opacity-10 border-info">
                                            <h6 class="mb-0">
                                                <i class="bi bi-building"></i> Thông tin tài khoản Hub
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-5">
                                                    <small class="text-muted">Ngân hàng</small>
                                                    <p class="mb-0 fw-bold" id="hubBankName">Đang tải...</p>
                                                </div>
                                                <div class="col-7">
                                                    <small class="text-muted">Số tài khoản</small>
                                                    <p class="mb-0 fw-bold" id="hubAccountNumber">Đang tải...</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <small class="text-muted">Chủ tài khoản</small>
                                                    <p class="mb-0 fw-bold" id="hubAccountName">Đang tải...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="qrLoadingSpinner" class="text-center mb-3">
                                        <div class="spinner-border text-primary"></div>
                                        <p class="text-muted mt-2 mb-0">Đang tạo mã QR...</p>
                                    </div>

                                    <div id="qrCodeDisplay" class="text-center mb-4" style="display: none;">
                                        <div class="card border-primary shadow-sm">
                                            <div class="card-body p-3">
                                                <h6 class="card-title mb-3">
                                                    <i class="bi bi-qr-code"></i> Quét mã QR để chuyển khoản
                                                </h6>
                                                <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid"
                                                    style="max-width: 280px; border: 3px solid #0d6efd; border-radius: 12px; padding: 8px; background: white;">
                                                <p class="text-muted small mt-3 mb-0">
                                                    ✓ Mở app ngân hàng → Quét QR → Xác nhận thanh toán
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nội dung chuyển khoản</label>
                                        <div class="input-group">
                                            <input type="text" id="transferContent" class="form-control" readonly>
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="copyToClipboard()">
                                                <i class="bi bi-clipboard"></i> Sao chép
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-image"></i> Ảnh chứng từ chuyển khoản
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" id="proofInputBankTransfer" class="form-control"
                                            accept="image/*">
                                        <small class="text-muted">PNG, JPG, GIF - Tối đa 5MB</small>
                                    </div>

                                    <div class="alert alert-warning border-0 mb-0">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Lưu ý:</strong> Kiểm tra thông tin trước khi chuyển khoản
                                    </div>
                                </div>

                                <!-- VÍ ĐIỆN TỬ -->
                                <div id="walletSection" style="display: none;">
                                    <div class="alert alert-info border-0 mb-3">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Hướng dẫn:</strong> Chuyển khoản qua ví rồi upload ảnh
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="bi bi-image"></i> Ảnh chứng từ
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" id="proofInputWallet" class="form-control"
                                            accept="image/*">
                                    </div>
                                </div>

                                <!-- TIỀN MẶT -->
                                <div id="cashSection" style="display: none;">
                                    <div class="alert alert-warning border-0 mb-0">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Lưu ý:</strong> Đến bưu cục để thanh toán
                                    </div>
                                </div>

                                <!-- Cảnh báo chung -->
                                <div class="alert alert-danger border-0 mt-3 mb-0">
                                    <i class="bi bi-clock"></i>
                                    <strong>⏰ Hạn cuối:</strong> Thanh toán trong 24h
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- Footer -->
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Hủy
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Xác nhận đã thanh toán
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: YÊU CẦU ƯU TIÊN ==================== -->
    <div class="modal fade" id="priorityModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="priorityForm" method="POST">
                    @csrf

                    <!-- Header -->
                    <div class="modal-header bg-warning bg-opacity-10 border-warning"
                        style="border-bottom: 2px solid #ffc107;">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-lightning"></i> Yêu cầu xử lý ưu tiên
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 mb-3">
                            <strong>Đơn hàng:</strong> #<span id="priorityOrderIdDisplay">---</span>
                        </div>

                        <div class="alert alert-warning border-0 mb-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Thông tin:</strong> Yêu cầu sẽ được gửi tới bưu cục. Họ sẽ ưu tiên xử lý và liên hệ bạn
                            trong 24h
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-chat-dots"></i> Lý do yêu cầu
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason" id="priorityReason" class="form-control" rows="4"
                                placeholder="VD: Cần gấp tiền để chi trả cho nhân viên..." required></textarea>
                            <small class="text-muted">Tối đa 500 ký tự</small>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Hủy
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-lightning"></i> Gửi yêu cầu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Smooth transitions */
        .nav-link {
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #0d6efd !important;
        }

        .nav-link.active {
            border-bottom: 3px solid #0d6efd !important;
            color: #0d6efd !important;
        }

        .btn-group-sm .btn {
            padding: 0.4rem 0.6rem;
            font-size: 0.875rem;
        }

        /* Card hover effect */
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
        }

        /* Table row hover */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05) !important;
        }

        /* Badge styling */
        .badge {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Modal animation */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease;
            transform: scale(0.95) translateY(-50px);
        }

        .modal.show .modal-dialog {
            transform: scale(1) translateY(0);
        }

        /* QR Code styling */
        #qrCodeImage {
            transition: transform 0.2s ease;
            cursor: pointer;
        }

        #qrCodeImage:hover {
            transform: scale(1.05);
        }

        /* Form inputs */
        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Alert borders */
        .alert-info {
            border-left: 4px solid #0dcaf0;
        }

        .alert-warning {
            border-left: 4px solid #ffc107;
        }

        .alert-danger {
            border-left: 4px solid #dc3545;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal-lg {
                max-width: 95vw;
            }

            .btn-group-sm {
                flex-wrap: wrap;
                gap: 4px;
            }

            table {
                font-size: 0.875rem;
            }

            .table th {
                font-size: 0.75rem;
            }

            .d-flex.flex-wrap {
                gap: 4px;
            }
        }
    </style>

    <!-- ==================== JAVASCRIPT ==================== -->
   <script>
    let currentTransactionId = null;
let currentPayerShipping = null;
let isReturnOrder = false;

/**
 * ✅ MỞ MODAL THANH TOÁN - HỖ TRỢ CẢ ĐƠN THƯỜNG VÀ ĐƠN HOÀN VỀ
 */
function openPayFeeModal(transId, orderId, feeAmount, payerType) {
    currentTransactionId = transId;
    currentPayerShipping = payerType;
    isReturnOrder = (payerType === 'returned'); // ✅ PHÁT HIỆN ĐƠN HOÀN VỀ

    document.getElementById('payFeeForm').action = `/customer/cod/${transId}/pay-fee`;
    document.getElementById('orderIdDisplay').textContent = orderId;
    document.getElementById('feeAmountDisplay').textContent = number_format(feeAmount) + '₫';
    document.getElementById('totalFeeDisplay').textContent = number_format(feeAmount) + '₫';

    document.getElementById('paymentMethodSelect').value = '';
    hideAllPaymentSections();

    // ✅ TẢI CHI TIẾT PHÍ
    loadFeeDetails(transId);

    new bootstrap.Modal(document.getElementById('payFeeModal')).show();
}

/**
 * ✅ LOAD CHI TIẾT PHÍ - HỖ TRỢ CẢ 2 LOẠI ĐƠN
 */
function loadFeeDetails(transId) {
    const container = document.getElementById('feeBreakdown');
    container.innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="text-muted mt-2 mb-0 small">Đang tải chi tiết...</p>
        </div>
    `;

    fetch(`/customer/cod/${transId}/qr`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.feeData = data;
            displayFeeBreakdown(data.fee_breakdown, data.is_return_order);
        } else {
            container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        container.innerHTML = '<div class="alert alert-danger">Lỗi khi tải dữ liệu</div>';
    });
}

/**
 * ✅ HIỂN THỊ CHI TIẾT PHÍ - PHÂN BIỆT ĐƠN THƯỜNG VÀ ĐƠN HOÀN VỀ
 */
function displayFeeBreakdown(breakdown, isReturn) {
    const container = document.getElementById('feeBreakdown');
    container.innerHTML = '';
    let total = 0;

    if (isReturn) {
        // ✅ ĐƠN HOÀN VỀ - CHỈ HIỆN PHÍ HOÀN HÀNG
        if (breakdown.return_fee) {
            container.innerHTML = `
                <div class="alert alert-warning border-0 mb-3">
                    <i class="bi bi-arrow-return-left"></i>
                    <strong>Đơn hoàn về:</strong> Bạn không nhận được tiền COD từ đơn này.
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bi bi-box-arrow-left"></i> Phí hoàn hàng:</span>
                    <strong class="text-danger">${number_format(breakdown.return_fee)}₫</strong>
                </div>
            `;
            total = breakdown.return_fee;
        }
    } else {
        // ✅ ĐƠN THƯỜNG - HIỆN PHÍ COD + PHÍ SHIP (nếu có)
        if (breakdown.cod_fee) {
            container.innerHTML += `
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bi bi-box"></i> Phí COD:</span>
                    <strong class="text-primary">${number_format(breakdown.cod_fee)}₫</strong>
                </div>
            `;
            total += breakdown.cod_fee;
        }

        if (breakdown.shipping_fee) {
            container.innerHTML += `
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bi bi-truck"></i> Phí vận chuyển:</span>
                    <strong class="text-primary">${number_format(breakdown.shipping_fee)}₫</strong>
                </div>
            `;
            total += breakdown.shipping_fee;
        }
    }

    document.getElementById('totalFeeDisplay').textContent = number_format(total) + '₫';
}

/**
 * ✅ CHỌN PHƯƠNG THỨC THANH TOÁN
 */
document.getElementById('paymentMethodSelect').addEventListener('change', function() {
    const method = this.value;
    hideAllPaymentSections();

    if (method === 'bank_transfer') {
        document.getElementById('bankTransferSection').style.display = 'block';
        document.getElementById('proofInputBankTransfer').setAttribute('name', 'proof');
        document.getElementById('proofInputBankTransfer').required = true;
        loadQrCode();
    } else if (method === 'wallet') {
        document.getElementById('walletSection').style.display = 'block';
        document.getElementById('proofInputWallet').setAttribute('name', 'proof');
        document.getElementById('proofInputWallet').required = true;
    } else if (method === 'cash') {
        document.getElementById('cashSection').style.display = 'block';
        
        // ✅ HIỂN THỊ ORDER ID CHO TIỀN MẶT
        const orderId = document.getElementById('orderIdDisplay').textContent;
        const cashOrderIdSpan = document.getElementById('cashOrderId');
        if (cashOrderIdSpan) {
            cashOrderIdSpan.textContent = orderId;
        }
    }
});

/**
 * ✅ LOAD QR CODE
 */
function loadQrCode() {
    if (!currentTransactionId || !window.feeData) return;

    const data = window.feeData;
    document.getElementById('hubBankName').textContent = data.bank_info.bank_name;
    document.getElementById('hubAccountNumber').textContent = data.bank_info.account_number;
    document.getElementById('hubAccountName').textContent = data.bank_info.account_name;
    document.getElementById('transferContent').value = data.content;

    const image = document.getElementById('qrCodeImage');
    image.src = data.qr_url;
    image.onload = function() {
        document.getElementById('qrLoadingSpinner').style.display = 'none';
        document.getElementById('qrCodeDisplay').style.display = 'block';
    };
}

/**
 * ✅ ẨN TẤT CẢ SECTION THANH TOÁN
 */
function hideAllPaymentSections() {
    ['bankTransferSection', 'walletSection', 'cashSection', 'qrCodeDisplay'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });

    ['proofInputBankTransfer', 'proofInputWallet'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.removeAttribute('name');
            input.value = '';
            input.required = false;
        }
    });

    document.getElementById('qrLoadingSpinner').style.display = 'block';
}

/**
 * ✅ COPY NỘI DUNG CHUYỂN KHOẢN
 */
function copyTransferContent() {
    const content = document.getElementById('transferContent').value;
    navigator.clipboard.writeText(content).then(() => {
        alert('✅ Đã sao chép nội dung chuyển khoản');
    }).catch(() => {
        alert('❌ Không thể sao chép');
    });
}

/**
 * ✅ MỞ MODAL YÊU CẦU ƯU TIÊN
 */
function openPriorityModal(transId, orderId) {
    document.getElementById('priorityForm').action = `/customer/cod/${transId}/request-priority`;
    document.getElementById('priorityOrderIdDisplay').textContent = orderId;
    document.getElementById('priorityReason').value = '';
    new bootstrap.Modal(document.getElementById('priorityModal')).show();
}

/**
 * ✅ FORMAT SỐ TIỀN
 */
function number_format(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}

/**
 * ✅ RESET MODAL KHI ĐÓNG
 */
document.getElementById('payFeeModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('payFeeForm').reset();
    hideAllPaymentSections();
    currentTransactionId = null;
    currentPayerShipping = null;
    isReturnOrder = false;
    window.feeData = null;
});

document.getElementById('priorityModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('priorityForm').reset();
});
   </script>

<!-- ==================== MODAL: THANH TOÁN NỢ ==================== -->
<div class="modal fade" id="payDebtModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="payDebtForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-wallet2"></i> Thanh toán công nợ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-danger border-0 mb-4">
                        <h6 class="alert-heading">Tổng nợ hiện tại</h6>
                        <h3 class="mb-0" id="debtAmountDisplay">0₫</h3>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phương thức thanh toán</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">-- Chọn --</option>
                            <option value="bank_transfer">Chuyển khoản</option>
                            <option value="cash">Tiền mặt tại bưu cục</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Chứng từ thanh toán</label>
                        <input type="file" name="proof" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận thanh toán</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPayDebtModal(transId, orderId, debtAmount) {
    document.getElementById('payDebtForm').action = `/customer/cod/${transId}/pay-debt`;
    document.getElementById('debtAmountDisplay').textContent = number_format(debtAmount) + '₫';
    new bootstrap.Modal(document.getElementById('payDebtModal')).show();
}
</script>
@endsection