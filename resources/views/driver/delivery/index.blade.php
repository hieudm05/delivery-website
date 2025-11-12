@extends('driver.layouts.app')

@section('title', 'Danh sách đơn giao hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📦 Danh sách đơn giao hàng</h5>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter & Search -->
                    <form method="GET" action="{{ route('driver.delivery.index') }}" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}> Tất cả trạng thái</option>
                                    <option value="{{ \App\Models\Customer\Dashboard\Orders\Order::STATUS_AT_HUB }}" {{ $status == \App\Models\Customer\Dashboard\Orders\Order::STATUS_AT_HUB ? 'selected' : '' }}>
                                        Tại bưu cục
                                    </option>
                                    <option value="{{ \App\Models\Customer\Dashboard\Orders\Order::STATUS_SHIPPING }}" {{ $status == \App\Models\Customer\Dashboard\Orders\Order::STATUS_SHIPPING ? 'selected' : '' }}>
                                        Đang giao
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="🔎 Tìm theo mã đơn, người nhận, SĐT..." value="{{ $search }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                            </div>
                        </div>
                    </form>

                    <!-- Orders Table -->
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mã đơn</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Người nhận</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Địa chỉ</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thời gian giao</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">COD</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">#{{ $order->id }}</h6>
                                                    @if($order->isPartOfGroup())
                                                        <p class="text-xs text-secondary mb-0">
                                                            Nhóm: #{{ $order->order_group_id }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $order->recipient_name }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $order->recipient_phone }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs mb-0" style="max-width: 200px;">
                                                    {{ $order->recipient_full_address }}
                                                </p>
                                            </td>
                                            <td>
                                                <span class="text-xs">
                                                    {{ $order->delivery_time ? $order->delivery_time->format('d/m/Y H:i') : 'Chưa có' }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $payment = $order->payment_details;
                                                @endphp
                                                @if($payment['has_cod'])
                                                    <span class="bg bg-sm bg-gradient-warning">
                                                        {{ number_format($payment['cod_amount']) }}đ
                                                    </span>
                                                @else
                                                    <span class="text-xs text-secondary">Không</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="bg bg-sm bg-gradient-{{ $order->status_badge }}">
                                                    {{ $order->status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('driver.delivery.show', $order->id) }}" 
                                                       class="btn btn-sm btn-info" title="Chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_AT_HUB)
                                                        <form method="POST" action="{{ route('driver.delivery.start', $order->id) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary" 
                                                                    onclick="return confirm('Bắt đầu giao đơn này?')" title="Bắt đầu giao">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($order->status === \App\Models\Customer\Dashboard\Orders\Order::STATUS_SHIPPING)
                                                        <a href="{{ route('driver.delivery.form', $order->id) }}" 
                                                           class="btn btn-sm btn-success" title="Hoàn thành">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="{{ route('driver.delivery.failure.form', $order->id) }}" 
                                                           class="btn btn-sm btn-danger" title="Báo thất bại">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-secondary mb-3"></i>
                            <p class="text-secondary">Không có đơn hàng nào cần giao</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection