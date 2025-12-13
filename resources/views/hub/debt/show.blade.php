@extends('hub.layouts.app')
@section('title', 'Chi tiết thanh toán nợ #' . $transaction->id)

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold">
                <i class="bi bi-credit-card-2-back text-danger"></i> Chi tiết thanh toán nợ #{{ $transaction->id }}
            </h3>
            <p class="text-muted mb-0 mt-1">
                <i class="bi bi-box-seam"></i> Đơn hàng #{{ $transaction->order_id }} • 
                <i class="bi bi-calendar3"></i> {{ $transaction->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div>
            <a href="{{ route('hub.debt.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- COL 1: THÔNG TIN THANH TOÁN -->
        <div class="col-lg-8">
            <!-- TRẠNG THÁI -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header text-white py-3" 
                     style="background: linear-gradient(135deg, 
                            {{ $transaction->sender_debt_payment_status === 'completed' ? '#28a745, #20c997' : 
                               ($transaction->sender_debt_payment_status === 'rejected' ? '#dc3545, #c82333' : '#ffc107, #e0a800') }});">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-{{ $transaction->sender_debt_payment_status === 'completed' ? 'check-circle' : 
                                             ($transaction->sender_debt_payment_status === 'rejected' ? 'x-circle' : 'clock') }}"></i> 
                            Trạng thái: 
                            @if($transaction->sender_debt_payment_status === 'pending')
                                Chờ xác nhận
                            @elseif($transaction->sender_debt_payment_status === 'completed')
                                Đã xác nhận
                            @elseif($transaction->sender_debt_payment_status === 'rejected')
                                Đã từ chối
                            @else
                                Chưa thanh toán
                            @endif
                        </h5>
                        @if($transaction->sender_debt_payment_status === 'pending')
                        <div>
                            <button type="button" class="btn btn-light btn-sm me-2" 
                                    data-bs-toggle="modal" data-bs-target="#confirmModal">
                                <i class="bi bi-check"></i> Xác nhận
                            </button>
                            <button type="button" class="btn btn-outline-light btn-sm" 
                                    data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x"></i> Từ chối
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($transaction->sender_debt_payment_status === 'pending')
                        <div class="alert alert-warning border-warning">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                <div>
                                    <strong>Sender đã chuyển tiền trả nợ</strong>
                                    <p class="mb-0 mt-2">Vui lòng kiểm tra và xác nhận đã nhận được tiền.</p>
                                    @if($transaction->sender_debt_paid_at)
                                        <p class="mb-0 mt-2 small">
                                            <i class="bi bi-clock"></i> Thời gian: {{ $transaction->sender_debt_paid_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($transaction->sender_debt_payment_status === 'completed')
                        <div class="alert alert-success border-success">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                <div class="flex-grow-1">
                                    <strong>Đã xác nhận nhận tiền</strong>
                                    @if($transaction->sender_debt_confirmed_at)
                                        <p class="mb-1 mt-2 small">
                                            <i class="bi bi-clock"></i> {{ $transaction->sender_debt_confirmed_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                    @if($transaction->debtConfirmer)
                                        <p class="mb-0 small">
                                            <i class="bi bi-person"></i> Xác nhận bởi: {{ $transaction->debtConfirmer->full_name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($transaction->sender_debt_payment_status === 'rejected')
                        <div class="alert alert-danger border-danger">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-x-circle-fill fs-4 me-3"></i>
                                <div class="flex-grow-1">
                                    <strong>Đã từ chối</strong>
                                    @if($transaction->sender_debt_rejected_at)
                                        <p class="mb-1 mt-2 small">
                                            <i class="bi bi-clock"></i> {{ $transaction->sender_debt_rejected_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                    @if($transaction->debtRejecter)
                                        <p class="mb-1 small">
                                            <i class="bi bi-person"></i> Từ chối bởi: {{ $transaction->debtRejecter->full_name }}
                                        </p>
                                    @endif
                                    @if($transaction->sender_debt_rejection_reason)
                                        <p class="mb-0 mt-2">
                                            <strong>Lý do:</strong> {{ $transaction->sender_debt_rejection_reason }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-secondary border">
                            <i class="bi bi-info-circle"></i> Sender chưa thanh toán nợ
                        </div>
                    @endif
                </div>
            </div>

            <!-- THÔNG TIN THANH TOÁN -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-credit-card"></i> Thông tin thanh toán
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block mb-2">Số tiền trả nợ</small>
                                <h3 class="text-danger mb-0 fw-bold">{{ number_format($transaction->sender_fee_paid) }}₫</h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 bg-light">
                                <small class="text-muted d-block mb-2">Phương thức thanh toán</small>
                                <div class="fw-semibold">
                                    @if($transaction->sender_debt_payment_method === 'bank_transfer')
                                        <i class="bi bi-bank text-primary"></i> Chuyển khoản ngân hàng
                                    @elseif($transaction->sender_debt_payment_method === 'cash')
                                        <i class="bi bi-cash text-success"></i> Tiền mặt
                                    @else
                                        <span class="text-muted">Chưa xác định</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($transaction->sender_debt_payment_proof)
                    <div class="mt-4">
                        <label class="form-label fw-bold mb-3">
                            <i class="bi bi-image"></i> Chứng từ thanh toán:
                        </label>
                        <div class="text-center p-3 border rounded bg-light">
                            <img src="{{ asset('storage/' . $transaction->sender_debt_payment_proof) }}" 
                                 alt="Chứng từ" 
                                 class="img-fluid rounded shadow-sm" 
                                 style="max-height: 400px; cursor: pointer;"
                                 onclick="window.open(this.src)">
                            <div class="mt-3">
                                <a href="{{ asset('storage/' . $transaction->sender_debt_payment_proof) }}" 
                                   target="_blank" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-download"></i> Tải xuống
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- LỊCH SỬ NỢ -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-clock-history"></i> Lịch sử nợ của Sender
                    </h6>
                </div>
                <div class="card-body">
                    @if($debtHistory->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Không có lịch sử nợ
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Thời gian</th>
                                        <th>Đơn hàng</th>
                                        <th>Loại</th>
                                        <th class="text-end">Số tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($debtHistory as $debt)
                                    <tr>
                                        <td>
                                            <small>{{ $debt->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($debt->order_id)
                                                <a href="{{ route('hub.orders.show', $debt->order_id) }}" target="_blank">
                                                    #{{ $debt->order_id }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($debt->type === 'debt')
                                                <span class="badge bg-danger">Nợ phát sinh</span>
                                            @else
                                                <span class="badge bg-success">Trừ nợ</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="{{ $debt->type === 'debt' ? 'text-danger' : 'text-success' }} fw-bold">
                                                {{ $debt->type === 'debt' ? '+' : '-' }}{{ number_format($debt->amount) }}₫
                                            </span>
                                        </td>
                                        <td>
                                            @if($debt->status === 'paid')
                                                <span class="badge bg-success">Đã thanh toán</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- COL 2: THÔNG TIN LIÊN QUAN -->
        <div class="col-lg-4">
            <!-- THÔNG TIN SENDER -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-person"></i> Thông tin Sender
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Họ tên</td>
                            <td class="fw-semibold">{{ $transaction->sender->full_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Số điện thoại</td>
                            <td class="fw-semibold">{{ $transaction->sender->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td>{{ $transaction->sender->email ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- THÔNG TIN ĐƠN HÀNG -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-box-seam"></i> Thông tin đơn hàng
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Mã đơn</td>
                            <td>
                                <a href="{{ route('hub.orders.show', $transaction->order_id) }}" 
                                   target="_blank" 
                                   class="fw-semibold">
                                    #{{ $transaction->order_id }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">COD Amount</td>
                            <td class="fw-semibold">{{ number_format($transaction->cod_amount) }}₫</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Shipping Fee</td>
                            <td class="fw-semibold">{{ number_format($transaction->shipping_fee) }}₫</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày tạo</td>
                            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- THỐNG KÊ NỢ -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-graph-up"></i> Tổng quan nợ
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $totalDebt = $debtHistory->where('type', 'debt')->where('status', 'unpaid')->sum('amount');
                    @endphp
                    <div class="text-center mb-3 p-3 bg-light rounded">
                        <small class="text-muted d-block mb-2">Tổng nợ hiện tại</small>
                        <h3 class="text-{{ $totalDebt > 0 ? 'danger' : 'success' }} mb-0 fw-bold">
                            {{ number_format($totalDebt) }}₫
                        </h3>
                    </div>
                    <hr>
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">Đã trừ nợ</small>
                                <strong class="text-success">
                                    {{ number_format($debtHistory->where('type', 'deduction')->sum('amount')) }}₫
                                </strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">Tổng nợ phát sinh</small>
                                <strong class="text-danger">
                                    {{ number_format($debtHistory->where('type', 'debt')->sum('amount')) }}₫
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL XÁC NHẬN -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hub.debt.confirm', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Xác nhận đã nhận tiền
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="mb-2"><strong>Sender:</strong> {{ $transaction->sender->full_name }}</p>
                        <p class="mb-0"><strong>Số tiền:</strong> 
                            <span class="text-danger fs-5 fw-bold">{{ number_format($transaction->sender_debt_deducted) }}₫</span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú (tùy chọn)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú..."></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Lưu ý:</strong> Sau khi xác nhận, bạn xác nhận đã nhận được tiền trả nợ từ Sender.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check"></i> Xác nhận đã nhận tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TỪ CHỐI -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hub.debt.reject', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle"></i> Từ chối thanh toán
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Bạn đang từ chối thanh toán nợ {{ number_format($transaction->sender_debt_deducted) }}₫ từ {{ $transaction->sender->full_name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" 
                                  placeholder="Nhập lý do từ chối..." required></textarea>
                        <small class="text-muted">Ví dụ: Chứng từ không rõ ràng, số tiền không đúng, v.v.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x"></i> Từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection