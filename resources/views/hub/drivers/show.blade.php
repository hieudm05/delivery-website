@extends('hub.layouts.app')

@section('title', 'Chi tiết Driver - ' . $driver->full_name)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('hub.drivers.index') }}">Quản lý Driver</a></li>
                    <li class="breadcrumb-item active">{{ $driver->full_name }}</li>
                </ol>
            </nav>
            <h2 class="mb-0">Chi tiết Driver</h2>
        </div>
        <div class="btn-group">
            <a href="{{ route('hub.drivers.delivery-history', $driver->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-clock-history"></i> Lịch sử giao hàng
            </a>
            <a href="{{ route('hub.drivers.location', $driver->id) }}" class="btn btn-outline-info">
                <i class="bi bi-geo-alt"></i> Xem vị trí
            </a>
            <button type="button" 
                    class="btn btn-outline-{{ $driver->status === 'active' ? 'danger' : 'success' }}"
                    onclick="toggleDriverStatus({{ $driver->id }}, '{{ $driver->status }}')">
                <i class="bi bi-{{ $driver->status === 'active' ? 'lock' : 'unlock' }}"></i>
                {{ $driver->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa' }}
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Thông tin cá nhân -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $driver->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($driver->full_name) }}" 
                         alt="{{ $driver->full_name }}"
                         class="rounded-circle mb-3"
                         width="120" height="120">
                    
                    <h4 class="mb-1">{{ $driver->full_name }}</h4>
                    <p class="text-muted mb-3">ID: {{ $driver->id }}</p>

                    @if($driver->isOnline())
                        <span class="badge bg-success mb-3">
                            <i class="bi bi-circle-fill"></i> Đang online
                        </span>
                    @else
                        <span class="badge bg-secondary mb-3">
                            Offline {{ $driver->last_seen_at ? $driver->last_seen_at->diffForHumans() : '' }}
                        </span>
                    @endif

                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Trạng thái:</span>
                            <span class="badge bg-{{ $driver->status === 'active' ? 'success' : 'danger' }}">
                                {{ $driver->status === 'active' ? 'Hoạt động' : 'Đã khóa' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Loại xe:</span>
                            <strong>
                                @php
                                    $vehicleTypes = [
                                        'Xe máy' => 'Xe máy',
                                        'car' => 'Ô tô',
                                        'truck' => 'Xe tải'
                                    ];
                                @endphp
                                {{ $vehicleTypes[$driver->driverProfile->vehicle_type] ?? 'N/A' }}
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Biển số:</span>
                            <strong>{{ $driver->driverProfile->license_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Kinh nghiệm:</span>
                            <strong>{{ $driver->driverProfile->experience }} năm</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin liên hệ -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Thông tin liên hệ</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Số điện thoại</small>
                        <strong>{{ $driver->phone }}</strong>
                    </div>
                    @if($driver->email)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Email</small>
                        <strong>{{ $driver->email }}</strong>
                    </div>
                    @endif
                    @if($driver->userInfo && $driver->userInfo->full_address)
                    <div>
                        <small class="text-muted d-block mb-1">Địa chỉ</small>
                        <strong>{{ $driver->userInfo->full_address }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Vị trí hiện tại -->
            @if($driver->userInfo && $driver->userInfo->latitude)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Vị trí hiện tại</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Cập nhật:</small>
                        <strong>{{ $driver->last_seen_at ? $driver->last_seen_at->format('H:i d/m/Y') : 'Chưa có' }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Tọa độ:</small>
                        <strong>{{ $driver->userInfo->latitude }}, {{ $driver->userInfo->longitude }}</strong>
                    </div>
                    <a href="{{ route('hub.drivers.location', $driver->id) }}" class="btn btn-sm btn-outline-info w-100">
                        <i class="bi bi-map"></i> Xem trên bản đồ
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Thống kê & Lịch sử -->
        <div class="col-lg-8">
            <!-- Thống kê nhanh -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0  bg-opacity-10">
                        <div class="card-body">
                            <div class=" mb-1">
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                            <h3 class="mb-0">{{ $deliveryStats['today']['total'] }}</h3>
                            <small class="text-muted">Đơn hôm nay</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-opacity-10">
                        <div class="card-body">
                            <div class=" mb-1">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                            <h3 class="mb-0">{{ $deliveryStats['today']['delivered'] }}</h3>
                            <small class="text-muted">Đã giao hôm nay</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0  bg-opacity-10">
                        <div class="card-body">
                            <div class=" mb-1">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                            <h3 class="mb-0">{{ $deliveryStats['today']['failed'] }}</h3>
                            <small class="text-muted">Thất bại hôm nay</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-opacity-10">
                        <div class="card-body">
                            <div class=" mb-1">
                                <i class="bi bi-percent fs-4"></i>
                            </div>
                            <h3 class="mb-0">{{ $deliveryStats['all_time']['success_rate'] }}%</h3>
                            <small class="text-muted">Tỷ lệ thành công</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê chi tiết -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Thống kê giao hàng</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Khoảng thời gian</th>
                                    <th class="text-center">Tổng đơn</th>
                                    <th class="text-center">Đã giao</th>
                                    <th class="text-center">Tỷ lệ</th>
                                    <th class="text-end">COD thu được</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Hôm nay</strong></td>
                                    <td class="text-center">{{ $deliveryStats['today']['total'] }}</td>
                                    <td class="text-center">{{ $deliveryStats['today']['delivered'] }}</td>
                                    <td class="text-center">
                                        @php
                                            $rate = $deliveryStats['today']['total'] > 0 
                                                ? round(($deliveryStats['today']['delivered'] / $deliveryStats['today']['total']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <span class="badge bg-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">
                                            {{ $rate }}%
                                        </span>
                                    </td>
                                    <td class="text-end">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Tuần này</strong></td>
                                    <td class="text-center">{{ $deliveryStats['week']['total'] }}</td>
                                    <td class="text-center">{{ $deliveryStats['week']['delivered'] }}</td>
                                    <td class="text-center">
                                        @php
                                            $rate = $deliveryStats['week']['total'] > 0 
                                                ? round(($deliveryStats['week']['delivered'] / $deliveryStats['week']['total']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <span class="badge bg-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">
                                            {{ $rate }}%
                                        </span>
                                    </td>
                                    <td class="text-end">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Tháng này</strong></td>
                                    <td class="text-center">{{ $deliveryStats['month']['total'] }}</td>
                                    <td class="text-center">{{ $deliveryStats['month']['delivered'] }}</td>
                                    <td class="text-center">
                                        @php
                                            $rate = $deliveryStats['month']['total'] > 0 
                                                ? round(($deliveryStats['month']['delivered'] / $deliveryStats['month']['total']) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <span class="badge bg-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">
                                            {{ $rate }}%
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($deliveryStats['month']['cod_collected']) }} ₫</td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Tổng cộng</strong></td>
                                    <td class="text-center"><strong>{{ $deliveryStats['all_time']['total'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $deliveryStats['all_time']['delivered'] }}</strong></td>
                                    <td class="text-center">
                                        @php $rate = $deliveryStats['all_time']['success_rate']; @endphp
                                        <span class="badge bg-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">
                                            {{ $rate }}%
                                        </span>
                                    </td>
                                    <td class="text-end">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tài khoản ngân hàng -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Tài khoản ngân hàng</h6>
                </div>
                <div class="card-body">
                    @if($bankAccounts->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bank fs-1 d-block mb-2"></i>
                            Chưa có tài khoản ngân hàng
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($bankAccounts as $account)
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-start">
                                    @if($account->bank_logo)
                                    <img src="{{ $account->bank_logo }}" alt="{{ $account->bank_name }}" 
                                         class="me-3" width="48" height="48">
                                    @endif
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <strong>{{ $account->bank_name }}</strong>
                                                @if($account->is_primary)
                                                    <span class="badge bg-primary ms-2">Chính</span>
                                                @endif
                                                @if($account->isVerified())
                                                    <span class="badge bg-success ms-1">
                                                        <i class="bi bi-check-circle"></i> Đã xác thực
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-muted small mb-1">
                                            {{ $account->account_number }} - {{ $account->account_name }}
                                        </div>
                                        @if($account->note)
                                        <div class="text-muted small">
                                            <i class="bi bi-info-circle"></i> {{ $account->note }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lịch sử giao hàng gần đây -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Lịch sử giao hàng gần đây</h6>
                    <a href="{{ route('hub.drivers.delivery-history', $driver->id) }}" class="btn btn-sm btn-outline-primary">
                        Xem tất cả
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentDeliveries->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            Chưa có lịch sử giao hàng
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Thời gian</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end">COD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentDeliveries as $delivery)
                                    <tr>
                                        <td>
                                            <a href="#" class="text-decoration-none">
                                                #{{ $delivery->order_id }}
                                            </a>
                                        </td>
                                        <td>
                                            <small>{{ $delivery->created_at->format('H:i d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            @if($delivery->actual_delivery_time)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Đã giao
                                                </span>
                                                <small class="text-muted d-block">
                                                    {{ $delivery->actual_delivery_time->format('H:i d/m/Y') }}
                                                </small>
                                            @elseif($delivery->actual_delivery_start_time)
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-truck"></i> Đang giao
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Chưa bắt đầu</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($delivery->cod_collected_amount)
                                                <strong class="text-success">
                                                    {{ number_format($delivery->cod_collected_amount) }} ₫
                                                </strong>
                                            @else
                                                <span class="text-muted">-</span>
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
    </div>
</div>

<!-- Modal khóa/mở khóa -->
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
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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