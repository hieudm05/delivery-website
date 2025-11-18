@extends('hub.layouts.app')
@section('title', 'Chi tiết giao dịch COD #' . $transaction->id)

@section('content')
<div class="container">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-receipt"></i> Chi tiết giao dịch COD #{{ $transaction->id }}
            </h3>
            <p class="text-muted mb-0">Đơn hàng #{{ $transaction->order_id }}</p>
        </div>
        <div>
            <a href="{{ route('hub.cod.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- COL 1: THÔNG TIN GIAO DỊCH -->
        <div class="col-lg-8">
            <!-- LUỒNG TIỀN -->
            <div class="card shadow mb-4">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> Luồng tiền COD</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- BƯỚC 1: Driver → Hub -->
                        <div class="timeline-item {{ $transaction->shipper_payment_status === 'confirmed' ? 'completed' : ($transaction->shipper_payment_status === 'transferred' ? 'pending' : 'waiting') }}">
                            <div class="timeline-marker">
                                @if($transaction->shipper_payment_status === 'confirmed')
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($transaction->shipper_payment_status === 'transferred')
                                    <i class="bi bi-clock-fill text-warning"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-2">
                                    <i class="bi bi-truck"></i> Driver → Hub
                                    <span class="badge bg-{{ $transaction->shipper_payment_status === 'confirmed' ? 'success' : ($transaction->shipper_payment_status === 'transferred' ? 'warning' : 'secondary') }} ms-2">
                                        {{ $transaction->shipper_status_label }}
                                    </span>
                                </h6>
                                <p class="mb-1"><strong>Số tiền:</strong> <span class="text-primary fs-5">{{ number_format($transaction->total_collected) }}đ</span></p>
                                
                                @if($transaction->shipper_payment_status === 'transferred')
                                    <div class="alert alert-warning mb-3">
                                        <i class="bi bi-exclamation-triangle"></i> Driver đã chuyển tiền, đang chờ bạn xác nhận
                                    </div>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmFromDriverModal">
                                        <i class="bi bi-check-circle"></i> Xác nhận đã nhận tiền
                                    </button>
                                @elseif($transaction->shipper_payment_status === 'confirmed')
                                    <div class="alert alert-success mb-0">
                                        <p class="mb-1"><i class="bi bi-check-circle"></i> Đã xác nhận nhận tiền</p>
                                        <small>Thời gian: {{ $transaction->hub_confirm_time->format('d/m/Y H:i') }}</small><br>
                                        @if($transaction->hubConfirmer)
                                        <small>Người xác nhận: {{ $transaction->hubConfirmer->full_name }}</small>
                                        @endif
                                        @if($transaction->hub_confirm_note)
                                        <p class="mb-0 mt-2"><strong>Ghi chú:</strong> {{ $transaction->hub_confirm_note }}</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        <i class="bi bi-clock"></i> Chờ driver chuyển tiền
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- BƯỚC 2: Hub → Sender (COD) -->
                        <div class="timeline-item {{ $transaction->sender_payment_status === 'completed' ? 'completed' : ($transaction->sender_payment_status === 'pending' ? 'pending' : 'waiting') }}">
                            <div class="timeline-marker">
                                @if($transaction->sender_payment_status === 'completed')
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($transaction->sender_payment_status === 'pending')
                                    <i class="bi bi-clock-fill text-warning"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-2">
                                    <i class="bi bi-send"></i> Hub → Sender (COD)
                                    <span class="badge bg-{{ $transaction->sender_payment_status === 'completed' ? 'success' : ($transaction->sender_payment_status === 'pending' ? 'warning' : 'secondary') }} ms-2">
                                        {{ $transaction->sender_status_label }}
                                    </span>
                                </h6>
                                <p class="mb-1"><strong>Số tiền:</strong> <span class="text-success fs-5">{{ number_format($transaction->sender_receive_amount) }}đ</span></p>

                                @if($transaction->sender_payment_status === 'pending')
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle"></i> Cần chuyển tiền COD cho sender
                                    </div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferToSenderModal">
                                        <i class="bi bi-send"></i> Chuyển tiền cho Sender
                                    </button>
                                @elseif($transaction->sender_payment_status === 'completed')
                                    <div class="alert alert-success mb-0">
                                        <p class="mb-1"><i class="bi bi-check-circle"></i> Đã chuyển tiền cho sender</p>
                                        <small>Thời gian: {{ $transaction->sender_transfer_time->format('d/m/Y H:i') }}</small><br>
                                        <small>Phương thức: 
                                            @if($transaction->sender_transfer_method === 'bank_transfer') Chuyển khoản
                                            @elseif($transaction->sender_transfer_method === 'wallet') Ví điện tử
                                            @else Tiền mặt
                                            @endif
                                        </small>
                                        @if($transaction->sender_transfer_proof)
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $transaction->sender_transfer_proof) }}" target="_blank" class="btn btn-sm">
                                                <i class="bi bi-image"></i> Xem chứng từ
                                            </a>
                                        </div>
                                        @endif
                                        @if($transaction->sender_transfer_note)
                                        <p class="mb-0 mt-2"><strong>Ghi chú:</strong> {{ $transaction->sender_transfer_note }}</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        <i class="bi bi-lock"></i> Chưa sẵn sàng (cần xác nhận bước 1)
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- BƯỚC 3: Hub → Driver (Commission) -->
                        <div class="timeline-item {{ $transaction->driver_commission_status === 'paid' ? 'completed' : ($transaction->driver_commission_status === 'pending' && $transaction->shipper_payment_status === 'confirmed' ? 'pending' : 'waiting') }}">
                            <div class="timeline-marker">
                                @if($transaction->driver_commission_status === 'paid')
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($transaction->driver_commission_status === 'pending' && $transaction->shipper_payment_status === 'confirmed')
                                    <i class="bi bi-clock-fill text-warning"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-2">
                                    <i class="bi bi-cash"></i> Hub → Driver (Commission)
                                    <span class="badge bg-{{ $transaction->driver_commission_status === 'paid' ? 'success' : 'secondary' }} ms-2">
                                        {{ $transaction->driver_commission_status_label }}
                                    </span>
                                </h6>
                                <p class="mb-1"><strong>Commission:</strong> <span class="text-primary fs-5">{{ number_format($transaction->driver_commission) }}đ</span></p>
                                <small class="text-muted">= {{ number_format($transaction->shipping_fee) }}đ × {{ config('delivery.driver_commission_rate') * 100 }}%</small>

                                @if($transaction->canPayDriverCommission())
                                    <div class="alert alert-info mb-3 mt-2">
                                        <i class="bi bi-info-circle"></i> Cần trả commission cho driver
                                    </div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payDriverCommissionModal">
                                        <i class="bi bi-cash"></i> Trả commission
                                    </button>
                                @elseif($transaction->driver_commission_status === 'paid')
                                    <div class="alert alert-success mb-0 mt-2">
                                        <p class="mb-1"><i class="bi bi-check-circle"></i> Đã trả commission</p>
                                        <small>Thời gian: {{ $transaction->driver_paid_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0 mt-2">
                                        <i class="bi bi-lock"></i> Chưa sẵn sàng (cần xác nhận bước 1)
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- BƯỚC 4: Hub → System (COD Fee) -->
                        <div class="timeline-item {{ $transaction->hub_system_status === 'confirmed' ? 'completed' : ($transaction->hub_system_status === 'transferred' ? 'pending' : ($transaction->hub_system_status === 'pending' ? 'pending' : 'waiting')) }}">
                            <div class="timeline-marker">
                                @if($transaction->hub_system_status === 'confirmed')
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($transaction->hub_system_status === 'transferred' || $transaction->hub_system_status === 'pending')
                                    <i class="bi bi-clock-fill text-warning"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-2">
                                    <i class="bi bi-database"></i> Hub → System (COD Fee)
                                    <span class="badge bg-{{ $transaction->hub_system_status === 'confirmed' ? 'success' : ($transaction->hub_system_status === 'transferred' ? 'info' : ($transaction->hub_system_status === 'pending' ? 'warning' : 'secondary')) }} ms-2">
                                        {{ $transaction->system_status_label }}
                                    </span>
                                </h6>
                                <p class="mb-1"><strong>Số tiền:</strong> <span class="text-danger fs-5">{{ number_format($transaction->hub_system_amount) }}đ</span></p>

                                @if($transaction->hub_system_status === 'pending')
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle"></i> Cần nộp COD fee cho hệ thống
                                    </div>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#transferToSystemModal">
                                        <i class="bi bi-database"></i> Nộp cho hệ thống
                                    </button>
                                @elseif($transaction->hub_system_status === 'transferred')
                                    <div class="alert alert-warning mb-0">
                                        <p class="mb-1"><i class="bi bi-clock"></i> Đã nộp, chờ admin xác nhận</p>
                                        <small>Thời gian: {{ $transaction->hub_system_transfer_time->format('d/m/Y H:i') }}</small><br>
                                        <small>Phương thức: 
                                            @if($transaction->hub_system_method === 'bank_transfer') Chuyển khoản
                                            @else Tiền mặt
                                            @endif
                                        </small>
                                        @if($transaction->hub_system_proof)
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $transaction->hub_system_proof) }}" target="_blank" class="btn btn-sm ">
                                                <i class="bi bi-image"></i> Xem chứng từ
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                @elseif($transaction->hub_system_status === 'confirmed')
                                    <div class="alert alert-success mb-0">
                                        <p class="mb-1"><i class="bi bi-check-circle"></i> Admin đã xác nhận nhận tiền</p>
                                        <small>Thời gian: {{ $transaction->system_confirm_time->format('d/m/Y H:i') }}</small>
                                        @if($transaction->systemConfirmer)
                                        <br><small>Người xác nhận: {{ $transaction->systemConfirmer->full_name }}</small>
                                        @endif
                                        @if($transaction->system_confirm_note)
                                        <p class="mb-0 mt-2"><strong>Ghi chú:</strong> {{ $transaction->system_confirm_note }}</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">
                                        <i class="bi bi-lock"></i> Chưa sẵn sàng (cần xác nhận bước 1)
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PHÂN CHIA TIỀN -->
            <div class="card shadow mb-4">
                <div class="card-header text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Phân chia tiền</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Driver thu</h6>
                                <h4 class="text-primary mb-0">{{ number_format($transaction->total_collected) }}đ</h4>
                                <small class="text-muted">Từ khách hàng</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Sender nhận</h6>
                                <h4 class="text-success mb-0">{{ number_format($transaction->sender_receive_amount) }}đ</h4>
                                <small class="text-muted">Tiền COD</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Driver Commission</h6>
                                <h4 class="text-primary mb-0">{{ number_format($transaction->driver_commission) }}đ</h4>
                                <small class="text-muted">{{ config('delivery.driver_commission_rate') * 100 }}% phí ship</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Hub lợi nhuận</h6>
                                <h4 class="text-warning mb-0">{{ number_format($transaction->shipping_fee - $transaction->driver_commission) }}đ</h4>
                                <small class="text-muted">Từ phí ship</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Hub nộp System</h6>
                                <h4 class="text-danger mb-0">{{ number_format($transaction->hub_system_amount) }}đ</h4>
                                <small class="text-muted">COD Fee ({{ number_format(($transaction->platform_fee / $transaction->cod_amount) * 100, 1) }}%)</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="text-muted mb-2">Hub tổng cộng</h6>
                                <h4 class="text-success mb-0">{{ number_format($transaction->shipping_fee - $transaction->driver_commission) }}đ</h4>
                                <small class="text-muted">Lợi nhuận ròng</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COL 2: THÔNG TIN LIÊN QUAN -->
        <div class="col-lg-4">
            <!-- THÔNG TIN ĐƠN HÀNG -->
            <div class="card shadow mb-4">
                <div class="card-header  text-white">
                    <h6 class="mb-0"><i class="bi bi-box"></i> Thông tin đơn hàng</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Mã đơn:</strong></td>
                            <td><a href="{{ route('hub.orders.show', $transaction->order_id) }}" target="_blank">#{{ $transaction->order_id }}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Tiền COD:</strong></td>
                            <td>{{ number_format($transaction->cod_amount) }}đ</td>
                        </tr>
                        <tr>
                            <td><strong>Phí ship:</strong></td>
                            <td>{{ number_format($transaction->shipping_fee) }}đ</td>
                        </tr>
                        <tr>
                            <td><strong>Phí COD:</strong></td>
                            <td>{{ number_format($transaction->platform_fee) }}đ</td>
                        </tr>
                        <tr>
                            <td><strong>Người trả ship:</strong></td>
                            <td>
                                @if($transaction->payer_shipping === 'sender')
                                    <span class="badge bg-info">Người gửi</span>
                                @else
                                    <span class="badge bg-warning">Người nhận</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tạo lúc:</strong></td>
                            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- DRIVER -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-truck"></i> Tài xế</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $transaction->driver->full_name }}</strong></p>
                    <p class="mb-1"><i class="bi bi-phone"></i> {{ $transaction->driver->phone }}</p>
                    <p class="mb-1"><i class="bi bi-envelope"></i> {{ $transaction->driver->email }}</p>
                    @if($transaction->shipperBankAccount)
                    <hr>
                    <p class="mb-1 text-muted"><small>Tài khoản ngân hàng:</small></p>
                    <p class="mb-0"><strong>{{ $transaction->shipperBankAccount->bank_short_name ?? $transaction->shipperBankAccount->bank_name }}</strong></p>
                    <p class="mb-0">{{ $transaction->shipperBankAccount->account_number }}</p>
                    <p class="mb-0">{{ $transaction->shipperBankAccount->account_name }}</p>
                    @endif
                </div>
            </div>

            <!-- SENDER -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Người gửi</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $transaction->sender->full_name }}</strong></p>
                    <p class="mb-1"><i class="bi bi-phone"></i> {{ $transaction->sender->phone }}</p>
                    <p class="mb-1"><i class="bi bi-envelope"></i> {{ $transaction->sender->email }}</p>
                    @if($senderBankAccount)
                    <hr>
                    <p class="mb-1 text-muted"><small>Tài khoản nhận COD:</small></p>
                    <p class="mb-0"><strong>{{ $senderBankAccount->bank_short_name ?? $senderBankAccount->bank_name }}</strong></p>
                    <p class="mb-0">{{ $senderBankAccount->account_number }}</p>
                    <p class="mb-0">{{ $senderBankAccount->account_name }}</p>

                    @if($transaction->sender_payment_status === 'pending')
                    <button type="button" class="btn btn-sm btn-primary mt-2 w-100" 
                            onclick="showSenderQR('{{ $senderBankAccount->bank_code }}', '{{ $senderBankAccount->account_number }}', '{{ $transaction->sender_receive_amount }}', 'COD don {{ $transaction->order_id }}')">
                        <i class="bi bi-qr-code"></i> Tạo QR chuyển khoản
                    </button>
                    @endif
                    @endif
                </div>
            </div>

            <!-- TRẠNG THÁI TỔNG QUÁT -->
            @if($transaction->isFullyCompleted())
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <strong>Giao dịch đã hoàn tất</strong>
                <hr>
                <small>Tất cả các bước đã được thực hiện thành công</small>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL: XÁC NHẬN NHẬN TIỀN TỪ DRIVER -->
