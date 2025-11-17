@extends('customer.dashboard.layouts.app')
@section('title', 'Quản lý tiền COD')

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-cash-stack text-primary"></i> Quản lý tiền COD
            </h3>
            <p class="text-muted mb-0">Theo dõi tiền thu hộ từ các đơn hàng của bạn</p>
        </div>
        <a href="{{ route('customer.cod.statistics') }}" class="btn btn-info">
            <i class="bi bi-graph-up"></i> Thống kê chi tiết
        </a>
    </div>

    <!-- THỐNG KÊ TỔNG QUAN -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng tiền COD
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_cod']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-wallet2 fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_receive']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_pending']) }}đ
                            </div>
                            @if($stats['count_pending'] > 0)
                            <small class="text-muted">{{ $stats['count_pending'] }} giao dịch</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Phí nền tảng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_platform_fee']) }}đ
                            </div>
                            <small class="text-muted">2% trên mỗi đơn COD</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-percent fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG GIAO DỊCH -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-list-ul"></i> Lịch sử giao dịch COD
            </h5>
        </div>

        <div class="card-body">
            <!-- TABS -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="?status=all">
                        <i class="bi bi-list"></i> Tất cả
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'not_ready' ? 'active' : '' }}" href="?status=not_ready">
                        <i class="bi bi-hourglass"></i> Đang xử lý
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="?status=pending">
                        <i class="bi bi-clock"></i> Chờ nhận tiền
                        @if($stats['count_pending'] > 0)
                        <span class="badge bg-warning ms-1">{{ $stats['count_pending'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'completed' ? 'active' : '' }}" href="?status=completed">
                        <i class="bi bi-check-circle"></i> Đã nhận tiền
                    </a>
                </li>
            </ul>

            @if($transactions->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Chưa có giao dịch COD nào
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã GD</th>
                                <th>Đơn hàng</th>
                                <th>Tiền COD</th>
                                <th>Phí (2%)</th>
                                <th>Bạn nhận</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $trans)
                            <tr>
                                <td><strong class="text-primary">#{{ $trans->id }}</strong></td>
                                <td>
                                    <a href="{{ route('customer.orderManagent.show', $trans->order_id) }}" target="_blank">
                                        <i class="bi bi-box-seam"></i> #{{ $trans->order_id }}
                                    </a>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($trans->cod_amount) }}đ</strong>
                                </td>
                                <td class="text-danger">-{{ number_format($trans->platform_fee) }}đ</td>
                                <td>
                                    <strong class="text-primary">{{ number_format($trans->sender_receive_amount) }}đ</strong>
                                </td>
                                <td>
                                    @if($trans->sender_payment_status === 'not_ready')
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-hourglass"></i> Đang xử lý
                                        </span>
                                        <br><small class="text-muted">Chờ tài xế nộp về hub</small>
                                    @elseif($trans->sender_payment_status === 'pending')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Chờ hub trả tiền
                                        </span>
                                    @elseif($trans->sender_payment_status === 'completed')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Đã nhận tiền
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $trans->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('customer.cod.show', $trans->id) }}" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
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
                        {{ $transactions->appends(['status' => $status])->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- HƯỚNG DẪN -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Lưu ý về thanh toán COD
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Phí nền tảng:</strong> Hệ thống thu 2% trên mỗi đơn COD để duy trì dịch vụ</li>
                        <li><strong>Thời gian nhận tiền:</strong> Sau khi tài xế nộp tiền về hub, hub sẽ chuyển cho bạn trong vòng 1-3 ngày làm việc</li>
                        <li><strong>Phương thức nhận:</strong> Tiền sẽ được chuyển vào tài khoản ngân hàng chính của bạn</li>
                        <li><strong>Hỗ trợ:</strong> Nếu quá 5 ngày chưa nhận được tiền, vui lòng liên hệ bộ phận hỗ trợ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.border-left-danger { border-left: 4px solid #e74a3b !important; }
</style>
@endsection