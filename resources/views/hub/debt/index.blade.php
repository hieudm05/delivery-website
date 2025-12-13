@extends('hub.layouts.app')
@section('title', 'Xác nhận trả nợ')

@section('content')
<style>
    .modal-content{
        width: 100% !important;
    }
</style>
<div class="container">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold">
                <i class="bi bi-credit-card-2-back text-danger"></i> Xác nhận trả nợ từ Sender
            </h3>
            <p class="text-muted mb-0 mt-1">Quản lý các khoản thanh toán nợ của Sender</p>
        </div>
        <a href="{{ route('hub.debt.statistics') }}" class="btn btn-info">
            <i class="bi bi-graph-up"></i> Thống kê
        </a>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <div class="text-warning fw-bold small">Chờ xác nhận</div>
                        <div class="h4 fw-bold">{{ $stats['pending_confirmation'] }} khoản</div>
                        <small class="text-muted">{{ number_format($stats['pending_amount']) }}₫</small>
                    </div>
                    <i class="bi bi-clock-history display-6 text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <div class="text-success fw-bold small">Đã xác nhận</div>
                        <div class="h4 fw-bold">{{ $stats['confirmed'] }} khoản</div>
                        <small class="text-muted">{{ number_format($stats['confirmed_amount']) }}₫</small>
                    </div>
                    <i class="bi bi-check-circle display-6 text-success opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <div class="text-danger fw-bold small">Đã từ chối</div>
                        <div class="h4 fw-bold">{{ $stats['rejected'] }} khoản</div>
                    </div>
                    <i class="bi bi-x-circle display-6 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold text-primary">
            <i class="bi bi-list-ul"></i> Danh sách thanh toán nợ
        </div>

        <div class="card-body p-0">
            <ul class="nav nav-tabs px-3 pt-3">
                @foreach(['pending_confirmation'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','rejected'=>'Đã từ chối','all'=>'Tất cả'] as $key=>$label)
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="?tab={{ $key }}">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="p-3">
                @if($transactions->isEmpty())
                    <div class="alert alert-info">Không có dữ liệu</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                @if($tab === 'pending_confirmation')
                                    <th width="40">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                @endif
                                <th>Mã GD</th>
                                <th>Sender</th>
                                <th>Số tiền</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($transactions as $trans)
                                <tr>
                                    @if($tab === 'pending_confirmation')
                                        <td>
                                            <input type="checkbox"
                                                   class="form-check-input transaction-checkbox"
                                                   value="{{ $trans->id }}">
                                        </td>
                                    @endif
                                    <td class="fw-bold text-primary">#{{ $trans->id }}</td>
                                    <td>
                                        {{ $trans->sender->full_name ?? 'N/A' }}<br>
                                        <small class="text-muted">{{ $trans->sender->phone ?? '' }}</small>
                                    </td>
                                    <td class="text-danger fw-bold">
                                        {{ number_format($trans->sender_debt_deducted) }}₫
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Chuyển khoản</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            Chờ xác nhận
                                        </span>
                                    </td>
                                    <td>
                                        {{ optional($trans->sender_debt_paid_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmModal{{ $trans->id }}">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $trans->id }}">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $transactions->appends(['tab'=>$tab])->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================= MODALS (OUTSIDE TABLE) ================= --}}
@foreach($transactions as $trans)
@if($trans->sender_debt_payment_status === 'pending')

{{-- CONFIRM MODAL --}}
<div class="modal fade" id="confirmModal{{ $trans->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('hub.debt.confirm', $trans->id) }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Xác nhận đã nhận tiền</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Số tiền:</strong> {{ number_format($trans->sender_debt_deducted) }}₫</p>

                    @if($trans->sender_debt_payment_proof)
                        <img src="{{ asset('storage/'.$trans->sender_debt_payment_proof) }}"
                             class="img-fluid img-thumbnail mb-3">
                    @endif

                    <textarea name="note" class="form-control" placeholder="Ghi chú (tùy chọn)"></textarea>

                    <div class="alert alert-warning mt-3">
                        Sau khi xác nhận, hệ thống ghi nhận đã nhận tiền từ Sender.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-success">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- REJECT MODAL --}}
<div class="modal fade" id="rejectModal{{ $trans->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('hub.debt.reject', $trans->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Từ chối thanh toán</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea name="rejection_reason"
                              class="form-control"
                              rows="4"
                              required
                              placeholder="Nhập lý do từ chối..."></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif
@endforeach
@endsection
