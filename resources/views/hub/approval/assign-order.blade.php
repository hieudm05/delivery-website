@extends('hub.layouts.app')
@section('title', 'Phát đơn cho tài xế')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Thông tin đơn hàng -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam"></i> Thông tin đơn hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted">Mã đơn hàng</label>
                        <h4>#{{ $order->id }}</h4>
                    </div>

                    <hr>

                    <h6 class="mb-3"><i class="bi bi-person-circle"></i> Người gửi</h6>
                    <p class="mb-1"><strong>{{ $order->sender_name }}</strong></p>
                    <p class="mb-1"><i class="bi bi-telephone"></i> {{ $order->sender_phone }}</p>
                    <p class="mb-3"><i class="bi bi-geo-alt"></i> {{ $order->sender_address }}</p>

                    <hr>

                    <h6 class="mb-3"><i class="bi bi-geo-alt"></i> Người nhận</h6>
                    <p class="mb-1"><strong>{{ $order->recipient_name }}</strong></p>
                    <p class="mb-1"><i class="bi bi-telephone"></i> {{ $order->recipient_phone }}</p>
                    <p class="mb-3"><i class="bi bi-geo-alt"></i> {{ $order->recipient_full_address }}</p>

                    <hr>
                    <h6>Thông tin hàng hoá</h6>
                   <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">SL</th>
                                <th class="text-center">Khối lượng (g)</th>
                                <th class="text-right">Giá trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td class="text-center">{{ $product->quantity }}</td>
                                <td class="text-center">{{ $product->weight }}</td>
                                <td class="text-right">{{ $product->value }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <hr>

                    <!-- Trạng thái -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="text-muted d-block mb-1">Trạng thái</label>
                            <span class="badge bg-{{ $order->status_badge }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </div>

                    <!-- Phí ship & COD -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted d-block mb-1">Phí ship</label>
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                <strong>{{ number_format($order->shipping_fee) }}đ</strong>
                                <small class="text-muted">
                                    ({{ $order->payer === 'sender' ? 'người gửi trả' : 'người nhận trả' }})
                                </small>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="text-muted d-block mb-1">COD</label>
                            @if($order->cod_amount > 0)
                                <strong class="text-warning">{{ number_format($order->cod_amount) }}đ</strong>
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </div>
                    </div>

                    <!-- Tổng trả -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted d-block mb-1">Người nhận trả</label>
                            <strong>{{ number_format($order->recipient_total) }}đ</strong>
                        </div>

                        <div class="col-6">
                            <label class="text-muted d-block mb-1">Người gửi trả</label>
                            <strong>{{ number_format($order->sender_total) }}đ</strong>
                        </div>
                    </div>


                    @if($order->note)
                    <hr>
                    <label class="text-muted">Ghi chú</label>
                    <p class="mb-0">{{ $order->note }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Danh sách tài xế -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> Chọn tài xế giao hàng
                    </h5>
                </div>
                <div class="card-body">
                    @if($availableDrivers->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #ffc107;"></i>
                        <h5 class="mt-3">Không có tài xế khả dụng</h5>
                        <p class="text-muted">
                            Hiện tại không có tài xế nào đang online hoặc ở gần vị trí giao hàng.
                        </p>
                        <a href="{{ route('hub.index') }}" class="btn btn-outline-primary mt-3">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                    @else
                    <form id="assignForm" action="{{ route('hub.orders.assign', $order->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Ghi chú cho tài xế</label>
                            <textarea name="note" class="form-control" rows="2" 
                                      placeholder="Nhập ghi chú nếu cần..."></textarea>
                        </div>

                        <div class=" mb-3" style="max-height: 500px; overflow-y: auto;">
                            @foreach($availableDrivers as $driver)
                            <label class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-start">
                                    <input type="radio" name="driver_id" value="{{ $driver['id'] }}" 
                                           class="form-check-input me-3 mt-2" required>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $driver['name'] }}</h6>
                                                <p class=" text-muted small">
                                                    <i class="bi bi-telephone"></i> {{ $driver['phone'] }}
                                                </p>
                                            </div>
                                           @if($driver['is_online'])
                                                <span class="badge bg-success">
                                                    <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Online
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-circle" style="font-size: 0.5rem;"></i> Offline
                                                </span>
                                            @endif

                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="bi bi-building"></i> Cách hub: 
                                                <strong>{{ $driver['distance_to_hub'] }} km</strong>
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt"></i> Cách điểm giao: 
                                                <strong class="text-primary">{{ $driver['distance_to_order'] }} km</strong>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> {{ $driver['last_seen'] }}
                                        </small>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="bi bi-send"></i> Phát đơn cho tài xế đã chọn
                            </button>
                            <a href="{{ route('hub.income.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('assignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Disable button và hiển thị loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            alert(data.message);
            
            // Redirect về trang danh sách
            window.location.href = "{{ route('hub.approval') }}";
        } else {
            alert(data.error || 'Có lỗi xảy ra');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi phát đơn');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Highlight selected driver
document.querySelectorAll('input[name="driver_id"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.list-group-item').forEach(item => {
            item.classList.remove('active');
        });
        if (this.checked) {
            this.closest('.list-group-item').classList.add('active');
        }
    });
});
</script>
@endpush