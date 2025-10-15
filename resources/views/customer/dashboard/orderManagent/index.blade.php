@extends('customer.dashboard.layouts.app')

@section('title', 'Quản lý vận đơn')

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary">
            <i class="bi bi-truck me-2"></i> Quản lý vận đơn
        </h4>
        <a href="{{ route('customer.orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tạo đơn mới
        </a>
    </div>

    <!-- Card hiển thị từng vận đơn -->
    <div class="row g-4">
        @foreach($orders as $order)
            @php
                $rawProducts = $order->products_json;
                $products = is_string($rawProducts) ? json_decode($rawProducts, true) : $rawProducts;
                $firstProduct = $products[0] ?? [];
                $statusBadge = [
                    'pending' => 'bg-warning text-dark',
                    'confirmed' => 'bg-warning text-dark',
                    'picking_up' => 'bg-warning text-dark',
                    'picked_up' => 'bg-warning text-dark',
                    'shipping' => 'bg-info text-white',
                    'delivered' => 'bg-success text-white',
                    'cancelled' => 'bg-danger text-white',
                ][$order->status ?? 'pending'];
            @endphp

            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="fw-bold text-secondary">Mã đơn: #{{ $order->id }}</h6>
                            <span class="badge {{ $statusBadge }}">
                                {{ ucfirst($order->status ?? 'Mới tạo') }}
                            </span>
                        </div>

                        <hr style="background-color: red" >

                        <div class="mb-2">
                            <i class="bi bi-box-seam me-2 text-primary"></i>
                            <strong>{{ $firstProduct['name'] ?? 'Sản phẩm' }}</strong>
                            <div class="small text-muted">
                                Loại: {{ $firstProduct['type'] ?? 'package' }} |
                                SL: {{ $firstProduct['quantity'] ?? 1 }}
                            </div>
                        </div>

                        <div class="mb-2">
                            <i class="bi bi-person-fill me-2 text-success"></i>
                            <span class="fw-bold">Người gửi:</span> {{ $order->sender_name }}<br>
                            <span class="text-muted small">{{ $order->sender_address }}</span>
                        </div>

                        <div class="mb-2">
                            <i class="bi bi-geo-alt-fill me-2 text-danger"></i>
                            <span class="fw-bold">Người nhận:</span> {{ $order->recipient_name }}<br>
                            <span class="text-muted small">{{ $order->recipient_full_address }}</span>
                        </div>

                        <div class="mb-3">
                            <i class="bi bi-clock me-2 text-secondary"></i>
                            Giao dự kiến: {{ \Carbon\Carbon::parse($order->delivery_time)->format('H:i d/m/Y') }}
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('customer.orderManagent.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                 Xem chi tiết
                            </a>
                            <div>
                                @if($order->status === 'pending')
                                <a href="#" class="btn btn-sm btn-outline-success rounded-pill me-1">
                                    Sửa
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-danger rounded-pill">
                                    Xoá
                                </a>
                                @endif
                              
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(count($orders) == 0)
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            Chưa có vận đơn nào được tạo.
        </div>
    @endif

</div>
@endsection
