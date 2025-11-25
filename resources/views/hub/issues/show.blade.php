@extends('hub.layouts.app')

@section('title', 'Chi tiết vấn đề #' . $issue->id)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-danger"></i> 
                        Vấn đề giao hàng #{{ $issue->id }}
                    </h4>
                    <p class="text-muted small mb-0">Đơn hàng #{{ $issue->order->id }}</p>
                </div>
                <a href="{{ route('hub.issues.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <!-- ✅ THÊM: Thông báo nếu đã có OrderReturn -->
            @if($issue->orderReturn)
            <div class="alert alert-warning border-warning mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">
                            <i class="fas fa-undo"></i> Đơn này đã được chuyển hoàn hàng
                        </h6>
                        <p class="mb-0 small">
                            Trạng thái hoàn: 
                            <span class="badge bg-{{ $issue->orderReturn->status_badge }}">
                                {{ $issue->orderReturn->status_label }}
                            </span>
                        </p>
                    </div>
                    <a href="{{ route('hub.returns.show', $issue->orderReturn->id) }}" 
                       class="btn btn-warning">
                        <i class="fas fa-eye"></i> Xem đơn hoàn
                    </a>
                </div>
            </div>
            @endif

            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Thông tin Issue -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-danger text-white py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle"></i> Thông tin vấn đề
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Loại vấn đề</label>
                                    <div>
                                        <span class="badge bg-danger">{{ $issue->issue_type_label }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Thời gian báo cáo</label>
                                    <div>{{ $issue->issue_time->format('H:i d/m/Y') }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="small text-muted mb-1">Chi tiết vấn đề</label>
                                    <div class="alert alert-light border mb-0">
                                        {{ $issue->issue_note }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Người báo cáo</label>
                                    <div>
                                        <i class="fas fa-user"></i> {{ $issue->reporter->name }}
                                        <br>
                                        <small class="text-muted">{{ $issue->reporter->phone }}</small>
                                    </div>
                                </div>
                                @if($issue->issue_latitude && $issue->issue_longitude)
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Vị trí báo cáo</label>
                                    <div>
                                        <a href="{{ $issue->google_maps_link }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-map-marker-alt"></i> Xem trên bản đồ
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin đơn hàng -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-box"></i> Thông tin đơn hàng
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Người nhận</label>
                                    <div class="fw-bold">{{ $issue->order->recipient_name }}</div>
                                    <small class="text-muted">{{ $issue->order->recipient_phone }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Trạng thái đơn</label>
                                    <div>
                                        <span class="badge bg-{{ $issue->order->status_badge }}">
                                            {{ $issue->order->status_label }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="small text-muted mb-1">Địa chỉ giao</label>
                                    <div class="alert alert-light border mb-0">
                                        {{ $issue->order->recipient_full_address }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Số lần giao thất bại</label>
                                    <div>
                                        <span class="badge bg-danger">
                                            {{ $issue->order->delivery_attempt_count }} lần
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hàng hóa -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white py-3">
                            <h6 class="mb-0"><i class="fas fa-box"></i> Hàng hóa</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th>SL</th>
                                            <th>Trọng lượng</th>
                                            <th class="text-end">Giá trị</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($issue->order->products as $product)
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
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Trạng thái xử lý -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-{{ $issue->isResolved() ? 'success' : 'warning' }} text-white py-3">
                            <h6 class="mb-0">
                                <i class="fas {{ $issue->isResolved() ? 'fa-check-circle' : 'fa-clock' }}"></i> 
                                Trạng thái xử lý
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($issue->isResolved())
                                <div class="alert alert-success border-success mb-3">
                                    <strong>Đã xử lý:</strong>
                                    <span class="badge bg-{{ $issue->resolution_badge }}">
                                        {{ $issue->resolution_action_label }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Người xử lý:</small>
                                    <div>{{ $issue->resolver->name }}</div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Thời gian:</small>
                                    <div>{{ $issue->resolved_at->format('H:i d/m/Y') }}</div>
                                </div>
                                @if($issue->resolution_note)
                                <div>
                                    <small class="text-muted">Ghi chú:</small>
                                    <div class="small">{{ $issue->resolution_note }}</div>
                                </div>
                                @endif
                            @else
                                <div class="alert alert-warning border-warning mb-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Chưa xử lý</strong>
                                </div>
                                
                                <!-- Form xử lý -->
                                <form method="POST" action="{{ route('hub.issues.resolve', $issue->id) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            Quyết định <span class="text-danger">*</span>
                                        </label>
                                        <select name="action" class="form-select" required>
                                            <option value="">-- Chọn hành động --</option>
                                            <option value="retry">🔄 Thử giao lại</option>
                                            <option value="return">📦 Hoàn về sender</option>
                                            <option value="hold_at_hub">⏸️ Giữ tại hub</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ghi chú</label>
                                        <textarea name="note" class="form-control" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-check"></i> Xác nhận xử lý
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Lịch sử issues khác của đơn này -->
                    @if($issue->order->deliveryIssues->count() > 1)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-dark text-white py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-history"></i> 
                                Lịch sử vấn đề ({{ $issue->order->deliveryIssues->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($issue->order->deliveryIssues as $otherIssue)
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-danger">{{ $otherIssue->issue_type_label }}</span>
                                    <small class="text-muted">{{ $otherIssue->issue_time->format('d/m H:i') }}</small>
                                </div>
                                <p class="small mb-1">{{ Str::limit($otherIssue->issue_note, 80) }}</p>
                                @if($otherIssue->isResolved())
                                <small class="text-success">
                                    <i class="fas fa-check"></i> {{ $otherIssue->resolution_action_label }}
                                </small>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection