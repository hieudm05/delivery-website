{{-- resources/views/hub/orders/batch-assign.blade.php --}}
@extends('hub.layouts.app')
@section('title', 'Gom đơn và phát hàng loạt')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-layers"></i> Gom đơn và phát hàng loạt</h4>
                <a href="{{ route('hub.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Danh sách đơn hàng -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam"></i> 
                            Danh sách đơn chưa phát 
                            <span class="badge bg-primary">{{ $orders->count() }}</span>
                        </h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                <i class="bi bi-check-all"></i> Chọn tất cả
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                <i class="bi bi-x"></i> Bỏ chọn
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($orders->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">Không có đơn hàng chưa phát</p>
                        </div>
                    @else
                        <!-- Suggested Groups -->
                        @if(count($suggestedGroups) > 0)
                        <div class="alert alert-light m-3">
                            <h6><i class="bi bi-lightbulb"></i> Gợi ý gom đơn theo khu vực:</h6>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($suggestedGroups as $index => $group)
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="selectGroup({{ json_encode(collect($group['orders'])->pluck('id')) }})">
                                    <i class="bi bi-geo-alt"></i> 
                                    Nhóm {{ $index + 1 }} ({{ count($group['orders']) }} đơn)
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                       <th width="50" class="text-center">
                                            <div class="form-check">
                                                <input class="" type="checkbox" id="selectAllCheckbox" onclick="selectAll()">
                                            </div>
                                        </th>

                                        <th>Mã đơn</th>
                                        <th>Người nhận</th>
                                        <th>Địa chỉ</th>
                                        <th>COD</th>
                                        <th>Khối lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr class="order-row" data-order-id="{{ $order->id }}">
                                      <td class="text-center">
                                        <div class="form-check">
                                            <input class=" order-checkbox"
                                                type="checkbox"
                                                value="{{ $order->id }}"
                                                data-lat="{{ $order->recipient_latitude }}"
                                                data-lng="{{ $order->recipient_longitude }}"
                                                onchange="updateSelection()">
                                        </div>
                                    </td>

                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>
                                            {{ $order->recipient_name }}<br>
                                            <small class="text-muted">{{ $order->recipient_phone }}</small>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($order->recipient_full_address, 40) }}</small>
                                        </td>
                                        <td>
                                            @if($order->cod_amount > 0)
                                                <span class="badge bg-warning">
                                                    {{ number_format($order->cod_amount) }}đ
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $order->products->sum('weight') }}g</small>
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

        <!-- Panel chọn tài xế -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-check"></i> Phát đơn đã chọn
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Thống kê đơn đã chọn -->
                    <div id="selectionStats" class="mb-3">
                        <div class="alert alert-light">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Số đơn:</span>
                                <strong id="selectedCount">0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tổng COD:</span>
                                <strong id="totalCOD">0đ</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tổng KL:</span>
                                <strong id="totalWeight">0g</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Form phát đơn -->
                    <form id="batchAssignForm">
                        <div class="mb-3">
                            <label class="form-label">Chọn tài xế <span class="text-danger">*</span></label>
                            <select id="driverSelect" class="form-select" required disabled>
                                <option value="">Vui lòng chọn đơn hàng trước</option>
                            </select>
                            <small class="text-muted">Tài xế sẽ được gợi ý dựa trên vị trí các đơn đã chọn</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea id="noteInput" class="form-control" rows="3" 
                                      placeholder="Nhập ghi chú cho tài xế (nếu có)..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" id="assignBtn" disabled>
                                <i class="bi bi-send"></i> Phát đơn cho tài xế
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                <i class="bi bi-x-circle"></i> Xóa lựa chọn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedOrders = [];

