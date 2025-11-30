@extends('customer.dashboard.layouts.app')
@section('title', 'Chi tiết giao dịch COD #' . $transaction->order_id)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('customer.cod.index') }}">Quản lý COD</a></li>
                    <li class="breadcrumb-item active">Chi tiết giao dịch</li>
                </ol>
            </nav>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-receipt"></i> Giao dịch COD - Đơn #{{ $transaction->order_id }}
            </h4>
        </div>
        <a href="{{ route('customer.cod.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row g-4">
        <!-- CỘT TRÁI: Thông tin chính -->
        <div class="col-lg-8">
            
            <!-- Card 1: Tổng quan giao dịch -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient text-white border-0" 
                     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle"></i> Tổng quan giao dịch
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Mã giao dịch</label>
                            <p class="fw-bold mb-0">#{{ $transaction->id }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Đơn hàng</label>
                            <p class="mb-0">
                                <a href="{{ route('customer.orderManagent.show', $transaction->order_id) }}" 
                                   class="fw-bold text-primary">
                                    #{{ $transaction->order_id }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Thời gian tạo</label>
                            <p class="fw-bold mb-0">
                                <i class="bi bi-calendar"></i> 
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Tài xế giao hàng</label>
                            <p class="fw-bold mb-0">
                                @if($transaction->driver)
                                    <i class="bi bi-person-badge"></i> 
                                    {{ $transaction->driver->full_name }}
                                @else
                                    <span class="text-muted">Chưa xác định</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Bưu cục xử lý</label>
                            <p class="fw-bold mb-0">
                                @if($transaction->hub)
                                    <i class="bi bi-building"></i> 
                                    {{ $transaction->hub->full_name }}
                                @else
                                    <span class="text-muted">Chưa xác định</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Người trả phí ship</label>
                            <p class="mb-0">
                                @if($paymentDetails['payer_shipping'] === 'Người gửi')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-person-up"></i> Người gửi
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="bi bi-person-down"></i> Người nhận
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Chi tiết tiền -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-calculator"></i> Chi tiết tiền COD
                    </h6>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">
                                    <i class="bi bi-wallet2"></i> Tiền COD thu được
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary fs-5">
                                        {{ number_format($paymentDetails['cod_amount']) }}₫
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr class="my-2"></td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-3">
                                    <small><i class="bi bi-dash-circle"></i> Phí nền tảng</small>
                                </td>
                                <td class="text-end text-danger">
                                    -{{ number_format($paymentDetails['fee_breakdown']['platform_fee'] ?? 0) }}₫
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-3">
                                    <small><i class="bi bi-dash-circle"></i> Phí COD</small>
                                </td>
                                <td class="text-end text-danger">
                                    -{{ number_format($paymentDetails['fee_breakdown']['cod_fee'] ?? 0) }}₫
                                </td>
                            </tr>
                            @if(isset($paymentDetails['fee_breakdown']['shipping_fee']))
                            <tr>
                                <td class="text-muted ps-3">
                                    <small><i class="bi bi-dash-circle"></i> Phí vận chuyển</small>
                                </td>
                                <td class="text-end text-danger">
                                    -{{ number_format($paymentDetails['fee_breakdown']['shipping_fee']) }}₫
                                </td>
                            </tr>
                            @endif
                            
                            @if($paymentDetails['debt_deducted'] > 0)
                            <tr>
                                <td class="text-muted ps-3">
                                    <small>
                                        <i class="bi bi-arrow-down-circle text-info"></i> 
                                        Trừ nợ cũ (tự động)
                                    </small>
                                </td>
                                <td class="text-end text-info">
                                    -{{ number_format($paymentDetails['debt_deducted']) }}₫
                                </td>
                            </tr>
                            @endif
                            
                            <tr class="table-light">
                                <td colspan="2"><hr class="my-2"></td>
                            </tr>
                            <tr class="table-active">
                                <td class="fw-bold">
                                    <i class="bi bi-cash-coin"></i> Bạn sẽ nhận được
                                </td>
                                <td class="text-end">
                                    <h4 class="text-success fw-bold mb-0">
                                        {{ number_format($paymentDetails['will_receive']) }}₫
                                    </h4>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 3: Lịch sử hoạt động -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history"></i> Lịch sử hoạt động
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="timeline p-4">
                        <!-- Tạo giao dịch -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Tạo giao dịch COD</h6>
                                <small class="text-muted">
                                    {{ $transaction->created_at->format('d/m/Y H:i:s') }}
                                </small>
                            </div>
                        </div>

                        <!-- Tài xế chuyển tiền -->
                        @if($transaction->shipper_transfer_time)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Tài xế đã chuyển tiền cho Hub</h6>
                                <small class="text-muted">
                                    {{ $transaction->shipper_transfer_time->format('d/m/Y H:i:s') }}
                                </small>
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-info">
                                        {{ number_format($transaction->total_collected) }}₫
                                    </span>
                                </p>
                            </div>
                        </div>
                        @endif

                        <!-- Hub xác nhận -->
                        @if($transaction->hub_confirm_time)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Hub đã xác nhận nhận tiền</h6>
                                <small class="text-muted">
                                    {{ $transaction->hub_confirm_time->format('d/m/Y H:i:s') }}
                                </small>
                                @if($transaction->hubConfirmer)
                                <p class="mb-0 mt-1">
                                    <small>Bởi: {{ $transaction->hubConfirmer->full_name }}</small>
                                </p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Customer thanh toán phí -->
                        @if($paymentDetails['fee_status']['is_paid'])
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">
                                    @if($paymentDetails['debt_deducted'] > 0)
                                        Phí đã được trừ tự động từ nợ
                                    @else
                                        Bạn đã thanh toán phí
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    {{ $paymentDetails['fee_status']['paid_at']->format('d/m/Y H:i:s') }}
                                </small>
                                @if($paymentDetails['fee_status']['method'])
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-warning text-dark">
                                        {{ ucfirst($paymentDetails['fee_status']['method']) }}
                                    </span>
                                </p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Hub chuyển tiền cho customer -->
                        @if($transaction->sender_transfer_time)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Hub đã chuyển tiền COD cho bạn</h6>
                                <small class="text-muted">
                                    {{ $transaction->sender_transfer_time->format('d/m/Y H:i:s') }}
                                </small>
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-success">
                                        {{ number_format($transaction->sender_receive_amount) }}₫
                                    </span>
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- CỘT PHẢI: Trạng thái & Hành động -->
        <div class="col-lg-4">
            
            <!-- Card: Trạng thái -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-flag"></i> Trạng thái hiện tại
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-2">Trạng thái phí</label>
                        @if($paymentDetails['debt_deducted'] > 0)
                            <span class="badge bg-info fs-6">
                                <i class="bi bi-arrow-down-circle"></i> Đã trừ nợ tự động
                            </span>
                        @elseif($paymentDetails['fee_status']['is_paid'])
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle"></i> Đã thanh toán
                            </span>
                        @elseif($transaction->sender_fee_paid > 0)
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="bi bi-clock"></i> Chờ thanh toán
                            </span>
                        @else
                            <span class="badge bg-secondary fs-6">
                                <i class="bi bi-dash-circle"></i> Không có phí
                            </span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small d-block mb-2">Trạng thái COD</label>
                        @if($transaction->sender_payment_status === 'completed')
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle"></i> Đã nhận tiền
                            </span>
                        @elseif($transaction->sender_payment_status === 'pending')
                            <span class="badge bg-info fs-6">
                                <i class="bi bi-hourglass-split"></i> Chờ Hub chuyển
                            </span>
                        @else
                            <span class="badge bg-secondary fs-6">
                                {{ ucfirst($transaction->sender_payment_status) }}
                            </span>
                        @endif
                    </div>

                    @if($transaction->sender_note)
                    <div class="alert alert-info border-0 mb-0 mt-3">
                        <small>
                            <strong><i class="bi bi-info-circle"></i> Ghi chú:</strong><br>
                            {{ $transaction->sender_note }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card: Hành động -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-gear"></i> Hành động
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Thanh toán phí -->
                    @php
                        $needPayment = !$transaction->sender_fee_paid_at 
                            && $transaction->sender_fee_paid > 0 
                            && $transaction->sender_debt_deducted == 0;
                    @endphp
                    
                    @if($needPayment)
                        <button type="button" 
                                class="btn btn-danger w-100 mb-2"
                                onclick="openPayFeeModal({{ $transaction->id }}, {{ $transaction->order_id }}, {{ $transaction->sender_fee_paid }}, '{{ $transaction->payer_shipping }}')">
                            <i class="bi bi-credit-card"></i> Thanh toán phí
                        </button>
                    @endif

                    <!-- Yêu cầu ưu tiên -->
                    @if($transaction->sender_payment_status === 'pending' && 
                        ($transaction->sender_fee_paid_at || $transaction->sender_debt_deducted > 0))
                        <button type="button" 
                                class="btn btn-warning w-100 mb-2"
                                onclick="openPriorityModal({{ $transaction->id }}, {{ $transaction->order_id }})">
                            <i class="bi bi-lightning"></i> Yêu cầu ưu tiên
                        </button>
                    @endif

                    <!-- In biên lai -->
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()">
                        <i class="bi bi-printer"></i> In biên lai
                    </button>
                </div>
            </div>

            <!-- Card: Thông tin liên hệ -->
            @if($transaction->hub)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-telephone"></i> Liên hệ hỗ trợ
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Bưu cục:</strong><br>
                        {{ $transaction->hub->full_name }}
                    </p>
                    @if($transaction->hub->phone_number)
                    <p class="mb-2">
                        <strong>Hotline:</strong><br>
                        <a href="tel:{{ $transaction->hub->phone_number }}" class="text-primary">
                            <i class="bi bi-telephone"></i> {{ $transaction->hub->phone_number }}
                        </a>
                    </p>
                    @endif
                    @if($transaction->hub->email)
                    <p class="mb-0">
                        <strong>Email:</strong><br>
                        <a href="mailto:{{ $transaction->hub->email }}" class="text-primary">
                            <i class="bi bi-envelope"></i> {{ $transaction->hub->email }}
                        </a>
                    </p>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<!-- Modals -->
@include('customer.dashboard.cod.partials.pay-fee-modal')
@include('customer.dashboard.cod.partials.priority-modal')

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content h6 {
    font-size: 0.95rem;
    font-weight: 600;
}

@media print {
    .btn, .breadcrumb, nav {
        display: none !important;
    }
}
</style>
@endpush

<script>
    /**
 * ============================================
 * CUSTOMER COD MANAGEMENT JAVASCRIPT
 * ============================================
 */

// ============ GLOBAL VARIABLES ============
let currentTransactionId = null;
let currentPayerShipping = null;
let feeData = null;

// ============ UTILITY FUNCTIONS ============

/**
 * Format số tiền theo chuẩn Việt Nam
 */
function formatMoney(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('success', 'Đã sao chép!', 'Nội dung đã được sao chép vào clipboard');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        showToast('success', 'Đã sao chép!', 'Nội dung đã được sao chép');
    } catch (err) {
        showToast('error', 'Lỗi!', 'Không thể sao chép. Vui lòng copy thủ công');
    }
    document.body.removeChild(textArea);
}

/**
 * Show toast notification
 */
function showToast(type, title, message) {
    // Nếu có Bootstrap Toast
    const toastContainer = document.getElementById('toastContainer');
    if (toastContainer) {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        toastContainer.innerHTML = toastHtml;
        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    } else {
        // Fallback: dùng alert
        alert(`${title}\n${message}`);
    }
}

// ============ PAY FEE MODAL ============

/**
 * Mở modal thanh toán phí
 */
function openPayFeeModal(transId, orderId, feeAmount, payerShipping) {
    currentTransactionId = transId;
    currentPayerShipping = payerShipping;
    feeData = null;

    // Set form action
    const form = document.getElementById('payFeeForm');
    if (form) {
        form.action = `/customer/cod/${transId}/pay-fee`;
    }

    // Update display info
    const orderIdDisplay = document.getElementById('orderIdDisplay');
    if (orderIdDisplay) orderIdDisplay.textContent = orderId;

    const feeAmountDisplay = document.getElementById('feeAmountDisplay');
    if (feeAmountDisplay) feeAmountDisplay.textContent = formatMoney(feeAmount) + '₫';

    const totalFeeDisplay = document.getElementById('totalFeeDisplay');
    if (totalFeeDisplay) totalFeeDisplay.textContent = formatMoney(feeAmount) + '₫';

    // Reset form
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    if (paymentMethodSelect) paymentMethodSelect.value = '';
    
    hideAllPaymentSections();
    
    // Load fee details
    loadFeeDetails(transId);

    // Show modal
    const modal = document.getElementById('payFeeModal');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

/**
 * Load fee details từ server
 */
function loadFeeDetails(transId) {
    const feeBreakdown = document.getElementById('feeBreakdown');
    if (!feeBreakdown) return;

    // Show loading
    feeBreakdown.innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="text-muted mt-2 mb-0 small">Đang tải chi tiết phí...</p>
        </div>
    `;

    fetch(`/customer/cod/${transId}/qr`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            feeData = data;
            displayFeeBreakdown(data.fee_breakdown);
        } else {
            feeBreakdown.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle"></i> ${data.error || 'Không thể tải dữ liệu'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading fee details:', error);
        feeBreakdown.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="bi bi-exclamation-triangle"></i> Lỗi kết nối. Vui lòng thử lại
            </div>
        `;
    });
}

/**
 * Hiển thị breakdown phí
 */
function displayFeeBreakdown(breakdown) {
    const container = document.getElementById('feeBreakdown');
    if (!container) return;

    let html = '';
    let total = 0;

    if (breakdown.platform_fee) {
        html += `
            <div class="d-flex justify-content-between mb-2">
                <span><i class="bi bi-gear text-primary"></i> Phí nền tảng:</span>
                <strong class="text-danger">${formatMoney(breakdown.platform_fee)}₫</strong>
            </div>
        `;
        total += parseFloat(breakdown.platform_fee);
    }

    if (breakdown.cod_fee) {
        html += `
            <div class="d-flex justify-content-between mb-2">
                <span><i class="bi bi-box text-primary"></i> Phí COD:</span>
                <strong class="text-danger">${formatMoney(breakdown.cod_fee)}₫</strong>
            </div>
        `;
        total += parseFloat(breakdown.cod_fee);
    }

    if (breakdown.shipping_fee) {
        html += `
            <div class="d-flex justify-content-between mb-2">
                <span><i class="bi bi-truck text-primary"></i> Phí vận chuyển:</span>
                <strong class="text-danger">${formatMoney(breakdown.shipping_fee)}₫</strong>
            </div>
        `;
        total += parseFloat(breakdown.shipping_fee);
    }

    container.innerHTML = html;

    // Update total
    const totalFeeDisplay = document.getElementById('totalFeeDisplay');
    if (totalFeeDisplay) {
        totalFeeDisplay.textContent = formatMoney(total) + '₫';
    }
}

/**
 * Payment method change handler
 */
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const method = this.value;
            hideAllPaymentSections();

            if (method === 'bank_transfer') {
                showBankTransferSection();
            } else if (method === 'wallet') {
                showWalletSection();
            } else if (method === 'cash') {
                showCashSection();
            }
        });
    }
});

/**
 * Show bank transfer section
 */
function showBankTransferSection() {
    const section = document.getElementById('bankTransferSection');
    if (!section) return;

    section.style.display = 'block';

    // Set input name
    const proofInput = document.getElementById('proofInputBankTransfer');
    if (proofInput) {
        proofInput.setAttribute('name', 'proof');
        proofInput.required = true;
    }

    // Load QR code
    loadQrCode();
}

/**
 * Show wallet section
 */
function showWalletSection() {
    const section = document.getElementById('walletSection');
    if (!section) return;

    section.style.display = 'block';

    // Set input name
    const proofInput = document.getElementById('proofInputWallet');
    if (proofInput) {
        proofInput.setAttribute('name', 'proof');
        proofInput.required = true;
    }
}

/**
 * Show cash section
 */
function showCashSection() {
    const section = document.getElementById('cashSection');
    if (section) {
        section.style.display = 'block';
    }
}

/**
 * Hide all payment sections
 */
function hideAllPaymentSections() {
    const sections = [
        'bankTransferSection',
        'walletSection',
        'cashSection',
        'qrCodeDisplay'
    ];

    sections.forEach(id => {
        const section = document.getElementById(id);
        if (section) section.style.display = 'none';
    });

    // Reset file inputs
    ['proofInputBankTransfer', 'proofInputWallet'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.removeAttribute('name');
            input.value = '';
            input.required = false;
        }
    });

    // Show loading spinner
    const spinner = document.getElementById('qrLoadingSpinner');
    if (spinner) spinner.style.display = 'block';
}

/**
 * Load QR code
 */
function loadQrCode() {
    if (!currentTransactionId || !feeData) {
        console.error('Missing transaction ID or fee data');
        return;
    }

    // Hiển thị thông tin ngân hàng
    const bankNameEl = document.getElementById('hubBankName');
    if (bankNameEl) bankNameEl.textContent = feeData.bank_info.bank_name;

    const accountNumberEl = document.getElementById('hubAccountNumber');
    if (accountNumberEl) accountNumberEl.textContent = feeData.bank_info.account_number;

    const accountNameEl = document.getElementById('hubAccountName');
    if (accountNameEl) accountNameEl.textContent = feeData.bank_info.account_name;

    const transferContentEl = document.getElementById('transferContent');
    if (transferContentEl) transferContentEl.value = feeData.content;

    // Load QR image
    const qrImage = document.getElementById('qrCodeImage');
    if (qrImage && feeData.qr_url) {
        qrImage.src = feeData.qr_url;
        qrImage.onload = function() {
            const spinner = document.getElementById('qrLoadingSpinner');
            if (spinner) spinner.style.display = 'none';
            
            const display = document.getElementById('qrCodeDisplay');
            if (display) display.style.display = 'block';
        };
        qrImage.onerror = function() {
            const spinner = document.getElementById('qrLoadingSpinner');
            if (spinner) {
                spinner.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Không thể tải mã QR
                    </div>
                `;
            }
        };
    }
}

/**
 * Copy transfer content button handler
 */
function copyTransferContent() {
    const input = document.getElementById('transferContent');
    if (input) {
        copyToClipboard(input.value);
    }
}

// ============ PRIORITY MODAL ============

/**
 * Mở modal yêu cầu ưu tiên
 */
function openPriorityModal(transId, orderId) {
    // Set form action
    const form = document.getElementById('priorityForm');
    if (form) {
        form.action = `/customer/cod/${transId}/request-priority`;
    }

    // Update order ID display
    const orderIdDisplay = document.getElementById('priorityOrderIdDisplay');
    if (orderIdDisplay) orderIdDisplay.textContent = orderId;

    // Reset textarea
    const reasonTextarea = document.getElementById('priorityReason');
    if (reasonTextarea) reasonTextarea.value = '';

    // Show modal
    const modal = document.getElementById('priorityModal');
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

// ============ MODAL RESET HANDLERS ============

document.addEventListener('DOMContentLoaded', function() {
    // Reset pay fee modal on close
    const payFeeModal = document.getElementById('payFeeModal');
    if (payFeeModal) {
        payFeeModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('payFeeForm');
            if (form) form.reset();
            
            hideAllPaymentSections();
            currentTransactionId = null;
            currentPayerShipping = null;
            feeData = null;
        });
    }

    // Reset priority modal on close
    const priorityModal = document.getElementById('priorityModal');
    if (priorityModal) {
        priorityModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('priorityForm');
            if (form) form.reset();
        });
    }
});

// ============ FORM VALIDATION ============

document.addEventListener('DOMContentLoaded', function() {
    // Pay fee form validation
    const payFeeForm = document.getElementById('payFeeForm');
    if (payFeeForm) {
        payFeeForm.addEventListener('submit', function(e) {
            const method = document.getElementById('paymentMethodSelect')?.value;
            
            if (!method) {
                e.preventDefault();
                showToast('error', 'Lỗi!', 'Vui lòng chọn phương thức thanh toán');
                return false;
            }

            if (method === 'bank_transfer' || method === 'wallet') {
                const proofInput = method === 'bank_transfer' 
                    ? document.getElementById('proofInputBankTransfer')
                    : document.getElementById('proofInputWallet');

                if (proofInput && !proofInput.files.length) {
                    e.preventDefault();
                    showToast('error', 'Lỗi!', 'Vui lòng tải lên ảnh chứng từ');
                    return false;
                }
            }

            // Show loading
            const submitBtn = payFeeForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
            }
        });
    }

    // Priority form validation
    const priorityForm = document.getElementById('priorityForm');
    if (priorityForm) {
        priorityForm.addEventListener('submit', function(e) {
            const reason = document.getElementById('priorityReason')?.value.trim();
            
            if (!reason) {
                e.preventDefault();
                showToast('error', 'Lỗi!', 'Vui lòng nhập lý do yêu cầu');
                return false;
            }

            if (reason.length > 500) {
                e.preventDefault();
                showToast('error', 'Lỗi!', 'Lý do không được vượt quá 500 ký tự');
                return false;
            }

            // Show loading
            const submitBtn = priorityForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';
            }
        });
    }
});

// ============ FILE PREVIEW ============

document.addEventListener('DOMContentLoaded', function() {
    // Preview uploaded images
    const fileInputs = ['proofInputBankTransfer', 'proofInputWallet'];
    
    fileInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        showToast('error', 'Lỗi!', 'Kích thước file không được vượt quá 5MB');
                        this.value = '';
                        return;
                    }

                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        showToast('error', 'Lỗi!', 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)');
                        this.value = '';
                        return;
                    }

                    // Show file info
                    const fileName = file.name;
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    const label = this.parentElement.querySelector('label');
                    if (label) {
                        label.innerHTML = `
                            <i class="bi bi-image"></i> Ảnh chứng từ chuyển khoản
                            <span class="text-danger">*</span>
                            <span class="text-success small d-block mt-1">
                                ✓ ${fileName} (${fileSize} MB)
                            </span>
                        `;
                    }
                }
            });
        }
    });
});

// ============ EXPORT FUNCTIONS TO WINDOW ============
window.openPayFeeModal = openPayFeeModal;
window.openPriorityModal = openPriorityModal;
window.copyTransferContent = copyTransferContent;
window.copyToClipboard = copyToClipboard;
</script>

@endsection