@extends('admin.layouts.app')

@section('title', 'Danh sách hồ sơ tài xế')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Danh sách hồ sơ tài xế</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-light text-center">
                    <tr>
                        <th>#</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Loại xe</th>
                        <th>Trạng thái</th>
                        <th>Ngày nộp</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($drivers as $driver)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $driver->full_name }}</td>
                            <td>{{ $driver->email }}</td>
                            <td>{{ ucfirst($driver->vehicle_type) }}</td>
                            <td class="text-center">
                                @if($driver->status === 'approved')
                                    <span class="text-success">Đã duyệt</span>
                                @elseif($driver->status === 'pending')
                                    <span class="text-warning">Chờ duyệt</span>
                                @else
                                    <span class="text-secondary">{{ ucfirst($driver->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $driver->created_at->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.driver.show', $driver->id) }}" class="btn btn-sm btn-info">
                                    Xem hồ sơ
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $drivers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
