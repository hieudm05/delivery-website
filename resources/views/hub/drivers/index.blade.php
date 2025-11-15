@extends('hub.layouts.app')

@section('title', 'Quản lý Driver')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Quản lý Driver</h2>
            <p class="text-muted">Quản lý tài xế thuộc bưu cục của bạn</p>
        </div>
        <a href="{{ route('hub.drivers.report') }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-bar-graph"></i> Báo cáo tổng hợp
        </a>
    </div>

    <!-- Thống kê nhanh -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bage-primary bage-opacity-10 p-3 rounded">
                                <i class="bi bi-people-fill text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Tổng Driver</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bage-success bage-opacity-10 p-3 rounded">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Đang hoạt động</h6>
                            <h3 class="mb-0">{{ $stats['active'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bage-info bage-opacity-10 p-3 rounded">
                                <i class="bi bi-wifi text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Đang online</h6>
                            <h3 class="mb-0">{{ $stats['online'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bage-danger bg-opacity-10 p-3 rounded">
                                <i class="bi bi-lock-fill text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Đã khóa</h6>
                            <h3 class="mb-0">{{ $stats['blocked'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('hub.drivers.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tên, SĐT, Email..." value="{{ $search }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="blocked" {{ $status === 'blocked' ? 'selected' : '' }}>Đã khóa</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Loại xe</label>
                    <select name="vehicle_type" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="Xe máy" {{ $vehicleType === 'bike' ? 'selected' : '' }}>Xe máy</option>
                        <option value="car" {{ $vehicleType === 'car' ? 'selected' : '' }}>Ô tô</option>
                        <option value="truck" {{ $vehicleType === 'truck' ? 'selected' : '' }}>Xe tải</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách Driver -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Driver</th>
                            <th>Liên hệ</th>
                            <th>Loại xe</th>
                            <th>Trạng thái</th>
                            <th>Online</th>
                            <th>Đơn hôm nay</th>
                            <th class="text-end px-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $driver->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($driver->full_name) }}" 
                                         alt="{{ $driver->full_name }}"
                                         class="rounded-circle me-3"
                                         width="40" height="40">
                                    <div>
                                        <div class="fw-semibold">{{ $driver->full_name }}</div>
                                        <small class="text-muted">ID: {{ $driver->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <i class="bi bi-telephone"></i> {{ $driver->phone }}
                                </div>
                                @if($driver->email)
                                <div class="text-muted small">
                                    <i class="bi bi-envelope"></i> {{ $driver->email }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $vehicleTypes = [
                                        'Xe máy' => ['Xe máy', 'bi-bicycle', 'info'],
                                        'car' => ['Ô tô', 'bi-car-front', 'primary'],
                                        'truck' => ['Xe tải', 'bi-truck', 'success']
                                    ];
                                    $vehicle = $vehicleTypes[$driver->driverProfile->vehicle_type] ?? ['N/A', 'bi-question', 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $vehicle[2] }} bg-opacity-10  }}">
                                     {{ $vehicle[0] }}
                                </span>
                            </td>
                            <td>
                                @if($driver->status === 'active')
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Đã khóa</span>
                                @endif
                            </td>
                            <td>
                                @if($driver->isOnline())
                                    <span class="badge bg-success">
                                        <i class="bi bi-circle-fill"></i> Online
                                    </span>
                                @else
                                    <span class="text-muted small">
                                        @if($driver->last_seen_at)
                                            {{ $driver->last_seen_at->diffForHumans() }}
                                        @else
                                            Chưa hoạt động
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    // Lấy đơn hàng thông qua bảng order_deliveries
                                    $todayOrders = \App\Models\Driver\Orders\OrderDelivery::where('delivery_driver_id', $driver->id)
                                        ->whereDate('created_at', today())
                                        ->count();
                                    
                                    $todayDelivered = \App\Models\Driver\Orders\OrderDelivery::where('delivery_driver_id', $driver->id)
                                        ->whereNotNull('actual_delivery_time')
                                        ->whereDate('actual_delivery_time', today())
                                        ->count();
                                @endphp
                                <span class="fw-semibold">{{ $todayDelivered }}/{{ $todayOrders }}</span>
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group">
                                    <a href="{{ route('hub.drivers.show', $driver->id) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('hub.drivers.location', $driver->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       title="Xem vị trí">
                                        <i class="bi bi-geo-alt"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-{{ $driver->status === 'active' ? 'danger' : 'success' }}"
                                            onclick="toggleDriverStatus({{ $driver->id }}, '{{ $driver->status }}')"
                                            title="{{ $driver->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                        <i class="bi bi-{{ $driver->status === 'active' ? 'lock' : 'unlock' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Không tìm thấy driver nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($drivers->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $drivers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal khóa/mở khóa tài khoản -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                @csrf
                <input type="hidden" id="driverId" name="driver_id">
                <input type="hidden" id="newStatus" name="status">
                
                <div class="modal-body">
                    <div class="alert alert-warning" id="blockWarning" style="display:none;">
                        <i class="bi bi-exclamation-triangle"></i>
                        Driver sẽ không thể đăng nhập và nhận đơn hàng mới khi bị khóa.
                    </div>
                    
                    <div class="mb-3" id="reasonGroup">
                        <label class="form-label">Lý do <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="Nhập lý do khóa tài khoản..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn" id="statusSubmitBtn"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let statusModal;

document.addEventListener('DOMContentLoaded', function() {
    statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
});

function toggleDriverStatus(driverId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'blocked' : 'active';
    const isBlocking = newStatus === 'blocked';
    
    document.getElementById('driverId').value = driverId;
    document.getElementById('newStatus').value = newStatus;
    document.getElementById('statusModalTitle').textContent = 
        isBlocking ? 'Khóa tài khoản Driver' : 'Mở khóa tài khoản Driver';
    
    document.getElementById('blockWarning').style.display = isBlocking ? 'block' : 'none';
    document.getElementById('reasonGroup').style.display = isBlocking ? 'block' : 'none';
    
    const submitBtn = document.getElementById('statusSubmitBtn');
    submitBtn.className = `btn btn-${isBlocking ? 'danger' : 'success'}`;
    submitBtn.textContent = isBlocking ? 'Khóa tài khoản' : 'Mở khóa';
    
    statusModal.show();
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const driverId = document.getElementById('driverId').value;
    const newStatus = document.getElementById('newStatus').value;
    const reason = this.querySelector('[name="reason"]').value;
    
    if (newStatus === 'blocked' && !reason.trim()) {
        alert('Vui lòng nhập lý do khóa tài khoản');
        return;
    }
    
    const submitBtn = document.getElementById('statusSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    
    fetch(`/hub/drivers/${driverId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
        },
        body: JSON.stringify({
            status: newStatus,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusModal.hide();
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra');
            submitBtn.disabled = false;
            submitBtn.textContent = newStatus === 'blocked' ? 'Khóa tài khoản' : 'Mở khóa';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại');
        submitBtn.disabled = false;
        submitBtn.textContent = newStatus === 'blocked' ? 'Khóa tài khoản' : 'Mở khóa';
    });
});
</script>
@endpush