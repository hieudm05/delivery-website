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

<!-- resources/views/customer/dashboard/cod/show.blade.php -->

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calculator"></i> Chi tiết giao dịch
        </h6>
    </div>
    <div class="card-body p-4">
        @if($transaction->is_returned_order)
            {{-- ========== ĐƠN BỊ HOÀN VỀ ========== --}}
            <div class="alert alert-danger border-0 mb-4">
                <div class="d-flex align-items-start">
                    <i class="bi bi-x-octagon-fill fs-1 text-danger me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-2">Đơn hàng đã bị hoàn về</h5>
                        <p class="mb-2">
                            Đơn hàng không giao thành công và đã được hoàn trả về cho bạn.
                        </p>
                        <hr class="my-2">
                        <p class="mb-0">
                            <strong>Kết quả:</strong>
                        </p>
                        <ul class="mb-0 mt-2">
                            <li>❌ Bạn <strong>không nhận được</strong> tiền COD</li>
                            <li>💰 Phí hoàn hàng: <strong class="text-danger">{{ number_format($transaction->sender_fee_paid) }}₫</strong></li>
                            <li>📋 Phí này đã được <strong>cộng vào công nợ</strong> của bạn với bưu cục</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Bảng chi tiết --}}
            <table class="table table-borderless mb-0">
                <tbody>
                    <tr class="table-light">
                        <td colspan="2" class="fw-bold">
                            <i class="bi bi-info-circle"></i> Thông tin ban đầu
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tiền COD dự kiến thu</td>
                        <td class="text-end text-muted text-decoration-line-through">
                            {{ number_format($transaction->cod_amount) }}₫
                        </td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="2" class="fw-bold">
                            <i class="bi bi-exclamation-triangle"></i> Chi phí phát sinh
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">
                            <i class="bi bi-arrow-return-left"></i> Phí hoàn hàng
                        </td>
                        <td class="text-end text-danger fw-bold">
                            {{ number_format($transaction->sender_fee_paid) }}₫
                        </td>
                    </tr>
                    <tr class="table-active">
                        <td class="fw-bold text-danger">
                            <i class="bi bi-wallet2"></i> Tổng nợ phát sinh
                        </td>
                        <td class="text-end">
                            <h4 class="text-danger fw-bold mb-0">
                                {{ number_format($transaction->sender_fee_paid) }}₫
                            </h4>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Hướng dẫn thanh toán --}}
            <div class="alert alert-info border-0 mt-3 mb-0">
                <h6 class="alert-heading">
                    <i class="bi bi-lightbulb"></i> Cách thanh toán công nợ
                </h6>
                <ol class="mb-0 ps-3">
                    <li>Nợ sẽ <strong>tự động trừ</strong> vào tiền COD của đơn hàng tiếp theo</li>
                    <li>Hoặc bạn có thể <strong>thanh toán trực tiếp</strong> cho bưu cục qua tài khoản ngân hàng</li>
                    <li>Liên hệ hotline bưu cục bên dưới để được hỗ trợ</li>
                </ol>
            </div>

        @else
            {{-- ========== ĐƠN GIAO THÀNH CÔNG ========== --}}
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
                            <small><i class="bi bi-dash-circle"></i> Trừ nợ cũ</small>
                        </td>
                        <td class="text-end text-danger">
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
        @endif
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
            
            <!-- ✅ CARD MỚI: CÔNG NỢ HIỆN TẠI VỚI HUB -->
            @if(isset($currentDebt) && $currentDebt > 0)
            <div class="card shadow-sm border-0 mb-4 border-start border-danger border-4">
                <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger">
                    <h6 class="mb-0 fw-bold text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> Công nợ hiện tại
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-6 text-danger fw-bold mb-2">
                            {{ number_format($currentDebt) }}₫
                        </div>
                        <small class="text-muted">
                            Nợ với <strong>{{ $transaction->hub->full_name ?? 'bưu cục này' }}</strong>
                        </small>
                    </div>

                    <div class="alert alert-warning border-0 mb-3">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            <strong>Cách xử lý:</strong>
                        </small>
                        <ul class="mb-0 mt-2 small">
                            <li>Tự động trừ vào COD đơn tiếp theo</li>
                            <li>Hoặc thanh toán trực tiếp cho Hub</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('customer.cod.index', ['tab' => 'all']) }}" 
                           class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-list-ul"></i> Xem tất cả nợ
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Card: Trạng thái -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-flag"></i> Trạng thái hiện tại
                    </h6>
                </div>
                <div class="card-body">
                    
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

                   @if($transaction->is_returned_order && $transaction->sender_fee_paid > 0)
    <div class="mb-3">
        <label class="text-muted small d-block mb-2">Trạng thái thanh toán nợ</label>
        
        @if($transaction->sender_debt_payment_status === 'pending')
            {{-- Chờ Hub xác nhận --}}
            <span class="badge bg-warning text-dark fs-6">
                <i class="bi bi-clock-history"></i> Chờ Hub xác nhận
            </span>
            <small class="text-muted d-block mt-1">
                Đã nộp {{ number_format($transaction->sender_fee_paid) }}₫ 
                vào {{ $transaction->sender_debt_paid_at->format('d/m/Y H:i') }}
            </small>
            
        @elseif($transaction->sender_debt_payment_status === 'completed')
            {{-- Hub đã xác nhận --}}
            <span class="badge bg-success fs-6">
                <i class="bi bi-check-circle"></i> Hub đã xác nhận
            </span>
            <small class="text-muted d-block mt-1">
                Xác nhận vào {{ $transaction->sender_debt_confirmed_at->format('d/m/Y H:i') }}
            </small>
            
        @elseif($transaction->sender_debt_payment_status === 'rejected')
            {{-- Hub từ chối --}}
            <span class="badge bg-danger fs-6 d-block mb-2">
                <i class="bi bi-x-circle"></i> Hub từ chối
            </span>
            <small class="text-danger d-block mb-2">
                Lý do: {{ $transaction->sender_debt_rejection_reason }}
            </small>
            
            
        @else
            {{-- Chưa thanh toán --}}
            <span class="badge bg-danger fs-6 d-block mb-2">
                <i class="bi bi-wallet2"></i> Chưa thanh toán
            </span>
            
            @if($currentDebt > 0)
                <div class="alert alert-danger border-0 p-2 mb-2">
                    <small class="d-block mb-2">
                        <strong>Nợ hiện tại:</strong> {{ number_format($currentDebt) }}₫
                    </small>
                </div>
                
                {{-- ✅ NÚT THANH TOÁN --}}
                <button type="button" 
                    class="btn btn-sm btn-danger w-100"
                    onclick="openPayDebtModal(
                        {{ $transaction->id }}, 
                        {{ $transaction->order_id }}, 
                        {{ $currentDebt }}, 
                        '{{ $transaction->hub->full_name ?? 'Hub' }}'
                    )">
                    <i class="bi bi-credit-card"></i> Thanh toán ngay
                </button>
            @else
                <small class="text-muted">Không còn nợ</small>
            @endif
        @endif
    </div>
@endif

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

/* Debt Card Animation */
.border-start.border-danger.border-4 {
    animation: pulse-debt 2s ease-in-out infinite;
}

@keyframes pulse-debt {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
    }
    50% { 
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
}

@media print {
    .btn, .breadcrumb, nav {
        display: none !important;
    }
}
</style>
@endpush

@endsection