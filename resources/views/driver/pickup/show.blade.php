@extends('driver.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col">
            <a href="{{ route('driver.pickup.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Cột trái: Thông tin đơn hàng -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Đơn hàng #{{ $order->id }}</h5>
                </div>
                <div class="card-body">
                    <!-- Trạng thái -->
                    <div class="alert alert-{{ $order->status_badge }} mb-4">
                        <strong>Trạng thái:</strong> {{ $order->status_label }}
                    </div>

                    <!-- Người gửi -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="text-primary"><i class="fas fa-store"></i> Thông tin người gửi (Shop)</h6>
                        <p class="mb-1"><strong>Tên:</strong> {{ $order->sender_name }}</p>
                        <p class="mb-1"><strong>SĐT:</strong> 
                            <a href="tel:{{ $order->sender_phone }}" class="text-decoration-none">
                                {{ $order->sender_phone }}
                            </a>
                        </p>
                        <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->sender_address }}</p>
                        @if($order->sender_latitude && $order->sender_longitude)
                        <a href="https://www.google.com/maps?q={{ $order->sender_latitude }},{{ $order->sender_longitude }}" 
                           target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-map-marked-alt"></i> Xem bản đồ
                        </a>
                        @endif
                    </div>

                    <!-- Người nhận -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6 class="text-success"><i class="fas fa-user"></i> Thông tin người nhận</h6>
                        <p class="mb-1"><strong>Tên:</strong> {{ $order->recipient_name }}</p>
                        <p class="mb-1"><strong>SĐT:</strong> {{ $order->recipient_phone }}</p>
                        <p class="mb-1"><strong>Địa chỉ:</strong> {{ $order->recipient_full_address }}</p>
                    </div>

                    <!-- Thời gian -->
                    <div class="border-bottom pb-3 mb-3">
                        <h6><i class="fas fa-clock"></i> Thời gian</h6>
                        <p class="mb-1"><strong>Lấy hàng tại shop:</strong> 
                            <span class="text-warning">
                                {{ $order->pickup_time ? $order->pickup_time->format('H:i - d/m/Y') : 'Chưa xác định' }}
                            </span>
                        </p>
                        <p class="mb-0"><strong>Giao hàng dự kiến :</strong> 
                            {{ $order->delivery_time ? $order->delivery_time->format('H:i - d/m/Y') : 'Chưa xác định' }}
                        </p>
                    </div>

                    <!-- Sản phẩm -->
                    <div class="mb-3">
                        <h6><i class="fas fa-boxes"></i> Danh sách hàng hóa</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên hàng</th>
                                        <th class="text-center">SL</th>
                                        <th class="text-end">Khối lượng</th>
                                        <th class="text-end">Giá trị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">{{ $product->quantity }}</td>
                                        <td class="text-end">{{ $product->weight }} g</td>
                                        <td class="text-end">{{ number_format($product->value) }}đ</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Giá trị COD -->
                    <div class="alert alert-warning">
                      <div class="col-md-6">
                          <strong>Thu COD:</strong> 
                        <span class="fs-5">{{ number_format($order->cod_amount) }}đ</span>
                      </div>
                       <div class="col-md-12">
                         <strong>Phí Ship:</strong> 
                        <span class="fs-5">{{ number_format($order->shipping_fee) ?? "Free"}}đ</span>
                        <span class="fs-6">( {{ $order->payer =="sender" ? "Người gửi thanh toán" : "Người nhận thanh toán" }} )</span>
                       </div>
                        <div class="col-md-6">
                         <strong>Tổng thu người nhận:</strong> 
                        <span class="fs-5">{{ number_format($order->recipient_total) }}đ</span>
                       </div>
                    </div>

                    <!-- Ghi chú -->
                    @if($order->note)
                    <div class="alert alert-info">
                        <strong><i class="fas fa-sticky-note"></i> Ghi chú:</strong><br>
                        {{ $order->note }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột phải: Form xác nhận -->
        <div class="col-lg-5">
            @if($order->status === 'picking_up')
            <!-- Form xác nhận lấy hàng -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Xác nhận lấy hàng</h5>
                </div>
                <div class="card-body">
                    <form id="confirmPickupForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Số kiện thực tế -->
                        <div class="mb-3">
                            <label class="form-label">Số kiện thực tế <span class="text-danger">*</span></label>
                            <input type="number" name="actual_packages" class="form-control" 
                                   value="{{ $order->products->count() }}" min="1" required>
                            <small class="text-muted">Số kiện đã nhận từ shop</small>
                        </div>

                        <!-- Cân nặng thực tế -->
                        <div class="mb-3">
                            <label class="form-label">Cân nặng thực tế (kg)</label>
                            <input type="number" name="actual_weight" class="form-control" 
                                   value="{{ $order->products->sum('weight') }}" step="0.1" min="0">
                            <small class="text-muted">Để trống nếu chưa cân</small>
                        </div>

                        <!-- Chụp ảnh hàng hóa -->
                        <div class="mb-3">
                            <label class="form-label">Chụp ảnh hàng hóa <span class="text-danger">*</span></label>
                            <input type="file" name="images[]" class="form-control" 
                                   accept="image/*" capture="environment" multiple required>
                            <small class="text-muted">Chụp ít nhất 1 ảnh bưu kiện đã nhận</small>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3" 
                                      placeholder="Tình trạng hàng hóa, đặc điểm..."></textarea>
                        </div>

                        <!-- Nút xác nhận -->
                        <button type="submit" class="btn btn-success w-100" id="btnConfirmPickup">
                            <i class="fas fa-check"></i> Xác nhận đã lấy hàng
                        </button>
                    </form>
                </div>
            </div>

            <!-- Form báo cáo vấn đề -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Báo cáo vấn đề</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#issueModal">
                        <i class="fas fa-times-circle"></i> Không thể lấy hàng
                    </button>
                </div>
            </div>
            @elseif($order->status === 'confirmed')
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-hand-paper fa-3x text-warning mb-3"></i>
                    <h5>Chưa bắt đầu lấy hàng</h5>
                    <p class="text-muted">Nhấn nút bên dưới để bắt đầu lấy hàng</p>
                    <button class="btn btn-primary btn-lg w-100" id="btnStartPickup">
                        <i class="fas fa-play"></i> Bắt đầu lấy hàng
                    </button>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                    <h5>{{ $order->status_label }}</h5>
                    <p class="text-muted">Đơn hàng không ở trạng thái cần lấy hàng</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal báo cáo vấn đề -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Báo cáo vấn đề</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="reportIssueForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Loại vấn đề -->
                    <div class="mb-3">
                        <label class="form-label">Vấn đề gặp phải <span class="text-danger">*</span></label>
                        <select name="issue_type" class="form-select" required>
                            <option value="">-- Chọn vấn đề --</option>
                            <option value="shop_closed">Shop đóng cửa</option>
                            <option value="wrong_address">Địa chỉ sai/không tìm thấy</option>
                            <option value="no_goods">Shop không có hàng</option>
                            <option value="customer_cancel">Khách hủy đơn</option>
                            <option value="other">Vấn đề khác</option>
                        </select>
                    </div>

                    <!-- Chi tiết vấn đề -->
                    <div class="mb-3">
                        <label class="form-label">Mô tả chi tiết <span class="text-danger">*</span></label>
                        <textarea name="issue_note" class="form-control" rows="4" 
                            placeholder="Mô tả cụ thể vấn đề gặp phải..." required></textarea>
                    </div>

                    <!-- Ảnh minh chứng -->
                    <div class="mb-3">
                        <label class="form-label">Ảnh minh chứng (nếu có)</label>
                        <input type="file" name="images[]" class="form-control" 
                               accept="image/*" capture="environment" multiple>
                        <div id="issueImagePreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger" id="btnReportIssue">
                        <i class="fas fa-paper-plane"></i> Gửi báo cáo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const orderId = {{ $order->id }};

    // Tạo kiểu Toast nhỏ gọn
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });

    // Bắt đầu lấy hàng
    $('#btnStartPickup').click(function() {
        Swal.fire({
            title: 'Xác nhận',
            text: 'Bắt đầu lấy hàng cho đơn #' + orderId + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Bắt đầu',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const btn = $('#btnStartPickup');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

            $.ajax({
                url: `/driver/pickup/${orderId}/start`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        setTimeout(() => location.reload(), 1000);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', xhr.responseJSON?.message || 'Có lỗi xảy ra', 'error');
                    btn.prop('disabled', false)
                        .html('<i class="fas fa-play"></i> Bắt đầu lấy hàng');
                }
            });
        });
    });

    // Preview ảnh
    $('input[name="images[]"]').change(function() {
        previewImages(this, '#imagePreview');
    });
    $('input[name="issue_note"]').closest('form').find('input[name="images[]"]').change(function() {
        previewImages(this, '#issueImagePreview');
    });

    function previewImages(input, previewId) {
        $(previewId).html('');
        if (input.files) {
            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $(previewId).append(`
                        <div class="d-inline-block position-relative me-2 mb-2">
                            <img src="${e.target.result}" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover">
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                ${index + 1}
                            </span>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            });
        }
    }


    // Xác nhận lấy hàng
    $('#confirmPickupForm').submit(function(e) {
        e.preventDefault();
        submitPickupForm();

        function submitPickupForm() {
            const formData = new FormData($('#confirmPickupForm')[0]);
            const btn = $('#btnConfirmPickup');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

            $.ajax({
                url: `/driver/pickup/${orderId}/confirm`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Thành công', response.message, 'success');
                        setTimeout(() => window.location.href = '{{ route("driver.pickup.index") }}', 1500);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', xhr.responseJSON?.message || 'Có lỗi xảy ra', 'error');
                    btn.prop('disabled', false)
                        .html('<i class="fas fa-check"></i> Xác nhận đã lấy hàng');
                }
            });
        }
    });

    // Báo cáo vấn đề
    $('#reportIssueForm').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn chắc chắn muốn gửi báo cáo vấn đề này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Gửi báo cáo',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const formData = new FormData(this);
            const btn = $('#btnReportIssue');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang gửi...');

            $.ajax({
                url: `/driver/pickup/${orderId}/report-issue`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Thành công', response.message, 'success');
                        setTimeout(() => window.location.href = '{{ route("driver.pickup.index") }}', 1500);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', xhr.responseJSON?.message || 'Có lỗi xảy ra', 'error');
                    btn.prop('disabled', false)
                        .html('<i class="fas fa-paper-plane"></i> Gửi báo cáo');
                }
            });
        });
    });
});
</script>

@endsection