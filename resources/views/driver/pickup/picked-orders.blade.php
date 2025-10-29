@extends('driver.layouts.app')

@section('title', 'Đơn hàng đã lấy hôm nay')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-check-circle text-success"></i> Đơn đã lấy hôm nay</h2>
            <p class="text-muted">Tổng: <strong>{{ $orders->count() }}</strong> đơn hàng</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('driver.pickup.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            @if($orders->count() > 0)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="fas fa-building"></i> Chuyển về bưu cục
            </button>
            @endif
        </div>
    </div>

    @if($orders->count() > 0)
    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $orders->count() }}</h3>
                    <small>Tổng đơn hàng</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $orders->sum('actual_packages') }}</h3>
                    <small>Tổng số kiện</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ number_format($orders->sum('actual_weight'), 1) }} kg</h3>
                    <small>Tổng khối lượng</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ number_format($orders->sum('cod_amount')) }}đ</h3>
                    <small>Tổng COD</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách đơn hàng -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Danh sách đơn hàng</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                    <i class="fas fa-check-square"></i> Chọn tất cả
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>Mã ĐH</th>
                            <th>Người gửi</th>
                            <th>Địa chỉ lấy hàng</th>
                            <th>Thời gian lấy</th>
                            <th class="text-center">Số kiện</th>
                            <th class="text-end">Khối lượng</th>
                            <th class="text-end">COD</th>
                            <th width="100"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                <input type="checkbox" class="order-checkbox" value="{{ $order->id }}">
                            </td>
                            <td>
                                <strong>#{{ $order->id }}</strong>
                            </td>
                            <td>
                                <div>{{ $order->sender_name }}</div>
                                <small class="text-muted">{{ $order->sender_phone }}</small>
                            </td>
                            <td>
                                <small>{{ Str::limit($order->sender_address, 40) }}</small>
                            </td>
                            <td>
                                <small>{{ $order->actual_pickup_time->format('H:i') }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $order->actual_packages }}</span>
                            </td>
                            <td class="text-end">
                                {{ number_format($order->actual_weight, 1) }} kg
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($order->cod_amount) }}đ</strong>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info btn-view-images" 
                                        data-id="{{ $order->id }}">
                                    <i class="fas fa-images"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle fa-3x mb-3"></i>
        <h5>Chưa có đơn hàng nào được lấy hôm nay</h5>
        <a href="{{ route('driver.pickup.index') }}" class="btn btn-primary mt-2">
            <i class="fas fa-box-open"></i> Xem đơn cần lấy
        </a>
    </div>
    @endif
</div>

