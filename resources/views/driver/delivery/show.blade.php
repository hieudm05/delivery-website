@extends('driver.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="container-fluid py-4">

    <div class="row g-4">
        <!-- LEFT CONTENT -->
        <div class="col-lg-8">

            <!-- Order Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chi tiết đơn hàng #{{ $order->id }}</h5>
                    <span class="badge bg-{{ $order->status_badge }}">
                        {{ $order->status_label }}
                    </span>
                </div>

                <div class="card-body">

                    <!-- Sender -->
                    <div class="mb-4">
                        <h6 class="fw-bold small mb-2">Thông tin người gửi</h6>
                        <div class="row g-3">
                            <div class="col-md-6 small">
                                <p class="mb-1"><strong>Tên:</strong> {{ $order->sender_name }}</p>
                                <p class="mb-1"><strong>SĐT:</strong> {{ $order->sender_phone }}</p>
                            </div>
                            <div class="col-md-6 small">
                                <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->sender_address }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Recipient -->
                    <div class="mb-4">
                        <h6 class="fw-bold small mb-2">📥 Thông tin người nhận</h6>
                        <div class="row g-3">
                            <div class="col-md-6 small">
                                <p class="mb-1"><strong>Tên:</strong> {{ $order->recipient_name }}</p>
                                <p class="mb-1"><strong>SĐT:</strong> {{ $order->recipient_phone }}</p>
                            </div>
                            <div class="col-md-6 small">
                                <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->recipient_full_address }}</p>

                                @if($order->recipient_latitude)
                                    <a href="https://www.google.com/maps?q={{ $order->recipient_latitude }},{{ $order->recipient_longitude }}"
                                        class="btn btn-info btn-sm mt-2" target="_blank">
                                        <i class="fas fa-map-marker-alt"></i> Xem bản đồ
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Products -->
                    <div class="mb-4">
                        <h6 class="fw-bold small mb-2">📦 Hàng hóa</h6>

                        @if($order->products->count())
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên</th>
                                        <th>SL</th>
                                        <th>KL</th>
                                        <th>Kích thước</th>
                                        <th>Giá trị</th>
                                        <th>Đặc biệt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->quantity }}</td>
                                        <td>{{ $product->weight }}g</td>
                                        <td>{{ $product->length }}x{{ $product->width }}x{{ $product->height }}</td>
                                        <td>{{ number_format($product->value) }}đ</td>
                                        <td>
                                            @if($product->specials)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($product->specials as $special)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-exclamation-circle"></i> {{ $special }}
                                                    </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">Không có</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="small text-muted">Không có sản phẩm</p>
                        @endif
                    </div>

                    <hr>

                    <!-- Payment -->
                    @php $payment = $order->payment_details; @endphp
                    <div class="mb-4">
                        <h6 class="fw-bold small mb-2">💰 Chi phí</h6>

                        <div class="small mb-2">
                            <strong>Phí vận chuyển:</strong> 
                            {{ number_format($payment['shipping_fee']) }}đ 
                            <span class="text-muted">
                                ({{ $payment['payer'] === 'sender' ? 'người gửi trả' : 'người nhận trả' }})
                            </span>
                        </div>
                          @if($order->services)
                        <div class="small mb-2">
                            <strong>Tính chất hàng hoá:</strong> 
                            @foreach ($order->services as $service)
                                {{ $service}},
                            @endforeach
                        </div>
                        @endif
                        <div class="small mb-2">
                            <strong>Số tiền cần thu:</strong> {{ number_format($order['recipient_total'] ?? 0) }}đ
                        </div>
                      
                        @if($order['cod_amount'] > 0 )
                        <div class="alert alert-warning small py-2">
                            <strong>⚠️ Cần thu COD: {{ number_format($order['cod_amount']) }}đ</strong>
                        </div>
                        @endif
                    </div>

                    <!-- Delivery Images -->
                    @if($order->deliveryImages->count())
                    <hr>
                    <div class="mb-4">
                        <h6 class="fw-bold small mb-2">📸 Ảnh giao hàng</h6>
                        <div class="row g-3">
                            @foreach($order->deliveryImages as $image)
                            <div class="col-6 col-md-3">
                                <img src="{{ $image->image_url }}" class="img-fluid rounded shadow-sm" />
                                <p class="text-center small mt-1">{{ $image->type_label }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Note -->
                    @if($order->note)
                    <hr>
                    <h6 class="fw-bold small mb-2">📝 Ghi chú</h6>
                    <p class="small">{{ $order->note }}</p>
                    @endif

                </div>
            </div>

        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">⚡ Thao tác</h6>
                </div>

                <div class="card-body">

                    @if($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_AT_HUB)
                    <form method="POST" action="{{ route('driver.delivery.start', $order->id) }}">
                        @csrf
                        <button class="btn btn-primary w-100 mb-2"
                            onclick="return confirm('Bắt đầu giao đơn hàng?')">
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
                        class="btn btn-danger w-100 mb-3">
                        <i class="fas fa-times-circle"></i> Giao hàng thất bại
                    </a>
                    @endif

                    <a href="{{ route('driver.delivery.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