function updateSelection() {
    selectedOrders = [];
    let totalCOD = 0;
    let totalWeight = 0;

    document.querySelectorAll('.order-checkbox:checked').forEach(checkbox => {
        const orderId = parseInt(checkbox.value);
        const row = checkbox.closest('tr');
        
        selectedOrders.push(orderId);
        
        // Tính COD
        const codBadge = row.querySelector('.badge.bg-warning');
        if (codBadge) {
            const codText = codBadge.textContent.replace(/[^\d]/g, '');
            totalCOD += parseInt(codText) || 0;
        }
        
        // Tính khối lượng
        const weightCell = row.querySelector('td:last-child small');
        if (weightCell) {
            const weightText = weightCell.textContent.replace(/[^\d]/g, '');
            totalWeight += parseInt(weightText) || 0;
        }
    });

    // Cập nhật UI
    document.getElementById('selectedCount').textContent = selectedOrders.length;
    document.getElementById('totalCOD').textContent = new Intl.NumberFormat('vi-VN').format(totalCOD) + 'đ';
    document.getElementById('totalWeight').textContent = new Intl.NumberFormat('vi-VN').format(totalWeight) + 'g';

    const driverSelect = document.getElementById('driverSelect');
    const assignBtn = document.getElementById('assignBtn');

    if (selectedOrders.length > 0) {
        driverSelect.disabled = false;
        loadSuggestedDrivers();
    } else {
        driverSelect.disabled = true;
        driverSelect.innerHTML = '<option value="">Vui lòng chọn đơn hàng trước</option>';
        assignBtn.disabled = true;
    }
}

function loadSuggestedDrivers() {
    const driverSelect = document.getElementById('driverSelect');
    driverSelect.innerHTML = '<option value="">Đang tải...</option>';

    fetch('{{ route("hub.orders.batch.available-drivers") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            order_ids: selectedOrders
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.drivers.length > 0) {
            let html = '<option value="">-- Chọn tài xế --</option>';
            
            data.drivers.forEach(driver => {
                const onlineIcon = driver.is_online ? '🟢' : '⚪';
                
                html += `<option value="${driver.id}">
                    ${onlineIcon} ${driver.name} - ${driver.phone} (${driver.distance_to_centroid}km)
                </option>`;
            });
            
            driverSelect.innerHTML = html;
            document.getElementById('assignBtn').disabled = false;
        } else {
            driverSelect.innerHTML = '<option value="">Không có tài xế rảnh</option>';
            document.getElementById('assignBtn').disabled = true;
            
            alert('⚠️ Không có tài xế khả dụng!\n\nTất cả tài xế đang bận lấy hàng hoặc giao hàng.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        driverSelect.innerHTML = '<option value="">Lỗi khi tải danh sách tài xế</option>';
    });
}

function selectAll() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
    updateSelection();
}

function clearSelection() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelection();
}

function selectGroup(orderIds) {
    clearSelection();
    orderIds.forEach(id => {
        const checkbox = document.querySelector(`.order-checkbox[value="${id}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
    updateSelection();
}

// Submit form
document.getElementById('batchAssignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const driverId = document.getElementById('driverSelect').value;
    const note = document.getElementById('noteInput').value;
    
    if (!driverId) {
        alert('Vui lòng chọn tài xế');
        return;
    }
    
    if (selectedOrders.length === 0) {
        alert('Vui lòng chọn ít nhất 1 đơn hàng');
        return;
    }
    
    if (!confirm(`Xác nhận phát ${selectedOrders.length} đơn hàng cho tài xế đã chọn?`)) {
        return;
    }
    
    const assignBtn = document.getElementById('assignBtn');
    const originalText = assignBtn.innerHTML;
    assignBtn.disabled = true;
    assignBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    
    fetch('{{ route("hub.orders.batch.assign") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            order_ids: selectedOrders,
            driver_id: driverId,
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("hub.orders.index") }}';
        } else {
            alert(data.error || 'Có lỗi xảy ra');
            assignBtn.disabled = false;
            assignBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi phát đơn');
        assignBtn.disabled = false;
        assignBtn.innerHTML = originalText;
    });
});

// Enable/disable assign button when driver is selected
document.getElementById('driverSelect').addEventListener('change', function() {
    document.getElementById('assignBtn').disabled = !this.value;
});
</script>
@endpush