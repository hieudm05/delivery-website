@extends('customer.dashboard.layouts.app')
@section('title', 'Quản lý tiền COD')

@section('content')
<div class="container">
    <!-- THỐNG KÊ TỔNG QUAN -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Phí cần thanh toán
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalFeeOwed) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                COD chờ nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalCodPending) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                COD đã nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalCodReceived) }}đ
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
                        <a href="{{ route('customer.cod.statistics') }}" class="btn btn-sm btn-info">
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
                            <a class="nav-link {{ $tab === 'pending_payment' ? 'active' : '' }}" 
                               href="?tab=pending_payment">
                                <i class="bi bi-credit-card"></i> Chờ thanh toán phí
                                <span class="badge bg-danger ms-1">
                                    {{ $transactions->where('sender_fee_paid_at', null)->count() }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'waiting_cod' ? 'active' : '' }}" 
                               href="?tab=waiting_cod">
                                <i class="bi bi-hourglass-split"></i> Chờ nhận COD
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'received' ? 'active' : '' }}" 
                               href="?tab=received">
                                <i class="bi bi-check-circle"></i> Đã nhận COD
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
                                        <th>Đơn hàng</th>
                                        <th>Tiền COD</th>
                                        <th>Phí cần trả</th>
                                        <th>Sẽ nhận</th>
                                        <th>Trạng thái phí</th>
                                        <th>Trạng thái COD</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $trans)
                                    <tr>
                                        <td>
                                            <a href="{{ route('customer.orderManagent.show', $trans->order_id) }}" 
                                               class="text-decoration-none">
                                                <i class="bi bi-box-seam"></i> Đơn #{{ $trans->order_id }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $trans->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                {{ number_format($trans->cod_amount) }}đ
                                            </strong>
                                        </td>
                                        <td>
                                            @if($trans->sender_fee_paid > 0)
                                                <strong class="text-danger">
                                                    {{ number_format($trans->sender_fee_paid) }}đ
                                                </strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                {{ number_format($trans->sender_receive_amount) }}đ
                                            </strong>
                                        </td>
                                        <td>
                                            @if($trans->sender_fee_paid_at)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Đã thanh toán
                                                </span>
                                            @elseif($trans->sender_fee_paid > 0)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> Chưa thanh toán
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Không có phí</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $trans->sender_payment_status === 'completed' ? 'success' : ($trans->sender_payment_status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ $trans->sender_status_label }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('customer.cod.show', $trans->id) }}" 
                                                   class="btn btn-outline-info"
                                                   title="Xem chi tiết">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                @if(!$trans->sender_fee_paid_at && $trans->sender_fee_paid > 0)
                                                    <button type="button" 
                                                            class="btn btn-outline-primary" 
                                                            onclick="openPayFeeModal({{ $trans->id }}, '{{ $trans->order_id }}', '{{ number_format($trans->sender_fee_paid) }}')"
                                                            title="Thanh toán phí">
                                                        <i class="bi bi-credit-card"></i>
                                                    </button>
                                                @endif

                                                @if($trans->sender_payment_status === 'pending')
                                                    <button type="button" 
                                                            class="btn btn-outline-warning" 
                                                            onclick="openPriorityModal({{ $trans->id }}, '{{ $trans->order_id }}')"
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

<!-- ============================================ -->
<!-- MODAL THANH TOÁN PHÍ - CHỈ 1 MODAL DUY NHẤT -->
<!-- ============================================ -->
<div class="modal fade" id="payFeeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="payFeeForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header  text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card"></i> Thanh toán phí hệ thống
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert ">
                        <h6 class="alert-heading">Thông tin thanh toán</h6>
                        <hr>
                        <p class="mb-2">
                            <strong>Đơn hàng:</strong> #<span id="orderIdDisplay"></span>
                        </p>
                        <p class="mb-2">
                            <strong>Số tiền cần thanh toán:</strong> 
                            <span class="text-danger fs-5" id="feeAmountDisplay"></span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Phương thức thanh toán <span class="text-danger">*</span>
                        </label>
                        <select name="payment_method" id="paymentMethodSelect" class="form-select" required>
                            <option value="">-- Chọn phương thức --</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                            <option value="wallet">Ví điện tử (Momo, ZaloPay...)</option>
                            <option value="cash">Tiền mặt (tại bưu cục)</option>
                        </select>
                    </div>

                    <!-- ✅ SECTION: CHUYỂN KHOẢN NGÂN HÀNG -->
                    <div id="bankTransferSection" style="display: none;">
                        <div id="hubBankInfo" class="alert alert-info">
                            <h6 class="alert-heading">📌 Thông tin tài khoản Hub</h6>
                            <hr>
                            <p class="mb-1"><strong>Ngân hàng:</strong> <span id="hubBankName">Đang tải...</span></p>
                            <p class="mb-1"><strong>Số TK:</strong> <span id="hubAccountNumber">Đang tải...</span></p>
                            <p class="mb-1"><strong>Chủ TK:</strong> <span id="hubAccountName">Đang tải...</span></p>
                            <hr>
                            <p class="mb-0"><strong>Nội dung CK:</strong></p>
                            <code id="transferContent" class="d-block bg-white p-2 rounded">Đang tải...</code>
                        </div>

                        <!-- QR CODE DISPLAY -->
                        <div id="qrCodeDisplay" class="text-center mb-3" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">📱 Quét mã QR để chuyển khoản</h6>
                                    <img id="qrCodeImage" 
                                         src="" 
                                         alt="QR Code" 
                                         class="img-fluid" 
                                         style="max-width: 300px; border: 2px solid #0d6efd; border-radius: 8px; padding: 10px;">
                                    <p class="text-muted small mt-2 mb-0">
                                        Mở app ngân hàng → Quét mã QR → Xác nhận thanh toán
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div id="qrLoadingSpinner" class="text-center my-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải QR Code...</span>
                            </div>
                            <p class="text-muted mt-2">Đang tải mã QR...</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-image"></i> Ảnh chứng từ chuyển khoản <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   name="proof" 
                                   id="proofInputBankTransfer"
                                   class="form-control" 
                                   accept="image/*">
                            <small class="text-muted">
                                Tải lên ảnh chụp biên lai chuyển khoản (JPG, PNG, GIF - Max 5MB)
                            </small>
                        </div>
                    </div>

                    <!-- ✅ SECTION: VÍ ĐIỆN TỬ -->
                    <div id="walletSection" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="bi bi-wallet2"></i> Vui lòng chuyển khoản qua ví điện tử rồi upload ảnh chứng từ
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-image"></i> Ảnh chứng từ <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   name="proof" 
                                   id="proofInputWallet"
                                   class="form-control" 
                                   accept="image/*">
                            <small class="text-muted">
                                Ảnh chụp lịch sử giao dịch từ ví điện tử
                            </small>
                        </div>
                    </div>

                    <!-- ✅ SECTION: TIỀN MẶT -->
                    <div id="cashSection" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Vui lòng đến bưu cục để thanh toán tiền mặt trực tiếp
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <strong>⚠️ Lưu ý:</strong> Vui lòng thanh toán trong 24h để đơn hàng được xử lý nhanh nhất.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Xác nhận đã thanh toán
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL YÊU CẦU ƯU TIÊN - CHỈ 1 MODAL DUY NHẤT -->
<!-- ============================================ -->
<div class="modal fade" id="priorityModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="priorityForm" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="bi bi-lightning"></i> Yêu cầu xử lý ưu tiên
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Đơn hàng:</strong> #<span id="priorityOrderIdDisplay"></span>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Lưu ý:</strong> Yêu cầu ưu tiên sẽ được gửi đến bưu cục. Họ sẽ liên hệ với bạn sớm nhất.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Lý do yêu cầu ưu tiên <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" 
                                  id="priorityReason"
                                  class="form-control" 
                                  rows="3" 
                                  required
                                  placeholder="VD: Cần gấp tiền để chi trả..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
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
.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
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
// Hàm mở modal thanh toán phí
function openPayFeeModal(transId, orderId, feeAmount) {
    const form = document.getElementById('payFeeForm');
    form.action = "{{ url('customer/cod') }}/" + transId + "/pay-fee";
    
    document.getElementById('orderIdDisplay').textContent = orderId;
    document.getElementById('feeAmountDisplay').textContent = feeAmount + 'đ';
    
    // Reset form trước khi mở
    form.querySelector('select[name="payment_method"]').value = '';
    form.querySelector('input[name="proof"]').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('payFeeModal'));
    modal.show();
}

// Hàm mở modal yêu cầu ưu tiên
function openPriorityModal(transId, orderId) {
    const form = document.getElementById('priorityForm');
    form.action = "{{ url('customer/cod') }}/" + transId + "/request-priority";
    
    document.getElementById('priorityOrderIdDisplay').textContent = orderId;
    
    // Reset form trước khi mở
    document.getElementById('priorityReason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('priorityModal'));
    modal.show();
}

// Reset form khi đóng modal
document.getElementById('payFeeModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('payFeeForm').reset();
});

document.getElementById('priorityModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('priorityForm').reset();
});
</script>

<script>
let currentTransactionId = null;

// Hàm mở modal thanh toán phí
function openPayFeeModal(transId, orderId, feeAmount) {
    currentTransactionId = transId;
    
    const form = document.getElementById('payFeeForm');
    form.action = "{{ url('customer/cod') }}/" + transId + "/pay-fee";
    
    document.getElementById('orderIdDisplay').textContent = orderId;
    document.getElementById('feeAmountDisplay').textContent = feeAmount + 'đ';
    
    // Reset form trước khi mở
    form.querySelector('#paymentMethodSelect').value = '';
    hideAllPaymentSections();
    
    const modal = new bootstrap.Modal(document.getElementById('payFeeModal'));
    modal.show();
}

// Hàm ẩn tất cả sections
function hideAllPaymentSections() {
    document.getElementById('bankTransferSection').style.display = 'none';
    document.getElementById('walletSection').style.display = 'none';
    document.getElementById('cashSection').style.display = 'none';
    document.getElementById('qrCodeDisplay').style.display = 'none';
    document.getElementById('qrLoadingSpinner').style.display = 'none';
    
    // Remove name attribute to prevent multiple file inputs
    const proofBank = document.getElementById('proofInputBankTransfer');
    const proofWallet = document.getElementById('proofInputWallet');
    
    if (proofBank) {
        proofBank.removeAttribute('name');
        proofBank.value = '';
        proofBank.required = false;
    }
    if (proofWallet) {
        proofWallet.removeAttribute('name');
        proofWallet.value = '';
        proofWallet.required = false;
    }
}

// Hàm load QR code
function loadQrCode() {
    if (!currentTransactionId) {
        console.error('No transaction ID');
        return;
    }

    console.log('Loading QR code for transaction:', currentTransactionId);
    
    const qrDisplay = document.getElementById('qrCodeDisplay');
    const qrImage = document.getElementById('qrCodeImage');
    const qrSpinner = document.getElementById('qrLoadingSpinner');
    
    // Show loading spinner
    qrSpinner.style.display = 'block';
    qrDisplay.style.display = 'none';

    fetch(`{{ url('customer/cod') }}/${currentTransactionId}/qr`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log('QR Response:', data);
        
        if (data.error) {
            alert('❌ ' + data.error);
            qrSpinner.style.display = 'none';
            return;
        }
        
        if (data.qr_url) {
            // Update bank info
            document.getElementById('hubBankName').textContent = data.bank_name;
            document.getElementById('hubAccountNumber').textContent = data.account_number;
            document.getElementById('hubAccountName').textContent = data.account_name;
            document.getElementById('transferContent').textContent = data.content;
            
            // Show QR code
            qrImage.src = data.qr_url;
            qrSpinner.style.display = 'none';
            qrDisplay.style.display = 'block';
        } else {
            console.error('No QR URL in response');
            qrSpinner.style.display = 'none';
            alert('❌ Không thể tải mã QR. Vui lòng thử lại!');
        }
    })
    .catch(err => {
        console.error('Error loading QR:', err);
        qrSpinner.style.display = 'none';
        alert('❌ Lỗi kết nối. Vui lòng thử lại!');
    });
}

// Event listener cho payment method
document.getElementById('paymentMethodSelect').addEventListener('change', function() {
    const method = this.value;
    console.log('Payment method changed to:', method);
    
    hideAllPaymentSections();
    
    const proofBank = document.getElementById('proofInputBankTransfer');
    const proofWallet = document.getElementById('proofInputWallet');
    
    if (method === 'bank_transfer') {
        document.getElementById('bankTransferSection').style.display = 'block';
        proofBank.setAttribute('name', 'proof');
        proofBank.required = true;
        loadQrCode();
    } else if (method === 'wallet') {
        document.getElementById('walletSection').style.display = 'block';
        proofWallet.setAttribute('name', 'proof');
        proofWallet.required = true;
    } else if (method === 'cash') {
        document.getElementById('cashSection').style.display = 'block';
    }
});

// Reset form khi đóng modal
document.getElementById('payFeeModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('payFeeForm').reset();
    hideAllPaymentSections();
    currentTransactionId = null;
});
</script>

<style>
#qrCodeDisplay .card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

#qrCodeDisplay .card-body {
    padding: 1.5rem;
}

#qrCodeImage {
    background: white;
    transition: transform 0.2s;
}

#qrCodeImage:hover {
    transform: scale(1.02);
}

#hubBankInfo {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border: none;
    border-radius: 8px;
}

#transferContent {
    background: white !important;
    border: 2px dashed #0d6efd;
    font-size: 14px;
    font-weight: bold;
    color: #0d6efd;
}
</style>

@endsection