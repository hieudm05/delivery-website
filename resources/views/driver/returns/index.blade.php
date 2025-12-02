@extends('driver.layouts.app')

@section('title', 'Danh sách hoàn hàng')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-undo text-warning"></i> Danh sách hoàn hàng</h4>
            <p class="text-muted small mb-0">Quản lý các đơn cần hoàn về sender</p>
        </div>
        <a href="{{ route('driver.delivery.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-truck"></i> Đơn giao hàng
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Đã phân công</h6>
                            <h3 class="mb-0">{{ $stats['assigned'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Đang hoàn về</h6>
                            <h3 class="mb-0">{{ $stats['returning'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Đã hoàn thành</h6>
                            <h3 class="mb-0">{{ $stats['completed'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-dark bg-gradient text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Tổng đơn hoàn</h6>
                            <h3 class="mb-0">{{ $stats['assigned'] + $stats['returning'] + $stats['completed'] }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Trạng thái</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="assigned" {{ $status == 'assigned' ? 'selected' : '' }}>Đã phân công</option>
                        <option value="returning" {{ $status == 'returning' ? 'selected' : '' }}>Đang hoàn về</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Mã đơn, tên sender, SĐT..." 
                           value="{{ $search }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách đơn hoàn ({{ $returns->total() }})</h6>
        </div>
        <div class="card-body p-0">
            @if($returns->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Không có đơn hoàn nào</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Sender</th>
                                <th>Địa chỉ</th>
                                <th>Lý do hoàn</th>
                                <th>Trạng thái</th>
                                <th>Phí hoàn</th>
                                <th>Khởi tạo</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returns as $return)
                            <tr>
                                <td>
                                    <strong>#{{ $return->order->id }}</strong>
                                    @if($return->failed_attempts >= 3)
                                        <span class="badge bg-danger ms-1" title="Thất bại {{ $return->failed_attempts }} lần">
                                            {{ $return->failed_attempts }}x
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $return->sender_name }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-phone"></i> {{ $return->sender_phone }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted" style="max-width: 200px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $return->sender_address }}
                                    </small>
                                    @if($return->sender_latitude && $return->sender_longitude)
                                        <a href="{{ $return->sender_map_link }}" target="_blank" class="small">
                                            <i class="fas fa-map-marker-alt"></i> Xem bản đồ
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $return->reason_type === 'auto_failed' ? 'danger' : 'warning' }}">
                                        {{ $return->reason_type_label }}
                                    </span>
                                    @if($return->reason_detail)
                                        <br><small class="text-muted">{{ Str::limit($return->reason_detail, 30) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $return->status_badge }}">
                                        {{ $return->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-danger">{{ number_format($return->return_fee) }}đ</strong>
                                </td>
                                <td>
                                    <small>{{ $return->initiated_at->format('H:i d/m') }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('driver.returns.show', $return->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($return->isAssigned())
                                        <form method="POST" action="{{ route('driver.returns.start', $return->id) }}" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Xác nhận bắt đầu hoàn hàng?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" title="Bắt đầu hoàn">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($return->isReturning())
                                        <a href="{{ route('driver.returns.complete-form', $return->id) }}" 
                                           class="btn btn-sm btn-success" 
                                           title="Hoàn trả">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        @if($returns->hasPages())
        <div class="card-footer bg-white">
            {{ $returns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection