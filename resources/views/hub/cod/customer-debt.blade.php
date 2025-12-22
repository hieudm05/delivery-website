{{-- resources/views/hub/cod/customer-debt.blade.php --}}

@extends('hub.layouts.app')
@section('title', 'Chi tiết công nợ khách hàng')

@section('content')
<div class="container">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('hub.cod.index') }}">Quản lý COD</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('hub.cod.index', ['tab' => 'debt_management']) }}">Quản lý nợ</a>
                    </li>
                    <li class="breadcrumb-item active">Chi tiết khách hàng</li>
                </ol>
            </nav>
            <h3 class="mb-0 mt-2">
                <i class="bi bi-person-circle text-danger"></i> 
                Chi tiết công nợ: {{ $customer->full_name }}
            </h3>
        </div>
        <div>
            <a href="{{ route('hub.cod.index', ['tab' => 'debt_management']) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <button type="button" 
                    class="btn btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#sendReminderModal">
                <i class="bi bi-bell"></i> Gửi nhắc nhở
            </button>
        </div>
    </div>

    <!-- THÔNG TIN KHÁCH HÀNG -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-info shadow-sm h-100">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="mb-0">
                        <i class="bi bi-person"></i> Thông tin khách hàng
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td width="40%" class="text-muted">Tên khách hàng:</td>
                            <td><strong>{{ $customer->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Số điện thoại:</td>
                            <td><strong>{{ $customer->phone }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $customer->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Mã khách hàng:</td>
                            <td><code>#{{ $customer->id }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-header bg-danger bg-opacity-10">
                    <h6 class="mb-0 text-danger">
                        <i class="bi bi-exclamation-triangle"></i> Tổng quan công nợ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <p class="text-muted mb-2">Tổng nợ hiện tại</p>
                        <h2 class="text-danger fw-bold mb-3">
                            {{ number_format($totalDebt) }}₫
                        </h2>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <small class="text-muted d-block">Số đơn nợ</small>
                                    <strong class="text-danger fs-5">{{ $debts->count() }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <small class="text-muted d-block">Nợ lâu nhất</small>
                                    <strong class="text-warning fs-6">
                                        {{ $debts->first() ? $debts->first()->created_at->diffForHumans() : 'N/A' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CHI TIẾT CÁC KHOẢN NỢ -->
    <div class="card shadow mb-4">
        <div class="card-header bg-danger bg-opacity-10">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i> Chi tiết các khoản nợ chưa thanh toán
            </h5>
        </div>
        <div class="card-body p-0">
            @if($debts->isEmpty())
                <div class="alert alert-success m-3">
                    <i class="bi bi-check-circle"></i> Khách hàng này không còn nợ
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Đơn hàng</th>
                                <th>Loại nợ</th>
                                <th>Số tiền</th>
                                <th>Ngày phát sinh</th>
                                <th>Thời gian</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debts as $index => $debt)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($debt->order)
                                        <a href="{{ route('hub.orders.show', $debt->order_id) }}" target="_blank">
                                            <i class="bi bi-box-seam"></i> #{{ $debt->order_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-danger">Phí hoàn hàng</span>
                                </td>
                                <td>
                                    <strong class="text-danger">{{ number_format($debt->amount) }}₫</strong>
                                </td>
                                <td>
                                    {{ $debt->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        {{ $debt->created_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $debt->note ?? 'Không có ghi chú' }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                <td colspan="4">
                                    <strong class="text-danger fs-5">{{ number_format($totalDebt) }}₫</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- LỊCH SỬ GIAO DỊCH -->
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Lịch sử giao dịch COD
            </h5>
        </div>
        <div class="card-body p-0">
            @if($transactions->isEmpty())
                <div class="alert alert-info m-3">
                    <i class="bi bi-info-circle"></i> Chưa có giao dịch nào
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã GD</th>
                                <th>Đơn hàng</th>
                                <th>Phí hoàn</th>
                                <th>Trạng thái nợ</th>
                                <th>Thời gian</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $trans)
                            <tr>
                                <td><strong>#{{ $trans->id }}</strong></td>
                                <td>
                                    <a href="{{ route('hub.orders.show', $trans->order_id) }}" target="_blank">
                                        #{{ $trans->order_id }}
                                    </a>
                                </td>
                                <td>
                                    <strong class="text-danger">{{ number_format($trans->sender_fee_paid) }}₫</strong>
                                </td>
                                <td>
                                    @if($trans->sender_debt_payment_status === 'completed')
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    @elseif($trans->sender_debt_payment_status === 'pending')
                                        <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                    @elseif($trans->sender_debt_payment_status === 'rejected')
                                        <span class="badge bg-danger">Đã từ chối</span>
                                    @else
                                        <span class="badge bg-secondary">Chưa thanh toán</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $trans->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('hub.cod.show', $trans->id) }}" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ✅ MODAL GỬI NHẮC NHỞ -->
<div class="modal fade" id="sendReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hub.cod.send-debt-reminder', $customer->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning bg-opacity-25">
                    <h5 class="modal-title">
                        <i class="bi bi-bell"></i> Gửi nhắc nhở thanh toán
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3">
                        <p class="mb-1"><strong>Khách hàng:</strong> {{ $customer->full_name }}</p>
                        <p class="mb-0"><strong>Tổng nợ:</strong> 
                            <span class="text-danger fw-bold">{{ number_format($totalDebt) }}₫</span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tin nhắn nhắc nhở (tùy chọn)</label>
                        <textarea name="message" 
                                  class="form-control" 
                                  rows="5" 
                                  placeholder="Kính gửi quý khách {{ $customer->full_name }},

Bưu cục ghi nhận quý khách đang có khoản nợ {{ number_format($totalDebt) }}₫ từ {{ $debts->count() }} đơn hàng hoàn.

Vui lòng thanh toán trong thời gian sớm nhất để tiếp tục sử dụng dịch vụ.

Trân trọng,
{{ Auth::user()->full_name }}"></textarea>
                    </div>

                    <div class="alert alert-warning border-0 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            Hệ thống sẽ gửi thông báo qua app, email và SMS cho khách hàng
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send"></i> Gửi nhắc nhở
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection