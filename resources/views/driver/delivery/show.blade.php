@extends('driver.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Order Info Card -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📦 Chi tiết đơn hàng #{{ $order->id }}</h5>
                        <span class="badge bg-gradient-{{ $order->status_badge }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sender Info -->
                    <div class="mb-4">
                        <h6 class="text-sm font-weight-bold mb-2">📤 Thông tin người gửi</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-xs mb-1"><strong>Tên:</strong> {{ $order->sender_name }}</p>
                                <p class="text-xs mb-1"><strong>SĐT:</strong> {{ $order->sender_phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-xs mb-1"><strong>Địa chỉ:</strong> {{ $order->sender_address }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Recipient Info -->
                    <div class="mb-4">
                        <h6 class="text-sm font-weight-bold mb-2">📥 Thông tin người nhận</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-xs mb-1"><strong>Tên:</strong> {{ $order->recipient_name }}</p>
                                <p class="text-xs mb-1"><strong>SĐT:</strong> {{ $order->recipient_phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-xs mb-1"><strong>Địa chỉ:</strong> {{ $order->recipient_full_address }}</p>
                                @if($order->recipient_latitude && $order->recipient_longitude)
                                    <a href="https://www.google.com/maps?q={{ $order->recipient_latitude }},{{ $order->recipient_longitude }}" 
                                       target="_blank" class="btn btn-sm btn-info mt-2">
                                        <i class="fas fa-map-marker-alt"></i> Xem bản đồ
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Products -->
                    <div class="mb-4">
                        <h6 class="text-sm font-weight-bold mb-2">📦 Hàng hóa</h6>
                        @if($order->products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tên sản phẩm</th>
                                            <th>Số lượng</th>
                                            <th>Khối lượng</th>
                                            <th>Giá trị</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->products as $product)
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->quantity }}</td>
                                                <td>{{ $product->weight }}g</td>
                                                <td>{{ number_format($product->value) }}đ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-xs text-secondary">Không có thông tin sản phẩm chi tiết</p>
                        @endif
                    </div>

                    <hr>

                    <!-- Payment Details -->
                    <div class="mb-4">
                        <h6 class="text-sm font-weight-bold mb-2">💰 Chi phí</h6>
                        @php $payment = $order->payment_details; @endphp
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-xs mb-1"><strong>Phí vận chuyển:</strong> {{ number_format($payment['shipping_fee']) }}đ</p>
                                @if($payment['has_cod'])
                                    <p class="text-xs mb-1"><strong>COD:</strong> {{ number_format($payment['cod_amount']) }}đ</p>
                                    <p class="text-xs mb-1"><strong>Phí COD:</strong> {{ number_format($payment['cod_fee']) }}đ</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p class="text-xs mb-1"><strong>Người trả cước:</strong> 
                                    <span class="badge bg-info">{{ $payment['payer'] === 'sender' ? 'Người gửi' : 'Người nhận' }}</span>
                                </p>
                                @if($payment['has_cod'] && $payment['payer'] === 'recipient')
                                    <div class="alert alert-warning mt-2 py-2">
                                        <strong>⚠️ Cần thu COD: {{ number_format($payment['recipient_pays']) }}đ</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Images -->
                    @if($order->deliveryImages->count() > 0)
                        <hr>
                        <div class="mb-4">
                            <h6 class="text-sm font-weight-bold mb-2">📸 Ảnh giao hàng</h6>
                            <div class="row">
                                @foreach($order->deliveryImages as $image)
                                    <div class="col-md-3 mb-3">
                                        <img src="{{ $image->image_url }}" class="img-fluid rounded" alt="Delivery image">
                                        <p class="text-xs text-center mt-1">{{ $image->type_label }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Note -->
                    @if($order->note)
                        <hr>
                        <div>
                            <h6 class="text-sm font-weight-bold mb-2">📝 Ghi chú</h6>
                            <p class="text-xs">{{ $order->note }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Sidebar -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">⚡ Thao tác</h6>
                </div>
                <div class="card-body">
                    @if($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_AT_HUB)
                        <form method="POST" action="{{ route('driver.delivery.start', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 mb-2" 
                                    onclick="return confirm('Bắt đầu giao đơn hàng này?')">
                                <i class="fas fa-play"></i> Bắt đầu giao hàng
                            </button>
                        </form>
                    @endif

                    @if($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_SHIPPING)
                        <a href="{{ route('driver.delivery.form', $order->id) }}" 
                           class="btn btn-success w-100 mb-2">
                            <i class="fas fa-check-circle"></i> Giao hàng thành công
                        </a>
                        <a href="{{ route('driver.delivery.failure.form', $order->id) }}" 
                           class="btn btn-danger w-100 mb-2">
                            <i class="fas fa-times-circle"></i> Giao hàng thất bại
                        </a>
                    @endif

                    <a href="{{ route('driver.delivery.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">📋 Lịch sử đơn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-one-side">
                        <div class="timeline-block mb-3">
                            <span class="timeline-step bg-success">
                                <i class="fas fa-check"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Đơn hàng được tạo</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        @if($order->actual_pickup_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-info">
                                    <i class="fas fa-box"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Đã lấy hàng</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        {{ $order->actual_pickup_time->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($order->hub_transfer_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-primary">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Về bưu cục</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        {{ $order->hub_transfer_time->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($order->actual_delivery_start_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-warning">
                                    <i class="fas fa-truck"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Bắt đầu giao hàng</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        {{ $order->actual_delivery_start_time->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($order->actual_delivery_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Đã giao hàng</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        {{ $order->actual_delivery_time->format('d/m/Y H:i') }}
                                    </p>
                                    @if($order->received_by_name)
                                        <p class="text-xs mb-0">Người nhận: {{ $order->received_by_name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($order->delivery_issue_time)
                            <div class="timeline-block mb-3">
                                <span class="timeline-step bg-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                                <div class="timeline-content">
                                    <h6 class="text-dark text-sm font-weight-bold mb-0">Giao hàng thất bại</h6>
                                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                        {{ $order->delivery_issue_time->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="text-xs mb-0">{{ $order->delivery_issue_note }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection