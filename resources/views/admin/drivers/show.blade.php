@extends('admin.layouts.app')

@section('title', 'Chi tiết hồ sơ tài xế')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Chi tiết hồ sơ tài xế</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row">
                <!-- Cột trái -->
                <div class="col-md-6">
                    <h5 class="text-primary">{{ $driver->full_name }}</h5>
                    <p><strong>Email:</strong> {{ $driver->email }}</p>
                    <p><strong>Loại xe:</strong> {{ ucfirst($driver->vehicle_type) }}</p>
                    <p><strong>Số GPLX:</strong> {{ $driver->license_number }}</p>
                    <p><strong>Kinh nghiệm:</strong> {{ $driver->experience }} năm</p>
                    <p><strong>Trạng thái:</strong>
                        @if($driver->status === 'approved')
                            <span class="text-success">Đã duyệt</span>
                        @elseif($driver->status === 'pending')
                            <span class="text-warning">Chờ duyệt</span>
                        @else
                            <span class="text-secondary">{{ ucfirst($driver->status) }}</span>
                        @endif
                    </p>
                    <p><strong>Ngày tạo:</strong> {{ $driver->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <!-- Cột phải -->
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6 text-center">
                            <h6>Ảnh GPLX</h6>
                            <img src="{{ asset('storage/'.$driver->license_image) }}" class="img-fluid rounded shadow-sm mb-2" alt="GPLX">
                        </div>
                        <div class="col-md-6 text-center">
                            <h6>Ảnh CCCD</h6>
                            <img src="{{ asset('storage/'.$driver->identity_image) }}" class="img-fluid rounded shadow-sm mb-2" alt="CCCD">
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            @if($driver->status !== 'approved')
            <form action="{{ route('admin.driver.approve', $driver->id) }}" method="POST" onsubmit="return confirm('Xác nhận duyệt hồ sơ này?')">
                @csrf
                <button type="submit" class="btn btn-success">Duyệt hồ sơ</button>
                <a href="{{ route('admin.driver.index') }}" class="btn btn-secondary">Quay lại</a>
            </form>
            @else
                {{-- <div class="alert alert-success">
                    Hồ sơ này đã được duyệt vào lúc {{ $driver->approved_at ? $driver->approved_at->format('d/m/Y H:i') : '...' }}.
                </div> --}}
                <a href="{{ route('admin.driver.index') }}" class="btn btn-secondary">Quay lại danh sách</a>
            @endif
        </div>
    </div>
</div>
@endsection
