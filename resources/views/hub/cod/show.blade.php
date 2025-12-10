@extends('hub.layouts.app')
@section('title', 'Chi tiết giao dịch COD #' . $transaction->id)

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 50px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #e9ecef 0%, #dee2e6 100%);
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -38px;
        width: 40px;
        height: 40px;
        background: white;
        border: 3px solid #dee2e6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        z-index: 2;
    }
    .timeline-item.completed .timeline-marker {
        border-color: #198754;
        background: #d1e7dd;
    }
    .timeline-item.pending .timeline-marker {
        border-color: #ffc107;
        background: #fff3cd;
    }
    .timeline-item.waiting .timeline-marker {
        border-color: #6c757d;
        background: #e9ecef;
    }
    .timeline-content {
        width: unset;
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
    }
    .qr-code-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        color: white;
    }
    .qr-code-image {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        display: inline-block;
        margin: 1rem 0;
    }
    .bank-info-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }
    .amount-display {
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    .status-badge-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 50px;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0 fw-bold">
                    <i class="bi bi-receipt-cutoff text-primary"></i> Chi tiết giao dịch COD #{{ $transaction->id }}
                </h3>
                <p class="text-muted mb-0 mt-1">
                    <i class="bi bi-box-seam"></i> Đơn hàng #{{ $transaction->order_id }} • 
                    <i class="bi bi-calendar3"></i> {{ $transaction->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div>
                <a href="{{ route('hub.cod.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- COL 1: LUỒNG TIỀN -->
            <div class="col-lg-8">
                <!-- LUỒNG TIỀN -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient text-white py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-arrow-left-right"></i> Luồng tiền COD</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline">
                            <!-- BƯỚC 1: Driver → Hub -->
                            <div class="timeline-item {{ $transaction->shipper_payment_status === 'confirmed' ? 'completed' : ($transaction->shipper_payment_status === 'transferred' ? 'pending' : 'waiting') }}">
                                <div class="timeline-marker">
                                    @if ($transaction->shipper_payment_status === 'confirmed')
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($transaction->shipper_payment_status === 'transferred')
                                        <i class="bi bi-clock-fill text-warning"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">
                                                <i class="bi bi-truck"></i> Driver → Hub
                                            </h6>
                                            <small class="text-muted">Tài xế chuyển tiền thu được cho Hub</small>
                                        </div>
                                        <span class="badge status-badge-lg bg-{{ $transaction->shipper_payment_status === 'confirmed' ? 'success' : ($transaction->shipper_payment_status === 'transferred' ? 'warning' : 'secondary') }}">
                                            {{ $transaction->shipper_status_label }}
                                        </span>
                                    </div>
                                    
                                    <div class="alert alert-light border mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block mb-2"><i class="bi bi-cash-stack"></i> Số tiền</small>
                                                <h4 class="mb-0 text-primary fw-bold">{{ number_format($transaction->total_collected) }}đ</h4>
                                            </div>
                                            <div class="col-md-6">
                                                @if($driverBankAccount)
                                                    <small class="text-muted d-block mb-2"><i class="bi bi-bank"></i> Tài khoản Driver</small>
                                                    <div class="text-dark fw-semibold">{{ $driverBankAccount->bank_short_name ?? $driverBankAccount->bank_name }}</div>
                                                    <small class="text-muted">{{ $driverBankAccount->account_number }}</small>
                                                    <div class="mt-1">
                                                        <span class="badge bg-success-subtle text-success">
                                                            <i class="bi bi-check-circle-fill"></i> Đã xác minh
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning mb-0 py-2">
                                                        <i class="bi bi-exclamation-triangle"></i> Driver chưa có tài khoản ngân hàng
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if ($transaction->shipper_payment_status === 'transferred')
                                        <div class="alert alert-warning border-warning mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                                <div>
                                                    <strong>Driver đã chuyển tiền</strong>
                                                    <p class="mb-0 mt-2 small">Vui lòng kiểm tra và xác nhận đã nhận tiền</p>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmFromDriverModal">
                                            <i class="bi bi-check-circle"></i> Xác nhận đã nhận tiền
                                        </button>
                                    @elseif($transaction->shipper_payment_status === 'confirmed')
                                        <div class="alert alert-success border-success mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                                <div class="flex-grow-1">
                                                    <strong>Đã xác nhận nhận tiền</strong>
                                                    <p class="mb-1 mt-2 small">
                                                        <i class="bi bi-clock"></i> {{ $transaction->hub_confirm_time->format('d/m/Y H:i') }}
                                                    </p>
                                                    @if ($transaction->hubConfirmer)
                                                        <p class="mb-1 small"><i class="bi bi-person"></i> {{ $transaction->hubConfirmer->full_name }}</p>
                                                    @endif
                                                    @if ($transaction->hub_confirm_note)
                                                        <p class="mb-0 mt-2 small"><strong>Ghi chú:</strong> {{ $transaction->hub_confirm_note }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-secondary border mb-0">
                                            <i class="bi bi-clock"></i> Đang chờ driver chuyển tiền
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- BƯỚC 2: Hub → Sender (COD) -->
                            <div class="timeline-item {{ $transaction->sender_payment_status === 'completed' ? 'completed' : ($transaction->sender_payment_status === 'pending' ? 'pending' : 'waiting') }}">
                                <div class="timeline-marker">
                                    @if ($transaction->sender_payment_status === 'completed')
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($transaction->sender_payment_status === 'pending')
                                        <i class="bi bi-clock-fill text-warning"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">
                                                <i class="bi bi-send"></i> Hub → Sender (COD)
                                            </h6>
                                            <small class="text-muted">Hub chuyển tiền COD cho người gửi</small>
                                        </div>
                                        <span class="badge status-badge-lg bg-{{ $transaction->sender_payment_status === 'completed' ? 'success' : ($transaction->sender_payment_status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ $transaction->sender_status_label }}
                                        </span>
                                    </div>

                                    <div class="alert alert-light border mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block mb-2"><i class="bi bi-cash-stack"></i> Số tiền COD</small>
                                                <h4 class="mb-0 text-success fw-bold">{{ number_format($transaction->sender_receive_amount) }}đ</h4>
                                            </div>
                                            <div class="col-md-6">
                                                @if($senderBankAccount)
                                                    <small class="text-muted d-block mb-2"><i class="bi bi-bank"></i> Tài khoản Sender</small>
                                                    <div class="text-dark fw-semibold">{{ $senderBankAccount->bank_short_name ?? $senderBankAccount->bank_name }}</div>
                                                    <small class="text-muted">{{ $senderBankAccount->account_number }}</small>
                                                    <div class="mt-1">
                                                        <span class="badge bg-success-subtle text-success">
                                                            <i class="bi bi-check-circle-fill"></i> Đã xác minh
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="alert alert-danger mb-0 py-2">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Sender chưa có tài khoản ngân hàng
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if ($transaction->sender_payment_status === 'pending')
                                        @if(!$senderHasBankAccount)
                                            <div class="alert alert-danger border-danger mb-3">
                                                <div class="d-flex align-items-start">
                                                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                                    <div>
                                                        <strong>Không thể chuyển tiền</strong>
                                                        <p class="mb-0 mt-2 small">Sender chưa có tài khoản ngân hàng. Vui lòng liên hệ sender để cập nhật thông tin.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info border-info mb-3">
                                                <i class="bi bi-info-circle-fill"></i> <strong>Cần chuyển tiền COD cho sender</strong>
                                            </div>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferToSenderModal">
                                                <i class="bi bi-send-fill"></i> Chuyển tiền cho Sender
                                            </button>
                                        @endif
                                    @elseif($transaction->sender_payment_status === 'completed')
                                        <div class="alert alert-success border-success mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                                <div class="flex-grow-1">
                                                    <strong>Đã chuyển tiền cho sender</strong>
                                                    <p class="mb-1 mt-2 small">
                                                        <i class="bi bi-clock"></i> {{ $transaction->sender_transfer_time->format('d/m/Y H:i') }}
                                                    </p>
                                                    <p class="mb-1 small">
                                                        <i class="bi bi-credit-card"></i> 
                                                        @if ($transaction->sender_transfer_method === 'bank_transfer')
                                                            Chuyển khoản ngân hàng
                                                        @elseif($transaction->sender_transfer_method === 'wallet')
                                                            Ví điện tử
                                                        @else
                                                            Tiền mặt
                                                        @endif
                                                    </p>
                                                    @if ($transaction->sender_transfer_proof)
                                                        <a href="{{ asset('storage/' . $transaction->sender_transfer_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                            <i class="bi bi-image"></i> Xem chứng từ
                                                        </a>
                                                    @endif
                                                    @if ($transaction->sender_transfer_note)
                                                        <p class="mb-0 mt-2 small"><strong>Ghi chú:</strong> {{ $transaction->sender_transfer_note }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-secondary border mb-0">
                                            <i class="bi bi-lock"></i> Chưa sẵn sàng (cần xác nhận bước 1)
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- BƯỚC 3: Hub → Driver (Commission) -->
                            <div class="timeline-item {{ $transaction->driver_commission_status === 'paid' ? 'completed' : ($transaction->driver_commission_status === 'pending' && $transaction->shipper_payment_status === 'confirmed' ? 'pending' : 'waiting') }}">
                                <div class="timeline-marker">
                                    @if ($transaction->driver_commission_status === 'paid')
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($transaction->driver_commission_status === 'pending' && $transaction->shipper_payment_status === 'confirmed')
                                        <i class="bi bi-clock-fill text-warning"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">
                                                <i class="bi bi-cash-coin"></i> Hub → Driver (Commission)
                                            </h6>
                                            <small class="text-muted">Hub trả hoa hồng cho tài xế</small>
                                        </div>
                                        <span class="badge status-badge-lg bg-{{ $transaction->driver_commission_status === 'paid' ? 'success' : 'secondary' }}">
                                            {{ $transaction->driver_commission_status_label }}
                                        </span>
                                    </div>

                                    <div class="alert alert-light border mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block mb-2"><i class="bi bi-cash-coin"></i> Commission</small>
                                                <h4 class="mb-0 text-primary fw-bold">{{ number_format($transaction->driver_commission) }}đ</h4>
                                                <small class="text-muted">= {{ number_format($transaction->shipping_fee) }}đ × 50%</small>
                                            </div>
                                            <div class="col-md-6">
                                                @if($driverBankAccount)
                                                    <small class="text-muted d-block mb-2"><i class="bi bi-bank"></i> Tài khoản Driver</small>
                                                    <div class="text-dark fw-semibold">{{ $driverBankAccount->bank_short_name ?? $driverBankAccount->bank_name }}</div>
                                                    <small class="text-muted">{{ $driverBankAccount->account_number }}</small>
                                                    <div class="mt-1">
                                                        <span class="badge bg-success-subtle text-success">
                                                            <i class="bi bi-check-circle-fill"></i> Đã xác minh
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning mb-0 py-2">
                                                        <i class="bi bi-exclamation-triangle"></i> Driver chưa có tài khoản
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if ($transaction->canPayDriverCommission())
                                        @if(!$driverHasBankAccount)
                                            <div class="alert alert-danger border-danger mb-3">
                                                <div class="d-flex align-items-start">
                                                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                                    <div>
                                                        <strong>Không thể trả commission</strong>
                                                        <p class="mb-0 mt-2 small">Driver chưa có tài khoản ngân hàng. Vui lòng liên hệ driver để cập nhật.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info border-info mb-3">
                                                <i class="bi bi-info-circle-fill"></i> <strong>Cần trả commission cho driver</strong>
                                            </div>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payDriverCommissionModal">
                                                <i class="bi bi-cash-coin"></i> Trả commission
                                            </button>
                                        @endif
                                    @elseif($transaction->driver_commission_status === 'paid')
                                        <div class="alert alert-success border-success mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                                <div>
                                                    <strong>Đã trả commission</strong>
                                                    <p class="mb-0 mt-2 small">
                                                        <i class="bi bi-clock"></i> {{ $transaction->driver_paid_at->format('d/m/Y H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-secondary border mb-0">
                                            <i class="bi bi-lock"></i> Chưa sẵn sàng (cần xác nhận bước 1)
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- BƯỚC 4: Hub → System (COD Fee) -->
                            <div class="timeline-item {{ $transaction->hub_system_status === 'confirmed' ? 'completed' : ($transaction->hub_system_status === 'transferred' ? 'pending' : ($transaction->hub_system_status === 'pending' ? 'pending' : 'waiting')) }}">
                                <div class="timeline-marker">
                                    @if ($transaction->hub_system_status === 'confirmed')
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($transaction->hub_system_status === 'transferred' || $transaction->hub_system_status === 'pending')
                                        <i class="bi bi-clock-fill text-warning"></i>
                                    @else
                                        <i class="bi bi-circle text-muted"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1 fw-bold">
                                                <i class="bi bi-database"></i> Hub → System (COD Fee)
                                            </h6>
                                            <small class="text-muted">Hub nộp phí COD cho hệ thống</small>
                                        </div>
                                        <span class="badge status-badge-lg bg-{{ $transaction->hub_system_status === 'confirmed' ? 'success' : ($transaction->hub_system_status === 'transferred' ? 'info' : ($transaction->hub_system_status === 'pending' ? 'warning' : 'secondary')) }}">
                                            {{ $transaction->system_status_label }}
                                        </span>
                                    </div>

                                    <div class="alert alert-light border mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block mb-2"><i class="bi bi-cash-stack"></i> Số tiền nộp</small>
                                                <h4 class="mb-0 text-danger fw-bold">{{ number_format($transaction->hub_system_amount) }}đ</h4>
                                            </div>
                                            <div class="col-md-6">
                                                @if($systemBankAccount)
                                                    <small class="text-muted d-block mb-2"><i class="bi bi-bank"></i> Tài khoản System</small>
                                                    <div class="text-dark fw-semibold">{{ $systemBankAccount->bank_short_name ?? $systemBankAccount->bank_name }}</div>
                                                    <small class="text-muted">{{ $systemBankAccount->account_number }}</small>
                                                    <div class="mt-1">
                                                        <span class="badge bg-primary-subtle text-primary">
                                                            <i class="bi bi-shield-check"></i> Tài khoản hệ thống
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="alert alert-danger mb-0 py-2">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Chưa cấu hình tài khoản
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if ($transaction->hub_system_status === 'pending')
                                        @if(!$systemHasBankAccount)
                                            <div class="alert alert-danger border-danger mb-3">
                                                <div class="d-flex align-items-start">
                                                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                                    <div>
                                                        <strong>Không thể nộp tiền</strong>
                                                        <p class="mb-0 mt-2 small">Hệ thống chưa cấu hình tài khoản ngân hàng. Vui lòng liên hệ admin.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info border-info mb-3">
                                                <i class="bi bi-info-circle-fill"></i> <strong>Cần nộp COD fee cho hệ thống</strong>
                                            </div>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#transferToSystemModal">
                                                <i class="bi bi-database"></i> Nộp cho hệ thống
                                            </button>
                                        @endif
                                    @elseif($transaction->hub_system_status === 'transferred')
                                        <div class="alert alert-warning border-warning mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-clock-fill text-warning me-2 fs-5"></i>
                                                <div class="flex-grow-1">
                                                    <strong>Đã nộp, chờ admin xác nhận</strong>
                                                    <p class="mb-1 mt-2 small">
                                                        <i class="bi bi-clock"></i> {{ $transaction->hub_system_transfer_time->format('d/m/Y H:i') }}
                                                    </p>
                                                    <p class="mb-1 small">
                                                        <i class="bi bi-credit-card"></i> 
                                                        {{ $transaction->hub_system_method === 'bank_transfer' ? 'Chuyển khoản' : 'Tiền mặt' }}
                                                    </p>
                                                    @if ($transaction->hub_system_proof)
                                                        <a href="{{ asset('storage/' . $transaction->hub_system_proof) }}" target="_blank" class="btn btn-sm btn-outline-warning mt-2">
                                                            <i class="bi bi-image"></i> Xem chứng từ
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($transaction->hub_system_status === 'confirmed')
                                        <div class="alert alert-success border-success mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-check-circle-fill text-success me-2 fs-5"></i>
                                                <div class="flex-grow-1">
                                                    <strong>Admin đã xác nhận nhận tiền</strong>
                                                    <p class="mb-1 mt-2 small">
                                                        <i class="bi bi-clock"></i> {{ $transaction->system_confirm_time->format('d/m/Y H:i') }}
                                                    </p>
                                                    @if ($transaction->systemConfirmer)
                                                        <p class="mb-1 small"><i class="bi bi-person"></i> {{ $transaction->systemConfirmer->full_name }}</p>
                                                    @endif
                                                    @if ($transaction->system_confirm_note)
                                                        <p class="mb-0 mt-2 small"><strong>Ghi chú:</strong> {{ $transaction->system_confirm_note }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-secondary border mb-0">
                                            <i class="bi bi-lock"></i> Chưa sẵn sàng (cần xác nhận bước 1)
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PHÂN CHIA TIỀN -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-cash-stack"></i> Phân chia tiền</h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- TỔNG QUAN -->
                        <div class="alert alert-light border mb-4">
                            <div class="row text-center g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-2">💰 Driver thu từ khách</small>
                                    <h3 class="text-primary mb-0 fw-bold">{{ number_format($transaction->total_collected) }}đ</h3>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-2">📦 COD Amount</small>
                                    <h3 class="text-dark mb-0 fw-bold">{{ number_format($transaction->cod_amount) }}đ</h3>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block mb-2">🚚 Shipping Fee</small>
                                    <h3 class="text-dark mb-0 fw-bold">{{ number_format($transaction->shipping_fee) }}đ</h3>
                                </div>
                            </div>
                        </div>

                        <!-- PHÂN CHIA -->
                        <h6 class="text-muted mb-3 fw-semibold"><i class="bi bi-arrow-down-circle"></i> Phân chia cho các bên:</h6>
                        <div class="row g-3">
                            <!-- SENDER -->
                            <div class="col-md-3">
                                <div class="card h-100 border border-success border-2">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-muted mb-0 small">👤 Sender nhận</h6>
                                            @if ($transaction->sender_payment_status === 'completed')
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-clock text-warning"></i>
                                            @endif
                                        </div>
                                        <h4 class="text-success mb-2 fw-bold">{{ number_format($transaction->sender_receive_amount) }}đ</h4>
                                        <small class="text-muted d-block">
                                            = {{ number_format($transaction->cod_amount) }}đ<br>
                                            - {{ number_format($transaction->cod_fee) }}đ (phí COD)
                                            @if ($transaction->sender_debt_deducted > 0)
                                                <br>- {{ number_format($transaction->sender_debt_deducted) }}đ (trừ nợ)
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- DRIVER -->
                            <div class="col-md-3">
                                <div class="card h-100 border border-primary border-2">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-muted mb-0 small">🚗 Driver nhận</h6>
                                            @if ($transaction->driver_commission_status === 'paid')
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-clock text-warning"></i>
                                            @endif
                                        </div>
                                        <h4 class="text-primary mb-2 fw-bold">{{ number_format($transaction->driver_commission) }}đ</h4>
                                        <small class="text-muted d-block">
                                            = {{ number_format($transaction->shipping_fee) }}đ × 50%<br>
                                            (Commission)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- HUB -->
                            <div class="col-md-3">
                                <div class="card h-100 border border-warning border-2">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-muted mb-0 small">🏢 Hub giữ lại</h6>
                                            <i class="bi bi-building text-warning"></i>
                                        </div>
                                        <h4 class="text-warning mb-2 fw-bold">{{ number_format($transaction->hub_profit) }}đ</h4>
                                        <small class="text-muted d-block">
                                            = {{ number_format($transaction->shipping_fee) }}đ × 50%<br>
                                            (Commission)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- SYSTEM -->
                            <div class="col-md-3">
                                <div class="card h-100 border border-danger border-2">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="text-muted mb-0 small">💼 System nhận</h6>
                                            @if ($transaction->hub_system_status === 'confirmed')
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-clock text-warning"></i>
                                            @endif
                                        </div>
                                        <h4 class="text-danger mb-2 fw-bold">{{ number_format($transaction->hub_system_amount) }}đ</h4>
                                        <small class="text-muted d-block">
                                            = {{ number_format($transaction->cod_fee) }}đ (phí COD)
                                            @if ($transaction->sender_debt_deducted > 0)
                                                <br>+ {{ number_format($transaction->sender_debt_deducted) }}đ (nợ)
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COL 2: THÔNG TIN BỔ SUNG -->
            <div class="col-lg-4">
                <!-- THÔNG TIN ĐƠN HÀNG -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-box-seam"></i> Thông tin đơn hàng</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" width="40%"><i class="bi bi-hash"></i> Mã đơn</td>
                                <td class="fw-semibold">#{{ $transaction->order_id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-person"></i> Người gửi</td>
                                <td class="fw-semibold">{{ $transaction->sender->full_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-truck"></i> Tài xế</td>
                                <td class="fw-semibold">{{ $transaction->driver->full_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="bi bi-calendar3"></i> Ngày tạo</td>
                                <td class="fw-semibold">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- TRẠNG THÁI TỔNG THỂ -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light py-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-check2-square"></i> Trạng thái tổng thể</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Driver → Hub</small>
                                <span class="badge bg-{{ $transaction->shipper_payment_status === 'confirmed' ? 'success' : 'warning' }}">
                                    {{ $transaction->shipper_status_label }}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $transaction->shipper_payment_status === 'confirmed' ? 'success' : 'warning' }}" 
                                     style="width: {{ $transaction->shipper_payment_status === 'confirmed' ? '100' : '50' }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Hub → Sender</small>
                                <span class="badge bg-{{ $transaction->sender_payment_status === 'completed' ? 'success' : ($transaction->sender_payment_status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ $transaction->sender_status_label }}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $transaction->sender_payment_status === 'completed' ? 'success' : 'warning' }}" 
                                     style="width: {{ $transaction->sender_payment_status === 'completed' ? '100' : ($transaction->sender_payment_status === 'pending' ? '50' : '0') }}%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Hub → Driver (Commission)</small>
                                <span class="badge bg-{{ $transaction->driver_commission_status === 'paid' ? 'success' : 'secondary' }}">
                                    {{ $transaction->driver_commission_status_label }}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $transaction->driver_commission_status === 'paid' ? 'success' : 'secondary' }}" 
                                     style="width: {{ $transaction->driver_commission_status === 'paid' ? '100' : '0' }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Hub → System</small>
                                <span class="badge bg-{{ $transaction->hub_system_status === 'confirmed' ? 'success' : ($transaction->hub_system_status === 'transferred' ? 'info' : 'secondary') }}">
                                    {{ $transaction->system_status_label }}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $transaction->hub_system_status === 'confirmed' ? 'success' : 'info' }}" 
                                     style="width: {{ $transaction->hub_system_status === 'confirmed' ? '100' : ($transaction->hub_system_status === 'transferred' ? '50' : '0') }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    @include('hub.cod.modals.confirm-from-driver')
    @include('hub.cod.modals.transfer-to-sender')
    @include('hub.cod.modals.pay-driver-commission')
    @include('hub.cod.modals.transfer-to-system')
    {{-- Add this to the bottom of your show.blade.php file, before @endsection --}}

@push('scripts')
<script>
    // ===============================================
// THAY THẾ TOÀN BỘ PHẦN <script> TRONG show.blade.php
// ===============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== TRANSFER TO SENDER MODAL ==========
    const transferToSenderModal = document.getElementById('transferToSenderModal');
    if (transferToSenderModal) {
        transferToSenderModal.addEventListener('shown.bs.modal', function() {
            const methodSelect = document.getElementById('senderTransferMethod');
            if (methodSelect && methodSelect.value === 'bank_transfer') {
                loadSenderQrCode();
            }
        });
        
        const methodSelect = document.getElementById('senderTransferMethod');
        if (methodSelect) {
            methodSelect.addEventListener('change', function() {
                const qrSection = document.getElementById('senderQrCodeSection');
                if (this.value === 'bank_transfer') {
                    qrSection.classList.remove('d-none');
                    loadSenderQrCode();
                } else {
                    qrSection.classList.add('d-none');
                }
            });
        }
    }
    
    // ========== PAY DRIVER COMMISSION MODAL ==========
    const payDriverModal = document.getElementById('payDriverCommissionModal');
    if (payDriverModal) {
        payDriverModal.addEventListener('shown.bs.modal', function() {
            @if($driverHasBankAccount)
                loadDriverQrCode();
            @endif
        });
    }
    
    // ========== TRANSFER TO SYSTEM MODAL ==========
    const transferToSystemModal = document.getElementById('transferToSystemModal');
    if (transferToSystemModal) {
        transferToSystemModal.addEventListener('shown.bs.modal', function() {
            const methodSelect = document.getElementById('systemTransferMethod');
            if (methodSelect && methodSelect.value === 'bank_transfer') {
                loadSystemQrCode();
            }
        });
        
        const methodSelect = document.getElementById('systemTransferMethod');
        if (methodSelect) {
            methodSelect.addEventListener('change', function() {
                const qrSection = document.getElementById('systemQrCodeSection');
                const proofInput = document.querySelector('#transferToSystemModal input[name="proof"]');
                
                if (this.value === 'bank_transfer') {
                    qrSection.classList.remove('d-none');
                    loadSystemQrCode();
                    if (proofInput) proofInput.required = true;
                } else {
                    qrSection.classList.add('d-none');
                    if (proofInput) proofInput.required = false;
                }
            });
        }
    }
});

// ✅ FIXED: Load Sender QR Code
function loadSenderQrCode() {
    const qrContainer = document.getElementById('senderQrCodeContainer');
    if (!qrContainer) return;
    
    qrContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="text-muted mb-0">Đang tạo mã QR...</p>
        </div>
    `;
    
    // ✅ FIXED: Sử dụng route helper đúng
    fetch('{{ route("hub.cod.get-sender-qr", $transaction->id) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderQrCode(qrContainer, data, 'success');
            } else {
                showQrError(qrContainer, data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showQrError(qrContainer, 'Lỗi kết nối. Vui lòng thử lại.');
        });
}

// ✅ FIXED: Load Driver QR Code
function loadDriverQrCode() {
    const qrContainer = document.getElementById('driverQrCodeContainer');
    if (!qrContainer) return;
    
    qrContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="text-muted mb-0">Đang tạo mã QR...</p>
        </div>
    `;
    
    // ✅ FIXED: Sử dụng route helper đúng
    fetch('{{ route("hub.cod.get-driver-qr", $transaction->id) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderQrCode(qrContainer, data, 'primary');
            } else {
                showQrError(qrContainer, data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showQrError(qrContainer, 'Lỗi kết nối. Vui lòng thử lại.');
        });
}

// ✅ FIXED: Load System QR Code
function loadSystemQrCode() {
    const qrContainer = document.getElementById('systemQrCodeContainer');
    if (!qrContainer) return;
    
    qrContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-danger mb-3" role="status"></div>
            <p class="text-muted mb-0">Đang tạo mã QR...</p>
        </div>
    `;
    
    const amount = {{ $transaction->hub_system_amount }};
    const content = 'COD #{{ $transaction->id }} - System Fee';
    
    // ✅ FIXED: Sử dụng route helper + query params đúng
    const url = '{{ route("hub.cod.get-system-qr") }}' + 
                `?amount=${amount}&content=${encodeURIComponent(content)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderQrCode(qrContainer, data, 'danger');
            } else {
                showQrError(qrContainer, data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showQrError(qrContainer, 'Lỗi kết nối. Vui lòng thử lại.');
        });
}

// Render QR Code
function renderQrCode(container, data, theme = 'primary') {
    const gradients = {
        'success': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'primary': 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'danger': 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)'
    };
    
    container.innerHTML = `
        <div class="qr-wrapper" style="background: ${gradients[theme]}; padding: 2rem; border-radius: 12px;">
            <div class="text-white text-center mb-3">
                <h6 class="mb-0"><i class="bi bi-qr-code-scan"></i> Quét mã để chuyển tiền</h6>
            </div>
            
            <div class="bg-white p-3 rounded-3 shadow-sm mx-auto mb-3" style="max-width: 250px;">
                <img src="${data.qr_url}" alt="QR Code" class="img-fluid rounded">
            </div>
            
            <div class=" bg-opacity-10 rounded-3 p-3 mb-3 text-white">
                <div class="row g-2 text-center small">
                    <div class="col-12">
                        <div class="opacity-75 mb-1"><i class="bi bi-bank"></i> Ngân hàng</div>
                        <strong>${data.bank_info.bank_short_name}</strong>
                    </div>
                    <div class="col-6">
                        <div class="opacity-75 mb-1"><i class="bi bi-credit-card"></i> STK</div>
                        <strong>${data.bank_info.account_number}</strong>
                    </div>
                    <div class="col-6">
                        <div class="opacity-75 mb-1"><i class="bi bi-person"></i> Chủ TK</div>
                        <strong>${data.bank_info.account_name}</strong>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Show Error
function showQrError(container, message) {
    container.innerHTML = `
        <div class="alert alert-danger mb-0">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>${message}</strong>
        </div>
    `;
}

// Helper Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount);
}

function copyBankInfo(accountNumber, content) {
    const text = `STK: ${accountNumber}\nNội dung: ${content}`;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('✓ Đã copy thông tin', 'success');
        });
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('✓ Đã copy thông tin', 'success');
    }
}

function showToast(message, type = 'info') {
    const colors = { success: 'bg-success', error: 'bg-danger', info: 'bg-info' };
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white ${colors[type]} border-0`;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}
</script>
@endpush
@endsection

@push('scripts')
<script>
// Auto load QR code when modal opens
document.addEventListener('DOMContentLoaded', function() {
    // Transfer to Sender Modal
    const transferToSenderModal = document.getElementById('transferToSenderModal');
    if (transferToSenderModal) {
        transferToSenderModal.addEventListener('shown.bs.modal', function() {
            const methodSelect = document.getElementById('senderTransferMethod');
            const qrSection = document.getElementById('senderQrCodeSection');
            
            // ✅ CHECK và HIỂN THỊ ngay khi modal mở
            if (methodSelect && methodSelect.value === 'bank_transfer') {
                qrSection.classList.remove('d-none');
                loadSenderQrCode();
            }
        });
    
   if (transferToSystemModal) {
        // Khi modal vừa mở (shown.bs.modal)
        transferToSystemModal.addEventListener('shown.bs.modal', function() {
            const methodSelect = document.getElementById('systemTransferMethod');
            const qrSection = document.getElementById('systemQrCodeSection');

            // 1. Mặc định chọn "bank_transfer"
            if (methodSelect) {
                methodSelect.value = 'bank_transfer'; // hoặc giá trị tương ứng trong <option>
            }

            // 2. Hiển thị phần QR và load ngay lập tức
            if (qrSection) {
                qrSection.classList.remove('d-none');
                loadSystemQrCode();
            }
        });

        // Khi thay đổi phương thức (vẫn giữ logic cũ)
        const methodSelect = document.getElementById('systemTransferMethod');
        if (methodSelect) {
            methodSelect.addEventListener('change', function() {
                const qrSection = document.getElementById('systemQrCodeSection');
                const proofInput = document.querySelector('#transferToSystemModal input[name="proof"]');

                if (this.value === 'bank_transfer') {
                    qrSection.classList.remove('d-none');
                    loadSystemQrCode();
                    if (proofInput) proofInput.required = true;
                } else {
                    qrSection.classList.add('d-none');
                    if (proofInput) proofInput.required = false;
                }
            });
        }
    }
});



// Load QR Code for System
function loadSystemQrCode() {
    const qrContainer = document.getElementById('systemQrCodeContainer');
    if (!qrContainer) return;
    
    const amount = {{ $transaction->hub_system_amount }};
    const content = 'COD #{{ $transaction->id }} - System Fee';
    
    qrContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Đang tạo mã QR...</p></div>';
    
    // Show QR container
    document.getElementById('systemQrCodeSection').classList.remove('d-none');
    
    // Call actual API
    const url = '{{ route("hub.cod.get-system-qr") }}' + 
                `?amount=${amount}&content=${encodeURIComponent(content)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                qrContainer.innerHTML = `
                    <div class="qr-code-container">
                        <h5 class="mb-3"><i class="bi bi-qr-code"></i> Quét mã QR để nộp tiền</h5>
                        <div class="qr-code-image">
                            <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                        </div>
                        <div class="bank-info-card">
                            <div class="text-dark">
                                <strong>${data.bank_info.bank_short_name}</strong><br>
                                <span>${data.bank_info.account_number}</span><br>
                                <small>${data.bank_info.account_name}</small>
                            </div>
                        </div>
                        <div class="amount-display mt-3">
                            ${amount.toLocaleString('vi-VN')}đ
                        </div>
                        <small class="d-block mt-2">Nội dung: ${content}</small>
                    </div>
                `;
            } else {
                qrContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            qrContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Không thể tạo mã QR
                </div>
            `;
        });
}

// Handle transfer method change for Sender
document.getElementById('senderTransferMethod')?.addEventListener('change', function() {
    const qrSection = document.getElementById('senderQrCodeSection');
    if (this.value === 'bank_transfer') {
        loadSenderQrCode();
    } else {
        qrSection.classList.add('d-none');
    }
});

// Handle transfer method change for System
document.getElementById('systemTransferMethod')?.addEventListener('change', function() {
    const qrSection = document.getElementById('systemQrCodeSection');
    if (this.value === 'bank_transfer') {
        loadSystemQrCode();
    } else {
        qrSection.classList.add('d-none');
    }
});
</script>
@endpush