<div class="modal fade" id="confirmFromDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hub.cod.confirm', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-header  text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Xác nhận nhận tiền từ Driver</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert ">
                        <p class="mb-2"><strong>Driver:</strong> {{ $transaction->driver->full_name }}</p>
                        <p class="mb-2"><strong>Số tiền nhận:</strong> <span class="fs-5 text-primary">{{ number_format($transaction->total_collected) }}đ</span></p>
                        @if($transaction->shipper_transfer_time)
                        <p class="mb-0"><small>Driver chuyển lúc: {{ $transaction->shipper_transfer_time->format('d/m/Y H:i') }}</small></p>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú xác nhận</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú (nếu có)..."></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Sau khi xác nhận, bạn sẽ có thể:
                        <ul class="mb-0 mt-2">
                            <li>Chuyển COD cho Sender</li>
                            <li>Trả commission cho Driver</li>
                            <li>Nộp COD fee cho hệ thống</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận đã nhận tiền</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: CHUYỂN TIỀN CHO SENDER -->
<div class="modal fade" id="transferToSenderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('hub.cod.transfer-sender', $transaction->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header text-white">
                    <h5 class="modal-title"><i class="bi bi-send"></i> Chuyển tiền COD cho Sender</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert">
                        <p class="mb-2"><strong>Sender:</strong> {{ $transaction->sender->full_name }}</p>
                        <p class="mb-0"><strong>Số tiền COD:</strong> <span class="fs-5 text-success">{{ number_format($transaction->sender_receive_amount) }}đ</span></p>
                    </div>

                    @if($senderBankAccount)
                    <div class="alert alert-warning ">
                        <p class="mb-1"><strong>Tài khoản nhận:</strong></p>
                        <p class="mb-1">{{ $senderBankAccount->bank_short_name ?? $senderBankAccount->bank_name }}</p>
                        <p class="mb-1">{{ $senderBankAccount->account_number }} - {{ $senderBankAccount->account_name }}</p>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phương thức chuyển <span class="text-danger">*</span></label>
                        <select name="method" class="form-select" required>
                            <option value="">-- Chọn phương thức --</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                            <option value="wallet">Ví điện tử</option>
                            <option value="cash">Tiền mặt</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tài khoản Hub chuyển từ</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">-- Không chọn --</option>
                            @foreach($hubBankAccounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ $acc->bank_short_name ?? $acc->bank_name }} - {{ $acc->account_number }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ảnh chứng từ</label>
                        <input type="file" name="proof" class="form-control" accept="image/*">
                        <small class="text-muted">Upload ảnh xác nhận đã chuyển tiền (tối đa 5MB)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú (nếu có)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Xác nhận đã chuyển</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: TRẢ COMMISSION CHO DRIVER -->
<div class="modal fade" id="payDriverCommissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hub.cod.pay-driver-commission', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-header  text-white">
                    <h5 class="modal-title"><i class="bi bi-cash"></i> Trả commission cho Driver</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert ">
                        <p class="mb-2"><strong>Driver:</strong> {{ $transaction->driver->full_name }}</p>
                        <p class="mb-2"><strong>Commission:</strong> <span class="fs-5 text-primary">{{ number_format($transaction->driver_commission) }}đ</span></p>
                        <p class="mb-0"><small>= {{ number_format($transaction->shipping_fee) }}đ × {{ config('delivery.driver_commission_rate') * 100 }}%</small></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú (nếu có)..."></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-info-circle"></i> Xác nhận bạn đã trả commission cho driver này
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Xác nhận đã trả</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="transferToSystemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('hub.cod.transfer-system') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="transaction_ids[]" value="{{ $transaction->id }}">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-database"></i> Nộp COD Fee cho Hệ thống</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert">
                        <p class="mb-2"><strong>Giao dịch:</strong> #{{ $transaction->id }}</p>
                        <p class="mb-0"><strong>Số tiền nộp:</strong> <span class="fs-5">{{ number_format($transaction->hub_system_amount) }}đ</span></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Phương thức nộp <span class="text-danger">*</span></label>
                        <select name="method" id="systemMethod" class="form-select" required>
                            <option value="">-- Chọn phương thức --</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                            <option value="cash">Tiền mặt</option>
                        </select>
                    </div>

                    <div class="mb-3" id="systemBankInfo" style="display: none;">
                        <label class="form-label fw-bold">Thông tin tài khoản Hệ thống</label>
                        <div id="systemBankInfoContent">
                            <!-- Sẽ được load bằng JS -->
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm mt-2" onclick="generateSystemQR()">
                            <i class="bi bi-qr-code"></i> Tạo mã QR chuyển khoản
                        </button>
                        <div id="systemQrCode" class="mt-3 text-center"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ảnh chứng từ <span class="text-danger">*</span></label>
                        <input type="file" name="proof" class="form-control" accept="image/*" required>
                        <small class="text-muted">Bắt buộc upload ảnh xác nhận đã chuyển tiền (tối đa 5MB)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú (nếu có)..."></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Sau khi nộp, giao dịch sẽ chờ Admin hệ thống xác nhận
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận đã nộp</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL: SHOW QR CODE FOR SENDER -->
<div class="modal fade" id="senderQrModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-qr-code"></i> QR Code chuyển khoản cho Sender</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="senderQrContent">
                <!-- QR will be inserted here -->
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline styles */
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
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item.completed .timeline-content {
    opacity: 1;
}

.timeline-item.pending .timeline-content {
    opacity: 1;
}

.timeline-item.waiting .timeline-content {
    opacity: 0.6;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    box-shadow: 0 0 0 4px #fff;
}

.timeline-marker i {
    font-size: 20px;
}

.timeline-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #dee2e6;
}

.timeline-item.completed .timeline-content {
    border-left-color: #28a745;
}

.timeline-item.pending .timeline-content {
    border-left-color: #ffc107;
}

.timeline-item.waiting .timeline-content {
    border-left-color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide bank info based on method in transfer to system modal
    const systemMethodSelect = document.getElementById('systemMethod');
    if (systemMethodSelect) {
        systemMethodSelect.addEventListener('change', function() {
            const bankInfo = document.getElementById('systemBankInfo');
            if (this.value === 'bank_transfer') {
                bankInfo.style.display = 'block';
            } else {
                bankInfo.style.display = 'none';
            }
        });
    }
});

// Generate System QR Code
function generateSystemQR() {
    const amount = {{ $transaction->hub_system_amount }};
    const bankCode = '{{ config("system.bank_code", "VCB") }}';
    const accountNo = '{{ config("system.bank_account", "1234567890") }}';
    const content = 'COD {{ $transaction->id }}';
    
    const qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNo}-compact2.jpg?amount=${amount}&addInfo=${encodeURIComponent(content)}`;
    
    document.getElementById('systemQrCode').innerHTML = `
        <img src="${qrUrl}" alt="QR Code" class="img-fluid" style="max-width: 300px; border-radius: 8px;">
        <p class="mt-3 mb-0"><strong>Số tiền: ${new Intl.NumberFormat('vi-VN').format(amount)}đ</strong></p>
        <p class="mb-0"><small class="text-muted">Quét mã QR để chuyển khoản</small></p>
    `;
}

// Show QR for Sender transfer
function showSenderQR(bankCode, accountNo, amount, content) {
    const qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNo}-compact2.jpg?amount=${amount}&addInfo=${encodeURIComponent(content)}`;
    
    const modalContent = `
        <img src="${qrUrl}" alt="QR Code" class="img-fluid mb-3" style="max-width: 300px; border-radius: 8px;">
        <h5 class="mb-2">${new Intl.NumberFormat('vi-VN').format(amount)}đ</h5>
        <p class="mb-1"><strong>Nội dung:</strong> ${content}</p>
        <hr>
        <div class="text-start">
            <p class="mb-1"><strong>Ngân hàng:</strong> ${bankCode}</p>
            <p class="mb-1"><strong>Số TK:</strong> ${accountNo}</p>
        </div>
    `;
    
    document.getElementById('senderQrContent').innerHTML = modalContent;
    
    const modal = new bootstrap.Modal(document.getElementById('senderQrModal'));
    modal.show();
}
</script>
<script>
// Global variable to store system bank info
let systemBankInfo = null;