<!-- Modal chuyển về bưu cục -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-building"></i> Chuyển về bưu cục</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transferForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <span id="selectedCount">0</span> đơn hàng được chọn
                    </div>

                    <!-- Thông tin bưu cục gần nhất -->
                    <div class="card mb-3 border-success" id="nearestHubCard" style="display: none;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="text-success mb-2">
                                        <i class="fas fa-map-marker-alt"></i> Bưu cục gần nhất
                                    </h6>
                                    <div id="nearestHubInfo">
                                        <div class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i> Đang tìm bưu cục gần nhất...
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" id="useNearestHubBtn" style="display: none;">
                                    <i class="fas fa-check"></i> Chọn
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Chọn bưu cục -->
                    <div class="mb-3">
                        <label class="form-label">
                            Bưu cục nhận hàng <span class="text-danger">*</span>
                            <button type="button" class="btn btn-sm btn-link p-0 ms-2" id="findNearestBtn">
                                <i class="fas fa-location-arrow"></i> Tìm gần nhất
                            </button>
                        </label>
                        <select name="hub_id" id="hubSelect" class="form-select" required>
                            <option value="">-- Chọn bưu cục --</option>

                            @foreach($postOffices ?? [] as $office)
                            <option value="{{ $office->id }}" 
                                    data-lat="{{ $office->post_office_lat ?? '' }}" 
                                    data-lng="{{ $office->post_office_lng ?? '' }}"
                                    data-address="{{ $office->post_office_address ?? '' }}">
                                {{ $office->post_office_name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="distanceInfo"></small>
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3" 
                                  placeholder="Ghi chú về lô hàng chuyển về..."></textarea>
                    </div>

                    <input type="hidden" name="order_ids" id="orderIdsInput">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnTransfer">
                        <i class="fas fa-truck"></i> Xác nhận chuyển
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xem ảnh -->
<div class="modal fade" id="imagesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-images"></i> Ảnh lấy hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="imagesContainer">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const GOONG_API_KEY = '{{ config("services.goong.api_key") }}' 
    let driverLocation = null;
    let nearestHub = null;

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    });

    // ✅ Lấy vị trí: Ưu tiên backend, không dùng GPS
    function getDriverLocation() {
        getDriverLocationFromBackend();
    }

    function getDriverLocationFromBackend() {
    $.ajax({
        url: '{{ route("driver.pickup.location") }}',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const data = response.data;

                // ✅ Nếu API trả về toạ độ, lưu vào driverLocation
                if (data.latitude && data.longitude) {
                    driverLocation = {
                        lat: parseFloat(data.latitude),
                        lng: parseFloat(data.longitude)
                    };
                }

                // ✅ Thêm bưu cục từ API vào danh sách chọn
                const select = $('#hubSelect');
                select.empty(); // xoá các option cũ
                select.append(`<option value="">-- Chọn bưu cục --</option>`);

               const option = `
                    <option value="${data.id}"
                            data-lat="${data.latitude}"
                            data-lng="${data.longitude}"
                            data-address="${data.address}">
                        ${data.name}
                    </option>
                `;

                select.append(option);

                // ✅ Hiển thị card “Bưu cục gần nhất”
                const hubInfo = `
                    <div>
                        <strong>${data.name}</strong>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-map-marker-alt"></i> ${data.address}
                        </div>
                    </div>
                `;
                $('#nearestHubInfo').html(hubInfo);
                $('#nearestHubCard').slideDown();
                $('#useNearestHubBtn').show();

                // ✅ Chọn luôn option này
                select.val('1');
            } else {
                console.warn('Không có dữ liệu bưu cục từ backend.');
            }
        },
        error: function(xhr) {
            console.error('Không thể lấy thông tin bưu cục:', xhr.responseText);
        }
    });
}


    // ✅ LOẠI BỎ hoàn toàn hàm tryGPSLocation() - không cần GPS nữa

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function findNearestHub() {
        if (!driverLocation) {
            $('#nearestHubCard').hide();
            return;
        }

        let minDistance = Infinity;
        let nearest = null;

        $('#hubSelect option').each(function() {
            const $option = $(this);
            const lat = parseFloat($option.data('lat'));
            const lng = parseFloat($option.data('lng'));
            
            if (lat && lng) {
                const distance = calculateDistance(
                    driverLocation.lat, 
                    driverLocation.lng,
                    lat,
                    lng
                );
                
                if (distance < minDistance) {
                    minDistance = distance;
                    nearest = {
                        id: $option.val(),
                        name: $option.text(),
                        address: $option.data('address'),
                        distance: distance,
                        lat: lat,
                        lng: lng
                    };
                }
            }
        });

        if (nearest) {
            nearestHub = nearest;
            displayNearestHub(nearest);
            $('#hubSelect').val(nearest.id);
            updateDistanceInfo(nearest.distance);
        }
    }

    function displayNearestHub(hub) {
        const html = `
            <div>
                <strong>${hub.name}</strong>
                <div class="text-muted small mt-1">
                    <i class="fas fa-map-marker-alt"></i> ${hub.address || 'Không có địa chỉ'}
                </div>
                <div class="text-success small mt-1">
                    <i class="fas fa-route"></i> Cách bưu cục của bạn ${hub.distance.toFixed(1)} km
                </div>
            </div>
        `;
        $('#nearestHubInfo').html(html);
        $('#nearestHubCard').slideDown();
        $('#useNearestHubBtn').show();
    }

    function updateDistanceInfo(distance) {
        if (distance !== null) {
            $('#distanceInfo').html(`<i class="fas fa-route"></i> Khoảng cách: ${distance.toFixed(1)} km`);
        } else {
            $('#distanceInfo').html('');
        }
    }

    $('#hubSelect').change(function() {
        const $selected = $(this).find('option:selected');
        const lat = parseFloat($selected.data('lat'));
        const lng = parseFloat($selected.data('lng'));
        
        if (driverLocation && lat && lng) {
            const distance = calculateDistance(
                driverLocation.lat,
                driverLocation.lng,
                lat,
                lng
            );
            updateDistanceInfo(distance);
        } else {
            updateDistanceInfo(null);
        }
    });

    $('#findNearestBtn, #useNearestHubBtn').click(function() {
        if (nearestHub) {
            $('#hubSelect').val(nearestHub.id);
            updateDistanceInfo(nearestHub.distance);
            Toast.fire({
                icon: 'success',
                title: 'Đã chọn bưu cục gần nhất'
            });
        } else {
            // ✅ Nếu chưa có dữ liệu, thử lấy lại
            getDriverLocation();
            
            // Nếu vẫn không có sau 1s, thông báo chọn thủ công
            setTimeout(() => {
                if (!nearestHub) {
                    Toast.fire({
                        icon: 'info',
                        title: 'Vui lòng chọn bưu cục thủ công'
                    });
                }
            }, 1000);
        }
    });

    // Checkbox chọn tất cả
    $('#checkAll').change(function() {
        $('.order-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    $('.order-checkbox').change(function() {
        updateSelectedCount();
        $('#checkAll').prop('checked', $('.order-checkbox:checked').length === $('.order-checkbox').length);
    });

    $('#selectAllBtn').click(function() {
        $('.order-checkbox').prop('checked', true);
        $('#checkAll').prop('checked', true);
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.order-checkbox:checked').length;
        $('#selectedCount').text(count);
    }

    // Xem ảnh lấy hàng
    $('.btn-view-images').click(function() {
        const orderId = $(this).data('id');
        $('#imagesModal').modal('show');
        $('#imagesContainer').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

        $.ajax({
            url: `/driver/pickup/${orderId}/images`,
            method: 'GET',
            success: function(response) {
                if (response.images && response.images.length > 0) {
                    let html = '<div class="row g-3">';
                    response.images.forEach(img => {
                        html += `
                            <div class="col-md-6">
                                <img src="/storage/${img.image_path}" class="img-fluid rounded" alt="Ảnh lấy hàng">
                                ${img.note ? `<p class="text-muted small mt-1">${img.note}</p>` : ''}
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#imagesContainer').html(html);
                } else {
                    $('#imagesContainer').html('<p class="text-center text-muted">Không có ảnh</p>');
                }
            },
            error: function() {
                $('#imagesContainer').html('<p class="text-center text-danger">Không thể tải ảnh</p>');
            }
        });
    });

    // Submit form chuyển về bưu cục
    $('#transferForm').submit(function(e) {
        e.preventDefault();

        const selectedOrders = $('.order-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedOrders.length === 0) {
            Swal.fire('Chú ý', 'Vui lòng chọn ít nhất 1 đơn hàng.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Xác nhận',
            text: `Bạn có chắc muốn chuyển ${selectedOrders.length} đơn hàng về bưu cục không?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $('#orderIdsInput').val(JSON.stringify(selectedOrders));

            const btn = $('#btnTransfer');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');

            $.ajax({
                url: '/driver/pickup/transfer-to-hub',
                method: 'POST',
                data: $('#transferForm').serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Thành công', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire('Lỗi', response.message || 'Có lỗi xảy ra', 'error');
                        btn.prop('disabled', false)
                            .html('<i class="fas fa-truck"></i> Xác nhận chuyển');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', xhr.responseJSON?.message || 'Có lỗi xảy ra', 'error');
                    btn.prop('disabled', false)
                        .html('<i class="fas fa-truck"></i> Xác nhận chuyển');
                }
            });
        });
    });

    // Tự động tìm bưu cục khi mở modal
    $('#transferModal').on('shown.bs.modal', function() {
        if (!driverLocation) {
            getDriverLocation();
        } else if (!nearestHub) {
            findNearestHub();
        }
    });
});
</script>
@endsection