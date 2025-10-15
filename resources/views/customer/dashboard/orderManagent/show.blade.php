@extends('customer.dashboard.layouts.app')
@section('title', 'Chi tiết vận đơn')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center rounded-top-4">
            <h5 class="mb-0 text-white">Chi tiết vận đơn #{{ $order->id }}</h5>
            <a href="{{ route('customer.orderManagent.index') }}" class="btn btn-light btn-sm">
                ← Quay lại danh sách
            </a>
        </div>

        <div class="card-body p-4">

            {{-- 1️⃣ Thông tin người gửi và người nhận --}}
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="text-uppercase text-muted mb-3">Người gửi</h6>
                        <p class="mb-1"><strong>{{ $order->sender_name }}</strong></p>
                        <p class="mb-1">📞 {{ $order->sender_phone }}</p>
                        <p class="mb-0">🏠 {{ $order->sender_address }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="text-uppercase text-muted mb-3">Người nhận</h6>
                        <p class="mb-1"><strong>{{ $order->recipient_name }}</strong></p>
                        <p class="mb-1">📞 {{ $order->recipient_phone }}</p>
                        <p class="mb-0">🏠 {{ $order->recipient_full_address }}</p>
                    </div>
                </div>
            </div>

            {{-- 2️⃣ Thông tin vận đơn --}}
            <div class="row mt-4 g-4">
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="text-uppercase text-muted mb-3">Thông tin giao hàng</h6>
                        <p><strong>Mã bưu cục:</strong> {{ $order->post_office_id ?? '—' }}</p>
                        <p><strong>Loại hàng:</strong> {{ ucfirst($order->item_type) }}</p>
                        <p><strong>Dịch vụ:</strong>
                            @if(!empty($order->services))
                                {{ implode(', ', $order->services) }}
                            @else
                                Không có
                            @endif
                        </p>
                        <p><strong>Ghi chú:</strong> {{ $order->note ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="text-uppercase text-muted mb-3">Trạng thái & Thời gian</h6>
                        <p><strong>Trạng thái:</strong>
                            <span class="badge text-bg-{{ match($order->status) {
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'picking_up' => 'primary',
                                'picked_up' => 'secondary',
                                'shipping' => 'light',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                default => 'dark'
                            } }}">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </p>
                        <p><strong>Thu hộ (COD):</strong> {{ number_format($order->cod_amount, 0, ',', '.') }} đ</p>
                        <p><strong>Lấy hàng:</strong> {{ $order->pickup_time?->format('H:i d/m/Y') }}</p>
                        <p><strong>Giao hàng:</strong> {{ $order->delivery_time?->format('H:i d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- 3️⃣ Bảng hàng hoá --}}
            <div class="mt-5">
                <h6 class="text-uppercase text-muted mb-3">Danh sách hàng hoá</h6>
                <div class="table-responsive rounded-4 shadow-sm">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Khối lượng (g)</th>
                                <th>Giá trị (đ)</th>
                                <th>Kích thước (D x R x C)</th>
                                <th>Đặc biệt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->products as $index => $product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    <td>{{ number_format($product->weight) }}</td>
                                    <td>{{ number_format($product->value) }}</td>
                                    <td>{{ $product->length }} x {{ $product->width }} x {{ $product->height }}</td>
                                    <td>
                                        @if(!empty($product->specials))
                                            <ul class="mb-0 ps-3">
                                                @foreach($product->specials as $special)
                                                    <li>{{ $special }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