document.addEventListener('DOMContentLoaded', function() {
    // Show/hide bank info based on method in transfer to system modal
    const systemMethodSelect = document.getElementById('systemMethod');
    if (systemMethodSelect) {
        systemMethodSelect.addEventListener('change', function() {
            const bankInfo = document.getElementById('systemBankInfo');
            if (this.value === 'bank_transfer') {
                bankInfo.style.display = 'block';
                // Load system bank info
                loadSystemBankInfo();
            } else {
                bankInfo.style.display = 'none';
            }
        });
    }
});

// ✅ Load System Bank Info từ API
function loadSystemBankInfo() {
    // Nếu đã load rồi thì không load lại
    if (systemBankInfo) {
        displaySystemBankInfo(systemBankInfo);
        return;
    }

    const amount = {{ $transaction->hub_system_amount }};
    const content = 'COD {{ $transaction->id }}';
    
    fetch(`{{ route('hub.cod.api.system-qr') }}?amount=${amount}&content=${encodeURIComponent(content)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                systemBankInfo = data.bank_info;
                displaySystemBankInfo(data.bank_info);
            } else {
                document.getElementById('systemBankInfoContent').innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle"></i> ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading system bank info:', error);
            document.getElementById('systemBankInfoContent').innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Không thể tải thông tin tài khoản hệ thống
                </div>
            `;
        });
}

