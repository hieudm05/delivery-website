@extends('driver.layouts.app')
@section('title', 'Quản lý tiền COD')

@section('content')
<div class="container-fluid py-4">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-wallet2 text-primary"></i> Quản lý tiền COD
            </h3>
            <p class="text-muted mb-0">Theo dõi và nộp tiền thu hộ về bưu cục</p>
        </div>
        @if($stats['count_pending'] > 0)
        <div>
            <a href="{{ route('driver.cod.group-by-date') }}" class="btn btn-success">
                <i class="bi bi-stack"></i> Nộp tiền gộp
            </a>
        </div>
        @endif
    </div>

    <!-- THỐNG KÊ -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ nộp tiền
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_pending']) }}đ
                            </div>
                            <small class="text-muted">{{ $stats['count_pending'] }} giao dịch</small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Chờ xác nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_transferred']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã xác nhận
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_confirmed']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG GIAO DỊCH -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-list-ul"></i> Danh sách giao dịch COD
            </h5>
            <!-- LỌC THEO NGÀY -->
            <form method="GET" class="d-flex gap-2" style="max-width: 300px;">
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date ?? '' }}">
                <button type="submit" class="btn btn-sm btn-primary">Tìm</button>
                @if($date)
                <a href="{{ route('driver.cod.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="card-body">
            <!-- TABS -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="?status=all{{ $date ? '&date=' . $date : '' }}">
                        <i class="bi bi-list"></i> Tất cả
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="?status=pending{{ $date ? '&date=' . $date : '' }}">
                        <i class="bi bi-exclamation-circle"></i> Chờ nộp
                        @if($stats['count_pending'] > 0)
                        <span class="badge bg-warning ms-1">{{ $stats['count_pending'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'transferred' ? 'active' : '' }}" href="?status=transferred{{ $date ? '&date=' . $date : '' }}">
                        <i class="bi bi-clock"></i> Chờ xác nhận
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'confirmed' ? 'active' : '' }}" href="?status=confirmed{{ $date ? '&date=' . $date : '' }}">
                        <i class="bi bi-check-circle"></i> Đã xác nhận
                    </a>
                </li>
            </ul>

            @if($transactions->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Không có giao dịch nào
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Đơn hàng</th>
                                <th>Người gửi</th>
                                <th>Tiền COD</th>
                                <th>Phí ship</th>
                                <th>Tổng nộp</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $trans)
                            <tr>
                                <td>
                                    <a href="{{ route('driver.cod.show', $trans->id) }}" class="text-decoration-none">
                                        <strong>#{{ $trans->order_id }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $trans->sender->full_name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $trans->sender->phone ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($trans->cod_amount) }}đ</strong>
                                </td>
                                <td>{{ number_format($trans->shipping_fee) }}đ</td>
                                <td>
                                    <strong class="text-primary">{{ number_format($trans->total_collected) }}đ</strong>
                                </td>
                                <td>
                                    @if($trans->shipper_payment_status === 'pending')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-circle"></i> Chờ nộp
                                        </span>
                                    @elseif($trans->shipper_payment_status === 'transferred')
                                        <span class="badge bg-info">
                                            <i class="bi bi-clock"></i> Chờ xác nhận
                                        </span>
                                    @elseif($trans->shipper_payment_status === 'confirmed')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Đã xác nhận
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $trans->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    @if($trans->canDriverTransfer())
                                        <a href="{{ route('driver.cod.show', $trans->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-send"></i> Nộp tiền
                                        </a>
                                    @else
                                        <a href="{{ route('driver.cod.show', $trans->id) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                    @endif
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
                        {{ $transactions->appends(['status' => $status, 'date' => $date])->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
</style>
@endsection