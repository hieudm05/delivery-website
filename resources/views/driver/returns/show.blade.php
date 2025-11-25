@extends('driver.layouts.app')

@section('title', 'Chi tiết đơn hoàn #' . $return->order->id)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-undo text-warning"></i> Đơn hoàn #{{ $return->order->id }}
            </h4>
            <p class="text-muted small mb-0">Chi tiết đơn hàng cần hoàn về sender</p>
        </div>
        <a href="{{ route('driver.returns.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Trạng thái & Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">Trạng thái: 
                                <span class="badge bg-{{ $return->status_badge }} fs-6">
                                    {{ $return->status_label }}
                                </span>
                            </h5>
                            <small class="text-muted">Khởi tạo: {{ $return->initiated_at->format('H:i d/m/Y') }}</small>
                        </div>
                        
                        @if($return->isAssigned())
                            <form method="POST" action="{{ route('driver.returns.start', $return->id) }}" 
                                  onsubmit="return confirm('Xác nhận bắt đầu hoàn hàng về sender?')">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Bắt đầu hoàn hàng
                                </button>
                            </form>
                        @endif

                        @if($return->isReturning())
                            <a href="{{ route('driver.returns.complete-form', $return->id) }}" 
                               class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Xác nhận hoàn trả
                            </a>
                        @endif
                    </div>

                    <!-- Progress Steps -->
                    <div class="progress-steps">
                        <div class="step {{ in_array($return->status, ['pending', 'assigned', 'returning', 'completed']) ? 'active' : '' }}">
                            <div class="step-icon"><i class="fas fa-flag"></i></div>
                            <div class="step-label">Khởi tạo</div>
                        </div>
                        <div class="step {{ in_array($return->status, ['assigned', 'returning', 'completed']) ? 'active' : '' }}">
                            <div class="step-icon"><i class="fas fa-user-check"></i></div>
                            <div class="step-label">Đã phân</div>
                        </div>
                        <div class="step {{ in_array($return->status, ['returning', 'completed']) ? 'active' : '' }}">
                            <div class="step-icon"><i class="fas fa-truck"></i></div>
                            <div class="step-label">Đang hoàn</div>
                        </div>
                        <div class="step {{ $return->status === 'completed' ? 'active' : '' }}">
                            <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="step-label">Hoàn thành</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin Sender -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-user-circle"></i> Thông tin Sender (Người nhận hoàn)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted mb-1">Tên người gửi</label>
                            <div class="fw-bold">{{ $return->sender_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted mb-1">Số điện thoại</label>
                            <div>
                                <a href="tel:{{ $return->sender_phone }}" class="text-primary fw-bold">
                                    <i class="fas fa-phone"></i> {{ $return->sender_phone }}
                                </a>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted mb-1">Địa chỉ hoàn hàng</label>
                            <div class="alert alert-light border mb-0">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                {{ $return->sender_address }}
                                @if($return->sender_map_link)
                                    <a href="{{ $return->sender_map_link }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">
                                        <i class="fas fa-map"></i> Mở Google Maps
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lý do hoàn hàng -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Lý do hoàn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning border-warning mb-3">
                        <strong>Loại:</strong> 
                        <span class="badge bg-warning text-dark">{{ $return->reason_type_label }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Chi tiết:</strong>
                        <p class="mb-0 mt-2">{{ $return->reason_detail }}</p>
                    </div>
                    @if($return->failed_attempts > 0)
                        <div class="alert alert-danger border-danger mb-0">
                            <i class="fas fa-times-circle"></i>
                            <strong>Số lần giao hàng thất bại trước đó: {{ $return->failed_attempts }} lần</strong>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lịch sử giao hàng thất bại -->
            @if($return->order->deliveryIssues->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Lịch sử thất bại ({{ $return->order->deliveryIssues->count() }} lần)</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($return->order->deliveryIssues as $issue)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $issue->issue_type_label }}</strong>
                                    <small class="text-muted">{{ $issue->issue_time->format('H:i d/m/Y') }}</small>
                                </div>
                                <p class="mb-1 small">{{ $issue->issue_note }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> {{ $issue->reporter->name }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Hàng hóa -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-box"></i> Thông tin hàng hóa</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>SL</th>
                                    <th>Trọng lượng</th>
                                    <th class="text-end">Giá trị</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($return->order->products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    <td>{{ $product->weight }}g</td>
                                    <td class="text-end">{{ number_format($product->value) }}đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-stream"></i> Lịch sử hoàn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($return->timeline as $event)
                        <div class="timeline-item">
                            <div class="timeline-marker" style="background-color: {{ $event->event_color }}">
                                <i class="fas {{ $event->event_icon }} text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $event->event_label }}</strong>
                                        <p class="mb-1 small">{{ $event->description }}</p>
                                        @if($event->creator)
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> {{ $event->creator->name }}
                                            </small>
                                        @endif
                                    </div>
                                    <small class="text-muted text-nowrap">
                                        {{ $event->event_time->format('H:i d/m') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">Chưa có lịch sử</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Chi phí -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-dollar-sign"></i> Chi phí hoàn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí hoàn hàng:</span>
                        <strong class="text-danger">{{ number_format($return->return_fee) }}đ</strong>
                    </div>
                    @if($return->cod_amount > 0)
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tiền COD cần trả:</span>
                            <strong class="text-warning">{{ number_format($return->cod_amount) }}đ</strong>
                        </div>
                        @if($return->cod_returned)
                            <div class="alert alert-success border-success mb-0 mt-2">
                                <i class="fas fa-check-circle"></i> Đã trả COD lúc {{ $return->cod_returned_at->format('H:i d/m') }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Ảnh chứng từ -->
            @if($return->images->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-images"></i> Ảnh chứng từ ({{ $return->images->count() }})</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($return->images as $image)
                        <div class="col-6">
                            <div class="position-relative">
                                <img src="{{ $image->image_url }}" 
                                     class="img-fluid rounded shadow-sm" 
                                     style="cursor: pointer;"
                                     onclick="showImageModal('{{ $image->image_url }}', '{{ $image->type_label }}')"
                                     alt="{{ $image->type_label }}">
                                <span class="badge bg-dark position-absolute bottom-0 start-0 m-2 small">
                                    {{ $image->type_label }}
                                </span>
                            </div>
                            @if($image->note)
                                <small class="text-muted d-block mt-1">{{ $image->note }}</small>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Thông tin hoàn trả (nếu đã hoàn) -->
            @if($return->isCompleted())
            <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
                <div class="card-header border-0 py-3">
                    <h6 class="mb-0 text-white"><i class="fas fa-check-circle"></i> Thông tin hoàn trả</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-white-50">Người nhận hoàn:</small>
                        <div class="fw-bold">{{ $return->received_by_name }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-white-50">SĐT:</small>
                        <div>{{ $return->received_by_phone }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-white-50">Mối quan hệ:</small>
                        <div>{{ ucfirst($return->received_by_relation) }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-white-50">Tình trạng hàng:</small>
                        <div>{{ $return->package_condition_label }}</div>
                    </div>
                    @if($return->return_note)
                        <div class="mb-2">
                            <small class="text-white-50">Ghi chú:</small>
                            <div class="small">{{ $return->return_note }}</div>
                        </div>
                    @endif
                    <div class="mt-3 pt-3 border-top border-white-50">
                        <small class="text-white-50">Hoàn thành lúc:</small>
                        <div class="fw-bold">{{ $return->completed_at->format('H:i d/m/Y') }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imageModalImg" src="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<style>
.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-top: 2rem;
}
.progress-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}
.step {
    text-align: center;
    position: relative;
    flex: 1;
}
.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    position: relative;
    z-index: 1;
}
.step.active .step-icon {
    background: #0d6efd;
    color: white;
}
.step-label {
    font-size: 12px;
    color: #6c757d;
}
.step.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 30px;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #e9ecef;
}
.timeline-content {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
}
</style>

<script>
function showImageModal(url, title) {
    document.getElementById('imageModalImg').src = url;
    document.getElementById('imageModalTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endsection