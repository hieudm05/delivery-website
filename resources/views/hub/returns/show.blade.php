@extends('hub.layouts.app')

@section('title', 'Chi tiết đơn hoàn #' . $return->id)

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Chi tiết đơn hoàn #{{ $return->id }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('hub.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hub.returns.index') }}">Quản lý hoàn hàng</a></li>
                    <li class="breadcrumb-item active">Chi tiết đơn hoàn</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('hub.returns.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Cột trái: Thông tin chính --}}
        <div class="col-lg-8">
            {{-- Trạng thái & Actions --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-2">Trạng thái hoàn hàng</h5>
                            @php
                                use App\Models\Driver\Orders\OrderReturn;
                                $statusConfig = [
                                    OrderReturn::STATUS_PENDING => ['label' => 'Chờ phân công', 'class' => 'warning'],
                                    OrderReturn::STATUS_ASSIGNED => ['label' => 'Đã phân công', 'class' => 'info'],
                                    OrderReturn::STATUS_RETURNING => ['label' => 'Đang hoàn', 'class' => 'primary'],
                                    OrderReturn::STATUS_COMPLETED => ['label' => 'Đã hoàn thành', 'class' => 'success'],
                                    OrderReturn::STATUS_CANCELLED => ['label' => 'Đã hủy', 'class' => 'danger'],
                                ];
                                $current = $statusConfig[$return->status] ?? ['label' => $return->status, 'class' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $current['class'] }} fs-6">
                                {{ $current['label'] }}
                            </span>
                        </div>
                        <div>
                            @if($return->status === OrderReturn::STATUS_PENDING)
                                <a href="{{ route('hub.returns.assign-form', $return->id) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Phân công tài xế
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="fas fa-times me-2"></i>Hủy hoàn
                                </button>
                            @elseif($return->status === OrderReturn::STATUS_ASSIGNED)
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="fas fa-times me-2"></i>Hủy hoàn
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="position-relative pt-2">
                        @php
                            $steps = [
                                OrderReturn::STATUS_PENDING => 'Chờ xử lý',
                                OrderReturn::STATUS_ASSIGNED => 'Đã phân công',
                                OrderReturn::STATUS_RETURNING => 'Đang hoàn',
                                OrderReturn::STATUS_COMPLETED => 'Hoàn thành',
                            ];
                            $stepKeys = array_keys($steps);
                            $currentIndex = array_search($return->status, $stepKeys);
                            if ($return->status === OrderReturn::STATUS_CANCELLED) {
                                $currentIndex = -1;
                            }
                        @endphp
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $currentIndex >= 0 ? (($currentIndex + 1) / count($steps) * 100) : 0 }}%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            @foreach($steps as $key => $label)
                                @php
                                    $stepIndex = array_search($key, $stepKeys);
                                    $isActive = $stepIndex <= $currentIndex;
                                    $isCurrent = $key === $return->status;
                                @endphp
                                <div class="text-center" style="flex: 1;">
                                    <div class="mb-2">
                                        <i class="fas fa-circle {{ $isActive ? 'text-primary' : 'text-muted' }}" 
                                           style="font-size: {{ $isCurrent ? '14px' : '10px' }};"></i>
                                    </div>
                                    <small class="{{ $isActive ? 'text-dark fw-bold' : 'text-muted' }}">
                                        {{ $label }}
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thông tin đơn hàng gốc --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>Thông tin đơn hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Mã đơn hàng</label>
                            <div class="fw-bold">
                                <a href="{{ route('hub.orders.show', $return->order_id) }}" class="text-primary">
                                    #{{ $return->order_id }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Trạng thái đơn hàng</label>
                            <div>
                                <span class="badge bg-{{ $return->order->status_badge }}">
                                    {{ $return->order->status_label }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Người gửi</label>
                            <div class="fw-bold">{{ $return->order->sender_name }}</div>
                            <small class="text-muted">{{ $return->order->sender_phone }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Người nhận</label>
                            <div class="fw-bold">{{ $return->order->recipient_name }}</div>
                            <small class="text-muted">{{ $return->order->recipient_phone }}</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Địa chỉ giao hàng</label>
                            <div>{{ $return->order->recipient_full_address }}</div>
                        </div>
                        @if($return->order->cod_amount > 0)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tiền COD</label>
                            <div class="fw-bold text-success">
                                {{ number_format($return->order->cod_amount) }}đ
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Sản phẩm --}}
                    @if($return->order->products && $return->order->products->count() > 0)
                    <div class="mt-3 pt-3 border-top">
                        <label class="text-muted small mb-2">Sản phẩm trong đơn</label>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tên sản phẩm</th>
                                        <th class="text-center">SL</th>
                                        <th class="text-end">Trọng lượng</th>
                                        <th class="text-end">Giá trị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($return->order->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">{{ $product->quantity }}</td>
                                        <td class="text-end">{{ $product->weight }}g</td>
                                        <td class="text-end">{{ number_format($product->value) }}đ</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Thông tin hoàn hàng --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-undo me-2"></i>Thông tin hoàn hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Loại hoàn hàng</label>
                            <div class="fw-bold">
                                @if($return->reason_type === OrderReturn::REASON_AUTO_FAILED)
                                    <i class="fas fa-robot text-warning me-2"></i>Tự động (thất bại nhiều lần)
                                @elseif($return->reason_type === OrderReturn::REASON_HUB_DECISION)
                                    <i class="fas fa-building text-info me-2"></i>Hub quyết định
                                @elseif($return->reason_type === OrderReturn::REASON_CUSTOMER_REQUEST)
                                    <i class="fas fa-user-times text-warning me-2"></i>Khách hàng yêu cầu
                                @elseif($return->reason_type === OrderReturn::REASON_WRONG_INFO)
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>Thông tin sai
                                @elseif($return->reason_type === OrderReturn::REASON_OTHER)
                                    <i class="fas fa-ellipsis-h text-secondary me-2"></i>Lý do khác
                                @else
                                    <i class="fas fa-question-circle text-muted me-2"></i>{{ $return->reason_type_label }}
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Người yêu cầu hoàn</label>
                            <div class="fw-bold">
                                @if($return->initiator)
                                    {{ $return->initiator->name }}
                                @else
                                    Hệ thống
                                @endif
                            </div>
                            <small class="text-muted">{{ $return->initiated_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Lý do chi tiết</label>
                            <div class="bg-light p-3 rounded">
                                {{ $return->reason_detail ?? 'Không có ghi chú' }}
                            </div>
                        </div>
                        @if($return->return_driver_id && $return->driver)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tài xế hoàn hàng</label>
                            <div class="fw-bold">
                                <i class="fas fa-user me-2"></i>{{ $return->driver->full_name }}
                            </div>
                            <small class="text-muted">{{ $return->driver->phone }}</small>
                        </div>
                        @endif
                        @if($return->assigned_at)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Thời gian phân công</label>
                            <div>{{ $return->assigned_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @endif
                        @if($return->return_fee)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Phí hoàn hàng</label>
                            <div class="fw-bold text-danger">
                                {{ number_format($return->return_fee) }}đ
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($return->status === OrderReturn::STATUS_COMPLETED)
                    <div class="mt-3 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Thời gian hoàn thành</label>
                                <div class="fw-bold text-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ $return->completed_at ? $return->completed_at->format('d/m/Y H:i') : 'N/A' }}
                                </div>
                            </div>
                            @if($return->package_condition)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Tình trạng hàng</label>
                                <div>
                                    @if($return->package_condition === 'good')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Nguyên vẹn
                                        </span>
                                    @elseif($return->package_condition === 'damaged')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Hư hỏng
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-box-open me-1"></i>Đã mở
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @if($return->cod_returned && $return->cod_amount)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">COD đã hoàn</label>
                                <div class="fw-bold text-success">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    {{ number_format($return->cod_amount) }}đ
                                </div>
                            </div>
                            @endif
                            @if($return->completion_note)
                            <div class="col-12">
                                <label class="text-muted small">Ghi chú hoàn thành</label>
                                <div class="bg-light p-3 rounded">
                                    {{ $return->completion_note }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($return->status === OrderReturn::STATUS_CANCELLED)
                    <div class="mt-3 pt-3 border-top">
                        <div class="alert alert-danger mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-ban me-2"></i>Đơn hoàn đã bị hủy
                            </h6>
                            @if($return->cancelled_at)
                                <p class="mb-2"><strong>Thời gian:</strong> {{ $return->cancelled_at->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($return->cancellation_reason)
                                <p class="mb-0"><strong>Lý do:</strong> {{ $return->cancellation_reason }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Hình ảnh --}}
            @if($return->images && $return->images->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-images me-2"></i>Hình ảnh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($return->images as $image)
                        <div class="col-md-3">
                            <a href="{{ $image->image_url }}" target="_blank">
                                <img src="{{ $image->image_url }}" class="img-fluid rounded" alt="{{ $image->image_type }}">
                            </a>
                            <small class="text-muted d-block mt-1 text-center">{{ $image->image_type }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Timeline --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Lịch sử thao tác
                    </h5>
                </div>
                <div class="card-body">
                    @if($return->timeline && $return->timeline->count() > 0)
                    <div class="timeline">
                        @foreach($return->timeline as $event)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker me-3">
                                    <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $event->event_type }}</h6>
                                            <p class="mb-1 text-muted">{{ $event->description }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                @if($event->creator)
                                                    {{ $event->creator->name }}
                                                @else
                                                    Hệ thống
                                                @endif
                                            </small>
                                        </div>
                                        <small class="text-muted text-nowrap ms-3">
                                            {{ $event->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">Chưa có lịch sử thao tác</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cột phải: Thông tin phụ --}}
        <div class="col-lg-4">
            {{-- Thông tin vấn đề giao hàng --}}
            @if($return->order->deliveryIssues && $return->order->deliveryIssues->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Vấn đề giao hàng
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($return->order->deliveryIssues as $issue)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-warning">{{ $issue->issue_type }}</span>
                            <small class="text-muted">{{ $issue->issue_time ? $issue->issue_time->format('d/m/Y') : 'N/A' }}</small>
                        </div>
                        <p class="mb-1 small">{{ $issue->issue_note }}</p>
                        @if($issue->reporter)
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>{{ $issue->reporter->name }}
                        </small>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick actions --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('hub.orders.show', $return->order_id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-box me-2"></i>Xem đơn hàng gốc
                        </a>
                        @if($return->return_driver_id && $return->driver)
                        <a href="tel:{{ $return->driver->phone }}" class="btn btn-outline-info">
                            <i class="fas fa-phone me-2"></i>Liên hệ tài xế
                        </a>
                        @endif
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>In phiếu hoàn
                        </button>
                    </div>
                </div>
            </div>

            {{-- Thống kê nhanh --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Thống kê
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Thời gian xử lý</small>
                        <div class="fw-bold">
                            @if($return->status === OrderReturn::STATUS_COMPLETED && $return->completed_at)
                                {{ $return->initiated_at->diffForHumans($return->completed_at, true) }}
                            @else
                                {{ $return->initiated_at->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Tổng đơn hoàn hôm nay</small>
                        <div class="fw-bold">
                            {{ \App\Models\Driver\Orders\OrderReturn::where('order_id', 'LIKE', 
                                \App\Models\Customer\Dashboard\Orders\Order::where('post_office_id', $return->order->post_office_id)
                                    ->pluck('id')
                                    ->toArray())
                                ->whereDate('initiated_at', today())
                                ->count() }} đơn
                        </div>
                    </div>
                    @if($return->return_driver_id)
                    <div>
                        <small class="text-muted">Đơn hoàn của tài xế</small>
                        <div class="fw-bold">
                            {{ \App\Models\Driver\Orders\OrderReturn::where('return_driver_id', $return->return_driver_id)
                                ->where('status', OrderReturn::STATUS_COMPLETED)
                                ->count() }} đơn
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Hủy hoàn --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hub.returns.cancel', $return->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle text-danger me-2"></i>Hủy hoàn hàng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Bạn có chắc chắn muốn hủy đơn hoàn này?
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required 
                                  placeholder="Nhập lý do hủy hoàn hàng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Xác nhận hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: relative;
    width: 20px;
    padding-top: 5px;
}

.timeline-marker::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 15px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
    transform: translateX(-50%);
}

.timeline-item:last-child .timeline-marker::before {
    display: none;
}

@media print {
    .btn, .breadcrumb, nav, .card-header, .modal {
        display: none !important;
    }
}
</style>
@endsection