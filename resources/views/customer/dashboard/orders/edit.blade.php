@extends('customer.dashboard.layouts.app')
@section('title', 'Sửa đơn hàng #' . $order->id)

@section('content')
<link rel="stylesheet" href="{{ asset('assets2/css/customer/dashboard/orders/style.css') }}">

<style>
  .special-box {
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 15px;
    background: #fafafa;
  }
  .cost-breakdown {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
  }
  .cost-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #dee2e6;
  }
  .cost-item:last-child {
    border-bottom: none;
    font-weight: bold;
    font-size: 1.1rem;
    color: #dc3545;
  }
  .product-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
  }
  .product-item .remove-btn {
    cursor: pointer;
    color: #dc3545;
  }
  .image-preview-item {
    position: relative;
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
    margin-bottom: 15px;
  }
  .image-preview-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
  }
  .image-preview-item .remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
  }
  .readonly-info {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
  }
  input[id*="value"],
  input[id*="cod-amount"],
  input[class*="value"] {
    text-align: right;
    font-weight: 500;
  }
  .address-suggestions {
    position: absolute;
    z-index: 1000;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: none;
  }
  .address-suggestions .list-group-item {
    cursor: pointer;
    border-left: none;
    border-right: none;
  }
  .address-suggestions .list-group-item:hover {
    background: #f8f9fa;
  }
</style>

<div class="container-fluid py-4">
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1"><i class="bi bi-pencil-square"></i> Sửa đơn hàng #{{ $order->id }}</h4>
          <p class="text-muted mb-0">
            Trạng thái: <span class="badge bg-{{ $order->status_badge }}">{{ $order->status_label }}</span>
            @if($order->isPartOfGroup())
              | Nhóm đơn: <a href="#">#{{ $order->order_group_id }}</a>
            @endif
          </p>
        </div>
        <div>
          <a href="{{ route('customer.orderManagent.show', $order->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
          </a>
        </div>
      </div>
    </div>
  </div>

  <form id="orderEditForm" method="POST" action="{{ route('customer.orders.update', $order->id) }}" enctype="multipart/form-data">
    @csrf
    @method('POST')
    
    <input type="hidden" name="can_edit_sender" value="{{ !$order->pickup_driver_id && !$order->driver_id ? 'true' : 'false' }}">
    
    <div class="row">
      <div class="col-lg-5">
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0"><i class="bi bi-box-seam"></i> Thông tin người gửi</h6>
          </div>
          <div class="card-body">
            @if($order->pickup_driver_id || $order->driver_id)
              <div class="readonly-info">
                <div class="alert alert-warning mb-3">
                  <i class="bi bi-lock"></i> Thông tin người gửi không thể sửa vì đã có tài xế nhận đơn
                </div>
                <div><strong>Họ tên:</strong> {{ $order->sender_name }}</div>
                <div><strong>SĐT:</strong> {{ $order->sender_phone }}</div>
                <div><strong>Địa chỉ:</strong> {{ $order->sender_address }}</div>
                <div><strong>Thời gian lấy:</strong> {{ $order->pickup_time->format('H:i d/m/Y') }}</div>
              </div>
            @else
              <div class="mb-3">
                <label class="form-label">Tên người gửi <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="sender_name" value="{{ old('sender_name', $order->sender_name) }}" required>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="sender_phone" value="{{ old('sender_phone', $order->sender_phone) }}" required>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Địa chỉ lấy hàng <span class="text-danger">*</span></label>
                <textarea class="form-control" name="sender_address" rows="2" required>{{ old('sender_address', $order->sender_address) }}</textarea>
                <input type="hidden" name="sender_latitude" value="{{ $order->sender_latitude }}">
                <input type="hidden" name="sender_longitude" value="{{ $order->sender_longitude }}">
              </div>
              
              <div class="mb-3">
                <label class="form-label">Thời gian lấy hàng <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="pickup-time" value="{{ old('pickup_time', $order->pickup_time->format('Y-m-d\TH:i')) }}" required>
                <input type="hidden" id="pickup_time_formatted" name="pickup_time_formatted" value="{{ old('pickup_time_formatted', $order->pickup_time->format('Y-m-d H:i:s')) }}">
              </div>
              
              <div class="mb-3">
                <label class="form-label">Bưu cục lấy hàng</label>
                <select class="form-select" name="post_office_id" id="post-office-select">
                  <option value="">-- Chọn bưu cục --</option>
                </select>
                <small class="text-muted">Để trống nếu muốn tài xế đến tận nơi lấy hàng</small>
              </div>
            @endif
            
            <div class="mt-3">
              <label class="form-label">Ghi chú</label>
              <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú cho đơn hàng...">{{ old('note', $order->note) }}</textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0"><i class="bi bi-person"></i> Thông tin người nhận</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="recipient_name" value="{{ old('recipient_name', $order->recipient_name) }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="recipient_phone" value="{{ old('recipient_phone', $order->recipient_phone) }}" required>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
              <div class="row g-2">
                <div class="col-12">
                  <select class="form-select province-select" name="province_code" required>
                    <option value="">Tỉnh/Thành phố</option>
                  </select>
                </div>
                <div class="col-12">
                  <select class="form-select district-select" name="district_code" required>
                    <option value="">Quận/Huyện</option>
                  </select>
                </div>
                <div class="col-12">
                  <select class="form-select ward-select" name="ward_code" required>
                    <option value="">Phường/Xã</option>
                  </select>
                </div>
                <div class="col-12 position-relative">
                  <input type="text" class="form-control address-detail" name="address_detail" placeholder="Số nhà, tên đường..." value="{{ old('address_detail', $order->address_detail) }}" required autocomplete="off">
                  <div class="address-suggestions"></div>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Địa chỉ đầy đủ</label>
              <div class="p-2 bg-light rounded">
                <small class="full-address text-muted">{{ $order->recipient_full_address }}</small>
              </div>
              <input type="hidden" name="recipient_latitude" class="recipient-lat" value="{{ $order->recipient_latitude }}">
              <input type="hidden" name="recipient_longitude" class="recipient-lng" value="{{ $order->recipient_longitude }}">
              <input type="hidden" name="recipient_full_address" class="recipient-full-address" value="{{ $order->recipient_full_address }}">
              <div class="geocode-status mt-1"></div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Thời gian giao <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control delivery-time-input" value="{{ old('delivery_time', $order->delivery_time->format('Y-m-d\TH:i')) }}" required>
              <input type="hidden" class="delivery-time-formatted" name="delivery_time_formatted" value="{{ old('delivery_time_formatted', $order->delivery_time->format('Y-m-d H:i:s')) }}">
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0"><i class="bi bi-box"></i> Hàng hóa</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <div class="form-check form-check-inline">
                <input class="form-check-input item-type" type="radio" name="item_type" value="package" {{ $order->item_type === 'package' ? 'checked' : '' }}>
                <label class="form-check-label text-danger fw-bold">Bưu kiện</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input item-type" type="radio" name="item_type" value="document" {{ $order->item_type === 'document' ? 'checked' : '' }}>
                <label class="form-check-label text-danger fw-bold">Tài liệu</label>
              </div>
            </div>
            
            <div class="product-input-section form-package" style="{{ $order->item_type === 'package' ? '' : 'display:none;' }}">
              <h6 class="fw-bold mb-3">Thêm bưu kiện</h6>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tên hàng <span class="text-danger">*</span></label>
                  <input type="text" class="form-control product-name" placeholder="VD: Áo thun, Sách...">
                </div>
                <div class="col-6">
                  <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                  <input type="number" class="form-control product-quantity" value="1" min="1">
                </div>
                <div class="col-6">
                  <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control product-weight" value="1" min="1">
                </div>
                <div class="col-12">
                  <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control product-value" value="0">
                </div>
              </div>
              
              <div class="row mt-2">
                <div class="col-12 mb-2">
                  <label class="form-label">Kích thước (không bắt buộc)</label>
                </div>
                <div class="col-4">
                  <input type="number" class="form-control product-length" placeholder="Dài (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control product-width" placeholder="Rộng (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control product-height" placeholder="Cao (cm)" min="0">
                </div>
              </div>
              
              <div class="mt-3 special-box">
                <h6 class="fw-bold mb-2">Tính chất hàng hóa</h6>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="high-value" value="high_value">
                      <label class="form-check-label" for="high-value">Giá trị cao</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="oversized" value="oversized">
                      <label class="form-check-label" for="oversized">Quá khổ</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="fragile" value="fragile">
                      <label class="form-check-label" for="fragile">Dễ vỡ</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="liquid" value="liquid">
                      <label class="form-check-label" for="liquid">Chất lỏng</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="bulk" value="bulk">
                      <label class="form-check-label" for="bulk">Nguyên khối</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="battery" value="battery">
                      <label class="form-check-label" for="battery">Từ tính, Pin</label>
                    </div>
                  </div>
                </div>
              </div>
              
              <button type="button" class="btn btn-danger w-100 mt-3 add-product-btn">
                <i class="bi bi-plus-circle"></i> Thêm bưu kiện
              </button>
            </div>
            
            <div class="product-input-section form-document" style="{{ $order->item_type === 'document' ? '' : 'display:none;' }}">
              <h6 class="fw-bold mb-3">Thêm tài liệu</h6>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tên tài liệu <span class="text-danger">*</span></label>
                  <input type="text" class="form-control document-name" placeholder="VD: Hóa đơn...">
                </div>
                <div class="col-4">
                  <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                  <input type="number" class="form-control document-quantity" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control document-weight" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control document-value" value="0">
                </div>
              </div>
              
              <div class="row mt-2">
                <div class="col-12 mb-2">
                  <label class="form-label">Kích thước</label>
                </div>
                <div class="col-4">
                  <input type="number" class="form-control document-length" placeholder="Dài (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control document-width" placeholder="Rộng (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control document-height" placeholder="Cao (cm)" min="0">
                </div>
              </div>
              
              <div class="mt-3 special-box">
                <h6 class="fw-bold mb-2">Tính chất tài liệu</h6>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-check">
                      <input class="form-check-input doc-special-checkbox" type="checkbox" id="doc-high-value" value="high_value">
                      <label class="form-check-label" for="doc-high-value">Giá trị cao</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check">
                      <input class="form-check-input doc-special-checkbox" type="checkbox" id="doc-certificate" value="certificate">
                      <label class="form-check-label" for="doc-certificate">Hóa đơn, Giấy chứng nhận</label>
                    </div>
                  </div>
                </div>
              </div>
              
              <button type="button" class="btn btn-danger w-100 mt-3 add-document-btn">
                <i class="bi bi-plus-circle"></i> Thêm tài liệu
              </button>
            </div>
            
            <div class="products-list mb-3 mt-3"></div>
            <input type="hidden" name="products_json" class="products-json">
            
            <div class="mb-3">
              <label class="form-label fw-bold">Dịch vụ bổ sung</label>
              <div class="form-check">
                <input class="form-check-input service-checkbox" type="checkbox" id="priority" value="priority" name="services[]" {{ in_array('priority', $order->services ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="priority">Giao ưu tiên</label>
              </div>
              <div class="form-check">
                <input class="form-check-input service-checkbox" type="checkbox" id="insurance" value="insurance" name="services[]" {{ in_array('insurance', $order->services ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="insurance">Bảo hiểm</label>
              </div>
              <div class="form-check">
                <input class="form-check-input cod-checkbox" type="checkbox" id="cod" {{ in_array('cod', $order->services ?? []) || $order->cod_amount > 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="cod">Thu hộ COD</label>
              </div>
              
              <div class="cod-amount-container mt-2 {{ in_array('cod', $order->services ?? []) || $order->cod_amount > 0 ? '' : 'd-none' }}">
                <label class="form-label">Số tiền thu hộ (VNĐ)</label>
                <input type="text" class="form-control cod-amount-display" placeholder="Nhập số tiền" value="{{ $order->cod_amount > 0 ? number_format($order->cod_amount, 0, ',', '.') : '' }}">
                <input type="hidden" class="cod-amount-raw" name="cod_amount" value="{{ $order->cod_amount }}">
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label fw-bold">Người thanh toán cước phí</label>
              <div class="form-check">
                <input class="form-check-input payer-radio" type="radio" name="payer" id="payer-sender" value="sender" {{ $order->payer === 'sender' ? 'checked' : '' }}>
                <label class="form-check-label" for="payer-sender">Người gửi</label>
              </div>
              <div class="form-check">
                <input class="form-check-input payer-radio" type="radio" name="payer" id="payer-recipient" value="recipient" {{ $order->payer === 'recipient' ? 'checked' : '' }}>
                <label class="form-check-label" for="payer-recipient">Người nhận</label>
              </div>
            </div>
            
            <div class="cost-breakdown">
              <h6 class="fw-bold mb-2"><i class="bi bi-calculator"></i> Chi phí dự kiến</h6>
              <div class="cost-item">
                <span>Cước cơ bản:</span>
                <strong class="base-cost">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</strong>
              </div>
              <div class="cost-item">
                <span>Phụ phí:</span>
                <strong class="extra-cost">0 đ</strong>
              </div>
              <div class="cost-item cod-fee-row" style="{{ $order->cod_fee > 0 ? '' : 'display:none;' }}">
                <span>Phí COD:</span>
                <strong class="cod-fee">{{ number_format($order->cod_fee, 0, ',', '.') }} đ</strong>
              </div>
              <div class="cost-item">
                <span>Tổng cộng:</span>
                <strong class="total-cost">{{ number_format($order->shipping_fee + $order->cod_fee, 0, ',', '.') }} đ</strong>
              </div>
              <div class="cost-item" style="border-top: 2px solid #dee2e6; margin-top: 10px; padding-top: 10px;">
                <span>Người gửi trả:</span>
                <strong class="sender-pays text-success">{{ number_format($order->sender_total, 0, ',', '.') }} đ</strong>
              </div>
              <div class="cost-item">
                <span>Người nhận trả:</span>
                <strong class="recipient-pays text-warning">{{ number_format($order->recipient_total, 0, ',', '.') }} đ</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0"><i class="bi bi-images"></i> Hình ảnh đơn hàng</h6>
          </div>
          <div class="card-body">
            @if($order->images->count() > 0)
              <div class="mb-3">
                <label class="form-label fw-bold">Hình ảnh hiện tại</label>
                <div class="row existing-images-container">
                  @foreach($order->images as $image)
                    <div class="col-md-6 col-6 mb-2 existing-image-item" data-image-id="{{ $image->id }}">
                      <div class="image-preview-item">
                        <button type="button" class="remove-image" onclick="markImageForDeletion({{ $image->id }})">×</button>
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="Order Image">
                        <div class="p-2">
                          <small class="text-muted">{{ $image->note }}</small>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
            
            <input type="hidden" name="delete_images" class="delete-images-input" value="">
            
            <div class="mb-3">
              <label class="form-label fw-bold">Thêm hình ảnh mới (tối đa 5 ảnh)</label>
              <input type="file" class="form-control order-images" name="images[]" accept="image/*" multiple>
              <small class="text-muted">JPG, PNG, tối đa 5MB/ảnh</small>
              <div class="row mt-3 image-preview-container"></div>
            </div>
          </div>
        </div>

        <div class="text-end">
          <a href="{{ route('customer.orderManagent.show', $order->id) }}" class="btn btn-secondary me-2">Hủy</a>
          <button type="submit" class="btn btn-danger btn-lg" id="submitUpdate">
            <i class="bi bi-check-circle"></i> Cập nhật đơn hàng
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const GOONG_API_KEY = '{{ config("services.goong.api_key") }}';
let vietnamData = [];
let productsList = @json($productsData);
let selectedImages = [];
let imagesToDelete = [];
let geocodeTimeout = null;
let autocompleteTimeout = null;

$(document).ready(function() {
  console.log('🚀 Khởi tạo form sửa đơn...');
  console.log('📦 Products hiện tại:', productsList);
  
  loadProvinces().then(() => {
    console.log('✅ Đã load provinces');
    setupEventHandlers();
    setupCurrencyFormatting();
    setupToggleForms();
    renderProductsList();
    calculateCost();
    preselectAddress();
    loadPostOffices();
    formatExistingCurrencyValues();
  });
});

function loadProvinces() {
  return $.ajax({
    url: '/data/provinces.json',
    dataType: 'json',
    success: function(data) {
      vietnamData = data;
      populateProvinceSelect();
    },
    error: function() {
      console.error('❌ Không thể load provinces');
      alert('⚠️ Không thể tải dữ liệu địa chỉ. Vui lòng tải lại trang.');
    }
  });
}

function populateProvinceSelect() {
  let html = '<option value="">Tỉnh/Thành phố</option>';
  vietnamData.forEach(province => {
    html += `<option value="${province.code}">${province.name}</option>`;
  });
  $('.province-select').html(html);
}

// ============================================
// HÀM TÌM BƯU CỤC BẰNG OVERPASS API & NOMINATIM
// ============================================

function haversineDistance(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
    Math.sin(dLon/2) * Math.sin(dLon/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  return R * c;
}

async function loadPostOffices() {
  const senderLat = parseFloat('{{ $order->sender_latitude }}');
  const senderLng = parseFloat('{{ $order->sender_longitude }}');
  const currentOfficeId = '{{ $order->post_office_id }}';
  
  if (!senderLat || !senderLng || isNaN(senderLat) || isNaN(senderLng)) {
    console.warn('⚠️ Không có tọa độ người gửi');
    return;
  }
  
  console.log('🔍 Tìm bưu cục tại:', { senderLat, senderLng });
  
  $('#post-office-select').html('<option value="">Đang tải bưu cục...</option>');
  
  const radius = 10000; // 10km
  
  const overpassQuery = `
    [out:json][timeout:25];
    (
      node["amenity"="post_office"](around:${radius},${senderLat},${senderLng});
      node["office"="post_office"](around:${radius},${senderLat},${senderLng});
      way["amenity"="post_office"](around:${radius},${senderLat},${senderLng});
    );
    out body;
    >;
    out skel qt;
  `;
  
  const overpassUrl = `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(overpassQuery)}`;
  
  try {
    console.log('📡 Gọi Overpass API...');
    const response = await fetch(overpassUrl);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    
    console.log('📦 Kết quả Overpass:', data);
    
    if (!data.elements || data.elements.length === 0) {
      console.warn('⚠️ Không tìm thấy bưu cục, thử Nominatim...');
      await loadPostOfficesNominatim(senderLat, senderLng, currentOfficeId);
      return;
    }
    
    const nodes = data.elements.filter(item => 
      item.type === 'node' && item.lat && item.lon
    );
    
    let postOffices = nodes.map(item => ({
      name: item.tags?.name || item.tags?.['name:vi'] || 
            (item.tags?.['addr:street'] ? `Bưu cục ${item.tags['addr:street']}` : 'Bưu cục'),
      address: item.tags?.['addr:full'] || 
              item.tags?.['addr:street'] || 
              item.tags?.['addr:city'] || 
              'Không có địa chỉ chi tiết',
      lat: parseFloat(item.lat),
      lng: parseFloat(item.lon),
      operator: item.tags?.operator || 'Vietnam Post',
      id: item.id,
      type: 'node'
    })).filter(office => {
      return (
        office.name && 
        office.name !== 'Bưu cục' && 
        office.address && 
        office.address !== 'Không có địa chỉ chi tiết'
      );
    });
    
    console.log('📍 Danh sách bưu cục:', postOffices);
    
    if (postOffices.length === 0) {
      $('#post-office-select').html('<option value="">Không tìm thấy bưu cục gần đây</option>');
      return;
    }
    
    displayPostOffices(senderLat, senderLng, postOffices, currentOfficeId);
    
  } catch (err) {
    console.error('❌ Lỗi Overpass API:', err);
    await loadPostOfficesNominatim(senderLat, senderLng, currentOfficeId);
  }
}

async function loadPostOfficesNominatim(lat, lon, currentOfficeId) {
  console.log('📡 Gọi Nominatim API...');
  
  const keywords = ['bưu cục', 'post office', 'vnpost', 'vietnam post'];
  let allResults = [];
  
  for (const keyword of keywords) {
    try {
      const bboxSize = 0.05;
      const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(keyword + ' Hà Nội')}&format=json&limit=10&lat=${lat}&lon=${lon}&bounded=1&viewbox=${lon-bboxSize},${lat-bboxSize},${lon+bboxSize},${lat+bboxSize}`;
      
      const response = await fetch(url, {
        headers: {
          'User-Agent': 'PostOfficeApp/1.0',
          'Accept': 'application/json'
        }
      });
      
      if (response.ok) {
        const data = await response.json();
        allResults = allResults.concat(data.filter(item => 
          item.type === 'amenity' && 
          (item.class === 'post_office' || item.class === 'office')
        ));
      }
      
      await new Promise(resolve => setTimeout(resolve, 1000));
    } catch (err) {
      console.warn(`⚠️ Lỗi khi tìm "${keyword}":`, err);
    }
  }
  
  console.log('📦 Kết quả Nominatim:', allResults);
  
  if (allResults.length === 0) {
    $('#post-office-select').html('<option value="">Không tìm thấy bưu cục gần đây</option>');
    return;
  }
  
  const uniqueOffices = [];
  const seen = new Set();
  
  allResults.forEach(item => {
    const key = `${item.lat.toFixed(4)},${item.lon.toFixed(4)}`;
    if (!seen.has(key) && item.display_name) {
      seen.add(key);
      const addressParts = item.display_name.split(',');
      uniqueOffices.push({
        name: addressParts[0].trim() || 'Bưu cục',
        address: item.display_name || 'Không có địa chỉ chi tiết',
        lat: parseFloat(item.lat),
        lng: parseFloat(item.lon),
        operator: 'Vietnam Post',
        id: item.place_id,
        type: 'nominatim'
      });
    }
  });
  
  console.log('📍 Danh sách bưu cục sau khi lọc:', uniqueOffices);
  
  if (uniqueOffices.length > 0) {
    displayPostOffices(lat, lon, uniqueOffices, currentOfficeId);
  }
}

function displayPostOffices(userLat, userLon, postOffices, currentOfficeId) {
  if (postOffices.length === 0) {
    $('#post-office-select').html('<option value="">Không tìm thấy bưu cục</option>');
    return;
  }
  
  // Tính khoảng cách bằng Haversine
  const officesWithDistance = postOffices.map(office => {
    const distance = haversineDistance(userLat, userLon, office.lat, office.lng);
    return {
      ...office,
      distance: distance
    };
  });
  
  // Sắp xếp theo khoảng cách
  officesWithDistance.sort((a, b) => a.distance - b.distance);
  
  console.log('✅ Danh sách bưu cục đã sắp xếp:', officesWithDistance.slice(0, 5));
  
  // Hiển thị dropdown
  let html = '<option value="">-- Chọn bưu cục --</option>';
  
  officesWithDistance.slice(0, 15).forEach((office, index) => {
    const distanceKm = office.distance.toFixed(1);
    const selected = office.id == currentOfficeId ? 'selected' : '';
    
    html += `<option value="${office.id}" 
              data-lat="${office.lat}" 
              data-lng="${office.lng}" 
              data-distance="${office.distance}" 
              ${selected}>
      ${index + 1}. ${office.name} - ${office.address} (~${distanceKm}km)
    </option>`;
  });
  
  $('#post-office-select').html(html);
  console.log('✅ Đã hiển thị', Math.min(15, officesWithDistance.length), 'bưu cục');
}

function preselectAddress() {
  const provinceCode = '{{ $order->province_code }}';
  const districtCode = '{{ $order->district_code }}';
  const wardCode = '{{ $order->ward_code }}';
  
  console.log('📍 Preselecting:', { provinceCode, districtCode, wardCode });
  
  if (provinceCode) {
    $('.province-select').val(provinceCode).trigger('change');
    
    setTimeout(() => {
      if (districtCode) {
        $('.district-select').val(districtCode).trigger('change');
        
        setTimeout(() => {
          if (wardCode) {
            $('.ward-select').val(wardCode).trigger('change');
          }
        }, 300);
      }
    }, 300);
  }
}

function setupEventHandlers() {
  $('.province-select').on('change', handleProvinceChange);
  $('.district-select').on('change', handleDistrictChange);
  $('.ward-select, .address-detail').on('change keyup', updateFullAddress);
  
  $('.address-detail').on('input', function() {
    const query = $(this).val().trim();
    
    if (autocompleteTimeout) clearTimeout(autocompleteTimeout);
    
    if (query.length < 3) {
      $('.address-suggestions').hide().html('');
      return;
    }
    
    autocompleteTimeout = setTimeout(() => {
      goongAutocomplete(query);
    }, 500);
  });
  
  $('.add-product-btn').on('click', addProduct);
  $('.add-document-btn').on('click', addDocument);
  
  $('.item-type').on('change', function() {
    const itemType = $(this).val();
    if (itemType === 'package') {
      $('.form-package').show();
      $('.form-document').hide();
    } else {
      $('.form-package').hide();
      $('.form-document').show();
    }
  });
  
  $('.service-checkbox, .cod-checkbox').on('change', function() {
    if ($(this).hasClass('cod-checkbox')) {
      const isChecked = $(this).is(':checked');
      $('.cod-amount-container').toggleClass('d-none', !isChecked);
      if (!isChecked) {
        $('.cod-amount-display').val('');
        $('.cod-amount-raw').val('0');
      }
    }
    calculateCost();
  });
  
  $('.cod-amount-display').on('input', function() {
    const rawValue = getActualValue($(this).val());
    $('.cod-amount-raw').val(rawValue);
    
    if (window.cod_debounce) clearTimeout(window.cod_debounce);
    window.cod_debounce = setTimeout(calculateCost, 1000);
  });
  
  $('.payer-radio').on('change', calculateCost);
  $('.order-images').on('change', handleNewImageUpload);
  
  $('#pickup-time').on('change', function() {
    const value = $(this).val();
    $('#pickup_time_formatted').val(formatDatetimeForDatabase(value));
  });
  
  $('.delivery-time-input').on('change', function() {
    const value = $(this).val();
    $('.delivery-time-formatted').val(formatDatetimeForDatabase(value));
  });
  
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.address-detail, .address-suggestions').length) {
      $('.address-suggestions').hide();
    }
  });
}

function handleProvinceChange() {
  const provinceCode = String($('.province-select').val() || '');
  
  $('.district-select').html('<option value="">Quận/Huyện</option>').prop('disabled', true);
  $('.ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
  
  if (!provinceCode) {
    updateFullAddress();
    return;
  }
  
  const province = vietnamData.find(p => String(p.code) === provinceCode);
  if (province?.districts && Array.isArray(province.districts)) {
    let html = '<option value="">Quận/Huyện</option>';
    province.districts.forEach(district => {
      html += `<option value="${district.code}">${district.name}</option>`;
    });
    $('.district-select').html(html).prop('disabled', false);
  }
  
  updateFullAddress();
}

function handleDistrictChange() {
  const districtCode = String($('.district-select').val() || '');
  const provinceCode = String($('.province-select').val() || '');
  
  $('.ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
  
  if (!districtCode) {
    updateFullAddress();
    return;
  }
  
  const province = vietnamData.find(p => String(p.code) === provinceCode);
  const district = province?.districts?.find(d => String(d.code) === districtCode);
  
  if (district?.wards && Array.isArray(district.wards)) {
    let html = '<option value="">Phường/Xã</option>';
    district.wards.forEach(ward => {
      html += `<option value="${ward.code}">${ward.name}</option>`;
    });
    $('.ward-select').html(html).prop('disabled', false);
  }
  
  updateFullAddress();
}

function updateFullAddress() {
  const detail = $('.address-detail').val().trim();
  const wardText = $('.ward-select option:selected').text();
  const districtText = $('.district-select option:selected').text();
  const provinceText = $('.province-select option:selected').text();
  
  let addressParts = [];
  
  if (detail) addressParts.push(detail);
  if ($('.ward-select').val() && wardText !== 'Phường/Xã') addressParts.push(wardText);
  if ($('.district-select').val() && districtText !== 'Quận/Huyện') addressParts.push(districtText);
  if ($('.province-select').val() && provinceText !== 'Tỉnh/Thành phố') addressParts.push(provinceText);
  
  const fullAddress = addressParts.join(', ');
  $('.full-address').text(fullAddress || 'Chưa có địa chỉ đầy đủ');
  $('.recipient-full-address').val(fullAddress);
  
  if (geocodeTimeout) clearTimeout(geocodeTimeout);
  
  if ($('.province-select').val() && $('.district-select').val() && fullAddress) {
    $('.geocode-status').html('<small class="text-warning"><i class="bi bi-hourglass-split"></i> Đang tìm tọa độ...</small>');
    
    geocodeTimeout = setTimeout(() => {
      fetchCoordinates(fullAddress);
    }, 1000);
  } else {
    $('.recipient-lat').val('');
    $('.recipient-lng').val('');
    $('.geocode-status').html('<small class="text-muted">Chưa tìm tọa độ</small>');
  }
}

function goongAutocomplete(query) {
  const provinceText = $('.province-select option:selected').text();
  let input = query;
  if (provinceText && provinceText !== 'Tỉnh/Thành phố') {
    input += ', ' + provinceText;
  }
  
  $.ajax({
    url: 'https://rsapi.goong.io/Place/AutoComplete',
    data: {
      api_key: GOONG_API_KEY,
      input: input,
      limit: 5
    },
    success: function(data) {
      if (data?.predictions?.length > 0) {
        displayAutocompleteSuggestions(data.predictions);
      } else {
        $('.address-suggestions').hide().html('');
      }
    },
    error: function() {
      console.warn('⚠️ Goong Autocomplete API lỗi');
    }
  });
}

function displayAutocompleteSuggestions(predictions) {
  let html = '<div class="list-group">';
  predictions.forEach(pred => {
    html += `
      <button type="button" class="list-group-item list-group-item-action" 
              data-place-id="${pred.place_id}"
              data-description="${pred.description}">
        <i class="bi bi-geo-alt text-danger"></i> ${pred.description}
      </button>
    `;
  });
  html += '</div>';
  
  $('.address-suggestions').html(html).show();
  
  $('.address-suggestions .list-group-item').on('click', function(e) {
    e.preventDefault();
    const placeId = $(this).data('place-id');
    const description = $(this).data('description');
    
    goongPlaceDetail(placeId, description);
    $('.address-suggestions').hide();
  });
}

function goongPlaceDetail(placeId, description) {
  $.ajax({
    url: 'https://rsapi.goong.io/Place/Detail',
    data: {
      api_key: GOONG_API_KEY,
      place_id: placeId
    },
    success: function(data) {
      if (data?.result) {
        const result = data.result;
        const lat = result.geometry.location.lat;
        const lng = result.geometry.location.lng;
        
        $('.recipient-lat').val(lat);
        $('.recipient-lng').val(lng);
        $('.geocode-status').html('<small class="text-success"><i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ</small>');
        
        parseGoongAddress(result, description);
      }
    },
    error: function() {
      console.error('❌ Không thể lấy chi tiết địa điểm');
    }
  });
}

function parseGoongAddress(result, description) {
  $('.address-detail').val(description.split(',')[0].trim());
  
  const addressComponents = result.address_components || [];
  
  addressComponents.forEach(component => {
    const types = component.types || [];
    
    if (types.includes('administrative_area_level_1')) {
      const provinceName = component.long_name;
      $('.province-select option').each(function() {
        if ($(this).text().includes(provinceName)) {
          $('.province-select').val($(this).val()).trigger('change');
        }
      });
    }
    
    if (types.includes('administrative_area_level_2')) {
      setTimeout(() => {
        const districtName = component.long_name;
        $('.district-select option').each(function() {
          if ($(this).text().includes(districtName)) {
            $('.district-select').val($(this).val()).trigger('change');
          }
        });
      }, 500);
    }
    
    if (types.includes('sublocality_level_1') || types.includes('administrative_area_level_3')) {
      setTimeout(() => {
        const wardName = component.long_name;
        $('.ward-select option').each(function() {
          if ($(this).text().includes(wardName)) {
            $('.ward-select').val($(this).val()).trigger('change');
          }
        });
      }, 1000);
    }
  });
  
  setTimeout(updateFullAddress, 1500);
}

function fetchCoordinates(address) {
  $.ajax({
    url: 'https://rsapi.goong.io/geocode',
    data: {
      address: address,
      api_key: GOONG_API_KEY
    },
    timeout: 10000,
    success: function(data) {
      if (data?.results?.length > 0) {
        const result = data.results[0];
        const lat = result.geometry.location.lat;
        const lng = result.geometry.location.lng;
        
        $('.recipient-lat').val(lat);
        $('.recipient-lng').val(lng);
        $('.geocode-status').html('<small class="text-success"><i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ</small>');
      } else {
        $('.geocode-status').html('<small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Không tìm thấy tọa độ chính xác</small>');
      }
    },
    error: function() {
      $('.geocode-status').html('<small class="text-danger"><i class="bi bi-x-circle"></i> Lỗi kết nối Goong API</small>');
    }
  });
}

function addProduct() {
  const name = $('.product-name').val().trim();
  const quantity = parseInt($('.product-quantity').val()) || 1;
  const weight = parseFloat($('.product-weight').val()) || 0;
  const value = getCurrencyValue($('.product-value'));
  const length = parseFloat($('.product-length').val()) || 0;
  const width = parseFloat($('.product-width').val()) || 0;
  const height = parseFloat($('.product-height').val()) || 0;
  
  if (!name) {
    alert('⚠️ Vui lòng nhập tên hàng');
    return;
  }
  
  if (weight <= 0) {
    alert('⚠️ Khối lượng phải lớn hơn 0');
    return;
  }
  
  const specials = [];
  $('.special-checkbox:checked').each(function() {
    specials.push($(this).val());
  });
  
  const product = {
    type: 'package',
    name: name,
    quantity: quantity,
    weight: weight,
    value: value,
    length: length,
    width: width,
    height: height,
    specials: specials
  };
  
  productsList.push(product);
  console.log('✅ Đã thêm hàng:', name);
  
  renderProductsList();
  resetProductForm();
  calculateCost();
}

function addDocument() {
  const name = $('.document-name').val().trim();
  const quantity = parseInt($('.document-quantity').val()) || 1;
  const weight = parseFloat($('.document-weight').val()) || 0;
  const value = getCurrencyValue($('.document-value'));
  const length = parseFloat($('.document-length').val()) || 0;
  const width = parseFloat($('.document-width').val()) || 0;
  const height = parseFloat($('.document-height').val()) || 0;
  
  if (!name) {
    alert('⚠️ Vui lòng nhập tên tài liệu');
    return;
  }
  
  if (weight <= 0) {
    alert('⚠️ Khối lượng phải lớn hơn 0');
    return;
  }
  
  const specials = [];
  $('.doc-special-checkbox:checked').each(function() {
    specials.push($(this).val());
  });
  
  const product = {
    type: 'document',
    name: name,
    quantity: quantity,
    weight: weight,
    value: value,
    length: length,
    width: width,
    height: height,
    specials: specials
  };
  
  productsList.push(product);
  console.log('✅ Đã thêm tài liệu:', name);
  
  renderProductsList();
  resetDocumentForm();
  calculateCost();
}

function renderProductsList() {
  const container = $('.products-list');
  
  if (!productsList || productsList.length === 0) {
    container.html('<div class="alert alert-warning">Chưa có hàng hóa nào. Vui lòng thêm ít nhất 1 mặt hàng.</div>');
    $('.products-json').val('[]');
    return;
  }
  
  let html = '<h6 class="fw-bold mb-2">Danh sách hàng hóa:</h6>';
  productsList.forEach((item, idx) => {
    const icon = item.type === 'package' ? '📦' : '📄';
    const specialsText = item.specials && item.specials.length > 0 
      ? `<div class="text-muted small">Đặc tính: ${item.specials.join(', ')}</div>` 
      : '';
    
    html += `
      <div class="product-item">
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <strong>${icon} ${item.name}</strong>
            <div class="text-muted small">
              SL: ${item.quantity} | KL: ${item.weight}g | GT: ${item.value.toLocaleString('vi-VN')}đ
              ${item.length && item.width && item.height ? ` | KT: ${item.length}x${item.width}x${item.height}cm` : ''}
            </div>
            ${specialsText}
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger remove-btn" onclick="removeProduct(${idx})">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    `;
  });
  
  container.html(html);
  $('.products-json').val(JSON.stringify(productsList));
}

function removeProduct(idx) {
  if (confirm('Xóa hàng này?')) {
    productsList.splice(idx, 1);
    renderProductsList();
    calculateCost();
  }
}

function resetProductForm() {
  $('.product-name').val('');
  $('.product-quantity').val('1');
  $('.product-weight').val('');
  $('.product-value').val('0');
  $('.product-length').val('');
  $('.product-width').val('');
  $('.product-height').val('');
  $('.special-checkbox').prop('checked', false);
}

function resetDocumentForm() {
  $('.document-name').val('');
  $('.document-quantity').val('1');
  $('.document-weight').val('');
  $('.document-value').val('0');
  $('.document-length').val('');
  $('.document-width').val('');
  $('.document-height').val('');
  $('.doc-special-checkbox').prop('checked', false);
}

function markImageForDeletion(imageId) {
  if (confirm('Xóa ảnh này?')) {
    imagesToDelete.push(imageId);
    $(`.existing-image-item[data-image-id="${imageId}"]`).hide();
    
    let currentValue = $('.delete-images-input').val();
    let idsArray = currentValue ? currentValue.split(',').filter(Boolean) : [];
    idsArray.push(imageId);
    $('.delete-images-input').val(idsArray.join(','));
    
    console.log('🗑️ Đánh dấu xóa ảnh:', imageId);
  }
}

function handleNewImageUpload(e) {
  const files = Array.from(e.target.files);
  const MAX_IMAGES = 5;
  const MAX_FILE_SIZE = 5 * 1024 * 1024;
  
  const existingCount = $('.existing-images-container .existing-image-item:visible').length;
  const newCount = selectedImages.length;
  
  if (existingCount + newCount + files.length > MAX_IMAGES) {
    alert(`⚠️ Chỉ được tải tối đa ${MAX_IMAGES} ảnh`);
    $(e.target).val('');
    return;
  }
  
  for (let file of files) {
    if (!file.type.startsWith('image/')) {
      alert('⚠️ Chỉ chấp nhận file ảnh');
      continue;
    }
    
    if (file.size > MAX_FILE_SIZE) {
      alert(`⚠️ File "${file.name}" vượt quá 5MB`);
      continue;
    }
    
    selectedImages.push(file);
  }
  
  renderNewImagePreviews();
}

function renderNewImagePreviews() {
  const container = $('.image-preview-container');
  container.html('');
  
  if (!selectedImages || selectedImages.length === 0) return;
  
  selectedImages.forEach((file, index) => {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      const html = `
        <div class="col-md-6 col-6">
          <div class="image-preview-item">
            <button type="button" class="remove-image" onclick="removeNewImage(${index})">×</button>
            <img src="${e.target.result}" alt="Preview">
            <div class="p-2">
              <input type="text" 
                     class="form-control form-control-sm" 
                     name="image_notes[]" 
                     placeholder="Ghi chú ảnh">
            </div>
          </div>
        </div>
      `;
      container.append(html);
    };
    
    reader.readAsDataURL(file);
  });
}

function removeNewImage(index) {
  selectedImages.splice(index, 1);
  renderNewImagePreviews();
}

function calculateCost() {
  if (!productsList || productsList.length === 0) {
    resetCostDisplay();
    return;
  }
  
  let codAmount = 0;
  const codRawInput = $('.cod-amount-raw').val();
  if (codRawInput && codRawInput.trim()) {
    codAmount = parseFloat(codRawInput);
  }
  
  const services = [];
  $('.service-checkbox:checked').each(function() {
    services.push($(this).val());
  });
  
  if ($('.cod-checkbox').is(':checked')) {
    if (!services.includes('cod')) {
      services.push('cod');
    }
  }
  
  const payer = $('.payer-radio:checked').val() || 'sender';
  const itemType = $('.item-type:checked').val() || 'package';
  
  const data = {
    products_json: JSON.stringify(productsList),
    services: services,
    cod_amount: codAmount,
    payer: payer,
    item_type: itemType,
    _token: $('meta[name="csrf-token"]').attr('content')
  };
  
  console.log('📤 Calculating cost:', data);
  
  $.ajax({
    url: '{{ route("customer.orders.calculate") }}',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(res) {
      console.log('📥 Cost response:', res);
      
      if (res && res.success === true) {
        $('.base-cost').text((res.base_cost || 0).toLocaleString('vi-VN') + ' đ');
        $('.extra-cost').text((res.extra_cost || 0).toLocaleString('vi-VN') + ' đ');
        
        if (res.cod_fee && res.cod_fee > 0) {
          $('.cod-fee').text(res.cod_fee.toLocaleString('vi-VN') + ' đ');
          $('.cod-fee-row').show();
        } else {
          $('.cod-fee').text('0 đ');
          $('.cod-fee-row').hide();
        }
        
        $('.total-cost').text((res.total || 0).toLocaleString('vi-VN') + ' đ');
        $('.sender-pays').text((res.sender_pays || 0).toLocaleString('vi-VN') + ' đ');
        $('.recipient-pays').text((res.recipient_pays || 0).toLocaleString('vi-VN') + ' đ');
      }
    },
    error: function(xhr) {
      console.error('❌ Calculate error:', xhr.responseText);
    }
  });
}

function resetCostDisplay() {
  $('.base-cost').text('0 đ');
  $('.extra-cost').text('0 đ');
  $('.total-cost').text('0 đ');
  $('.sender-pays').text('0 đ');
  $('.recipient-pays').text('0 đ');
  $('.cod-fee-row').hide();
}

function formatCurrencyDisplay(value) {
  if (!value || value === '') return '';
  const numStr = String(value).replace(/\D/g, '');
  if (!numStr) return '';
  return parseInt(numStr).toLocaleString('vi-VN');
}

function getActualValue(formatted) {
  if (!formatted || formatted === '') return 0;
  return parseInt(String(formatted).replace(/\D/g, '')) || 0;
}

function getCurrencyValue(element) {
  const $el = typeof element === 'string' ? $(element) : element;
  const actualValue = $el.data('actual-value');
  if (typeof actualValue === 'number') {
    return actualValue;
  }
  return getActualValue($el.val());
}

function setupCurrencyFormatting() {
  const currencySelectors = [
    'input[id*="value"]',
    'input[id*="cod-amount"]',
    'input[class*="value"]'
  ];
  
  const selector = currencySelectors.join(', ');
  
  $(document).on('input', selector, function(e) {
    const $input = $(this);
    const input = this;
    
    const cursorPosition = input.selectionStart;
    const oldValue = $input.val();
    const dotsBeforeCursor = (oldValue.substring(0, cursorPosition).match(/\./g) || []).length;
    
    const rawValue = oldValue.replace(/\D/g, '');
    const formatted = formatCurrencyDisplay(rawValue);
    const actual = getActualValue(formatted);
    
    $input.val(formatted);
    $input.data('actual-value', actual);
    
    if (formatted !== oldValue) {
      const newDotsBeforeCursor = (formatted.substring(0, cursorPosition).match(/\./g) || []).length;
      const dotDifference = newDotsBeforeCursor - dotsBeforeCursor;
      let newPosition = cursorPosition + dotDifference;
      
      newPosition = Math.min(newPosition, formatted.length);
      newPosition = Math.max(0, newPosition);
      
      if (input.setSelectionRange) {
        setTimeout(() => {
          input.setSelectionRange(newPosition, newPosition);
        }, 0);
      }
    }
  });
  
  $(document).on('focus', selector, function() {
    const $input = $(this);
    const value = $input.val();
    if (value && value !== '') {
      const formatted = formatCurrencyDisplay(value);
      const actual = getActualValue(formatted);
      $input.val(formatted);
      $input.data('actual-value', actual);
    }
  });
  
  $(document).on('paste', selector, function(e) {
    e.preventDefault();
    const pastedText = (e.originalEvent || e).clipboardData.getData('text/plain');
    const formatted = formatCurrencyDisplay(pastedText);
    const actual = getActualValue(formatted);
    $(this).val(formatted);
    $(this).data('actual-value', actual);
  });
}

function formatExistingCurrencyValues() {
  const codDisplay = $('.cod-amount-display');
  if (codDisplay.length && codDisplay.val()) {
    const formatted = formatCurrencyDisplay(codDisplay.val());
    codDisplay.val(formatted);
    codDisplay.data('actual-value', getActualValue(formatted));
  }
  
  $('input[id*="value"], input[class*="value"]').each(function() {
    const $input = $(this);
    if ($input.val()) {
      const formatted = formatCurrencyDisplay($input.val());
      $input.val(formatted);
      $input.data('actual-value', getActualValue(formatted));
    }
  });
}

function setupToggleForms() {
  const itemType = $('.item-type:checked').val() || 'package';
  if (itemType === 'package') {
    $('.form-package').show();
    $('.form-document').hide();
  } else {
    $('.form-package').hide();
    $('.form-document').show();
  }
}

function formatDatetimeForDatabase(datetimeLocalValue) {
  if (!datetimeLocalValue) return null;
  const [date, time] = datetimeLocalValue.split('T');
  return `${date} ${time}:00`;
}

$('#orderEditForm').on('submit', function(e) {
  e.preventDefault();
  
  console.log('📤 Submitting edit form...');
  
  if (!validateForm()) {
    return false;
  }
  
  $('.products-json').val(JSON.stringify(productsList));
  
  const canEditSender = $('input[name="can_edit_sender"]').val();
  if (canEditSender === 'true') {
    const pickupValue = $('#pickup-time').val();
    $('#pickup_time_formatted').val(formatDatetimeForDatabase(pickupValue));
  }
  
  const deliveryValue = $('.delivery-time-input').val();
  $('.delivery-time-formatted').val(formatDatetimeForDatabase(deliveryValue));
  
  $('#submitUpdate').prop('disabled', true)
    .html('<span class="spinner-border spinner-border-sm me-2"></span>Đang cập nhật...');
  
  this.submit();
});

function validateForm() {
  if (!$('input[name="recipient_name"]').val().trim()) {
    alert('⚠️ Vui lòng nhập tên người nhận');
    return false;
  }
  
  if (!$('input[name="recipient_phone"]').val().trim()) {
    alert('⚠️ Vui lòng nhập số điện thoại người nhận');
    return false;
  }
  
  const phonePattern = /^(0|\+84)[0-9]{9,10}$/;
  if (!phonePattern.test($('input[name="recipient_phone"]').val().trim())) {
    alert('⚠️ Số điện thoại không hợp lệ');
    return false;
  }
  
  if (!$('.province-select').val() || !$('.district-select').val() || !$('.ward-select').val()) {
    alert('⚠️ Vui lòng chọn địa chỉ đầy đủ');
    return false;
  }
  
  if (!$('.address-detail').val().trim()) {
    alert('⚠️ Vui lòng nhập số nhà, tên đường');
    return false;
  }
  
  if (!productsList || productsList.length === 0) {
    alert('⚠️ Vui lòng thêm ít nhất 1 hàng hóa');
    return false;
  }
  
  if (!$('.delivery-time-input').val()) {
    alert('⚠️ Vui lòng chọn thời gian giao hàng');
    return false;
  }
  
  const canEditSender = $('input[name="can_edit_sender"]').val();
  if (canEditSender === 'true') {
    if (!$('input[name="sender_name"]').val().trim()) {
      alert('⚠️ Vui lòng nhập tên người gửi');
      return false;
    }
    
    if (!$('input[name="sender_phone"]').val().trim()) {
      alert('⚠️ Vui lòng nhập số điện thoại người gửi');
      return false;
    }
    
    if (!phonePattern.test($('input[name="sender_phone"]').val().trim())) {
      alert('⚠️ Số điện thoại người gửi không hợp lệ');
      return false;
    }
    
    if (!$('input[name="sender_address"]').val().trim()) {
      alert('⚠️ Vui lòng nhập địa chỉ người gửi');
      return false;
    }
    
    if (!$('#pickup-time').val()) {
      alert('⚠️ Vui lòng chọn thời gian lấy hàng');
      return false;
    }
  }
  
  return true;
}

window.removeProduct = removeProduct;
window.markImageForDeletion = markImageForDeletion;
window.removeNewImage = removeNewImage;

console.log('✅ Edit order script loaded successfully');
</script>
@endsection