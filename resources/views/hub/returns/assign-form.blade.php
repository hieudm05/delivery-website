@extends('hub.layouts.app')

@section('title', 'Phân công tài xế hoàn hàng')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus"></i> Phân công tài xế hoàn hàng
                        </h5>
                        <a href="{{ route('hub.returns.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Order Info -->
                    <div class="alert border-info mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="mb-2"><i class="fas fa-box"></i> Thông tin đơn hàng</h6>
                                <div class="small">
                                    <div><strong>Mã đơn:</strong> #{{ $return->order->id }}</div>
                                    <div><strong>Số lần thất bại:</strong> {{ $return->failed_attempts }} lần</div>
                                    <div><strong>Khởi tạo:</strong> {{ $return->initiated_at->format('H:i d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-2"><i class="fas fa-user"></i> Sender</h6>
                                <div class="small">
                                    <div><strong>Tên:</strong> {{ $return->sender_name }}</div>
                                    <div><strong>SĐT:</strong> {{ $return->sender_phone }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <h6 class="mb-2"><i class="fas fa-map-marker-alt"></i> Địa chỉ hoàn</h6>
                                <div class="small">{{ $return->sender_address }}</div>
                                @if($return->sender_map_link)
                                    <a href="{{ $return->sender_map_link }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-map"></i> Xem bản đồ
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Lý do hoàn -->
                    <div class="alert alert-warning border-warning mb-4">
                        <h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Lý do hoàn hàng</h6>
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark">{{ $return->reason_type_label }}</span>
                        </div>
                        <div class="small">{{ $return->reason_detail }}</div>
                    </div>

                    <!-- Chi phí -->
                    <div class="alert alert-light border mb-4">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="small text-muted mb-1">Phí hoàn hàng</div>
                                <div class="h5 text-danger mb-0">{{ number_format($return->return_fee) }}đ</div>
                            </div>
                            @if($return->cod_amount > 0)
                            <div class="col-6">
                                <div class="small text-muted mb-1">Tiền COD cần trả</div>
                                <div class="h5 text-warning mb-0">{{ number_format($return->cod_amount) }}đ</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Form phân công -->
                    <form method="POST" action="{{ route('hub.returns.assign', $return->id) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Chọn tài xế hoàn hàng <span class="text-danger">*</span>
                            </label>
                            <select name="driver_id" 
                                    class="form-select @error('driver_id') is-invalid @enderror" 
                                    required
                                    onchange="showDriverInfo(this)">
                                <option value="">-- Chọn tài xế --</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" 
                                            data-name="{{ $driver->full_name }}"
                                            data-phone="{{ $driver->phone }}"
                                            data-active="{{ $driver->active_returns ?? 0 }}">
                                        {{ $driver->full_name }} ({{ $driver->phone }})
                                        @if(isset($driver->active_returns) && $driver->active_returns > 0)
                                            - Đang hoàn: {{ $driver->active_returns }} đơn
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('driver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Driver Info -->
                            <div id="driverInfo" class="alert alert-light border mt-3" style="display: none;">
                                <h6 class="mb-2"><i class="fas fa-user-circle"></i> Thông tin tài xế</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Tên tài xế:</small>
                                        <div class="fw-bold" id="driverName"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Số điện thoại:</small>
                                        <div class="fw-bold" id="driverPhone"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Đơn đang hoàn:</small>
                                        <div class="fw-bold" id="driverActive"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Ghi chú (Tùy chọn)</label>
                            <textarea name="note" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Ghi chú cho tài xế về đơn hoàn này..."></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                VD: Lưu ý địa chỉ khó tìm, cần liên hệ trước khi đến...
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-check"></i> Xác nhận phân công
                            </button>
                            <a href="{{ route('hub.returns.index') }}" class="btn btn-outline-secondary">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lịch sử issues -->
            @if($return->order->deliveryIssues->count() > 0)
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-danger text-white py-3">
                    <h6 class="mb-0">
                        <i class="fas fa-history"></i> 
                        Lịch sử giao hàng thất bại ({{ $return->order->deliveryIssues->count() }} lần)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Loại vấn đề</th>
                                    <th>Chi tiết</th>
                                    <th>Tài xế</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($return->order->deliveryIssues as $issue)
                                <tr>
                                    <td>
                                        <small>{{ $issue->issue_time->format('H:i d/m') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ $issue->issue_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($issue->issue_note, 50) }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $issue->reporter->name }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function showDriverInfo(select) {
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('driverName').textContent = option.dataset.name;
        document.getElementById('driverPhone').textContent = option.dataset.phone;
        document.getElementById('driverActive').textContent = option.dataset.active + ' đơn';
        document.getElementById('driverInfo').style.display = 'block';
    } else {
        document.getElementById('driverInfo').style.display = 'none';
    }
}
</script>
@endsection