// Display System Bank Info
function displaySystemBankInfo(bankInfo) {
    document.getElementById('systemBankInfoContent').innerHTML = `
        <div class="alert alert-secondary mb-0">
            <p class="mb-1"><strong>Ngân hàng:</strong> ${bankInfo.bank_short_name || bankInfo.bank_name}</p>
            <p class="mb-1"><strong>Số tài khoản:</strong> <code class="text-dark">${bankInfo.account_number}</code></p>
            <p class="mb-1"><strong>Chủ tài khoản:</strong> ${bankInfo.account_name}</p>
            <p class="mb-0"><strong>Nội dung:</strong> <code class="text-dark">COD {{ $transaction->id }}</code></p>
        </div>
    `;
}

// Generate System QR Code
function generateSystemQR() {
    const amount = {{ $transaction->hub_system_amount }};
    const content = 'COD {{ $transaction->id }}';
    
    // Hiển thị loading
    document.getElementById('systemQrCode').innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    
    fetch(`{{ route('hub.cod.api.system-qr') }}?amount=${amount}&content=${encodeURIComponent(content)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('systemQrCode').innerHTML = `
                    <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <p class="mt-3 mb-0"><strong>Số tiền: ${new Intl.NumberFormat('vi-VN').format(amount)}đ</strong></p>
                    <p class="mb-0"><small class="text-muted">Quét mã QR để chuyển khoản</small></p>
                `;
            } else {
                document.getElementById('systemQrCode').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${data.error}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error generating QR:', error);
            document.getElementById('systemQrCode').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Không thể tạo mã QR
                </div>
            `;
        });
}

// Show QR for Sender transfer
function showSenderQR(bankCode, accountNo, amount, content) {
    const qrUrl = `https://img.vietqr.io/image/${bankCode}-${accountNo}-compact2.jpg?amount=${amount}&addInfo=${encodeURIComponent(content)}`;
    
    const modalContent = `
        <img src="${qrUrl}" alt="QR Code" class="img-fluid mb-3" style="max-width: 300px; border-radius: 8px;">
        <h5 class="mb-2">${new Intl.NumberFormat('vi-VN').format(amount)}đ</h5>
        <p class="mb-1"><strong>Nội dung:</strong> ${content}</p>
        <hr>
        <div class="text-start">
            <p class="mb-1"><strong>Ngân hàng:</strong> ${bankCode}</p>
            <p class="mb-1"><strong>Số TK:</strong> ${accountNo}</p>
        </div>
    `;
    
    document.getElementById('senderQrContent').innerHTML = modalContent;
    
    const modal = new bootstrap.Modal(document.getElementById('senderQrModal'));
    modal.show();
}
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    });
</script>
@endif

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            html: '<ul class="text-start">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
        });
    });
</script>
@endif
@endsection