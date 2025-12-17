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
    position: relative;
  }
  .product-item .edit-product-btn {
    position: absolute;
    top: 10px;
    right: 45px;
  }
  .product-item .remove-product-btn {
    position: absolute;
    top: 10px;
    right: 10px;
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
  input[class*="value"],
  input[id*="cod-amount"],
  input[class*="cod-amount"] {
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
  .editing-product-form {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
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
    @method('PUT')
    
    <input type="hidden" name="can_edit_sender" value="{{ !$order->pickup_driver_id && !$order->driver_id ? 'true' : 'false' }}">
    <input type="hidden" id="delivery_time_formatted" name="delivery_time_formatted">
    <input type="hidden" id="pickup_time_formatted" name="pickup_time_formatted">
    
    <div class="row">
      <!-- CỘT TRÁI -->
      <div class="col-lg-5">
        <!-- NGƯỜI GỬI -->
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0"><i class="bi bi-box-seam"></i> Thông tin người gửi</h6>
          </div>
          <div class="card-body">
            <!-- ✅ HIỂN THỊ THÔNG TIN READONLY -->
            <div class="readonly-info">
              @if($order->pickup_driver_id || $order->driver_id)
                <div class="alert alert-warning mb-3">
                  <i class="bi bi-lock"></i> Thông tin người gửi không thể sửa (đã có tài xế nhận)
                </div>
              @endif
              
              <div class="mb-2"><strong>Họ tên:</strong> {{ $order->sender_name }}</div>
              <div class="mb-2"><strong>SĐT:</strong> {{ $order->sender_phone }}</div>
              <div class="mb-2"><strong>Địa chỉ:</strong> {{ $order->sender_address }}</div>
              <div class="mb-2"><strong>Thời gian lấy:</strong> {{ $order->pickup_time->format('H:i d/m/Y') }}</div>
              
              <!-- Hidden inputs -->
              <input type="hidden" name="sender_name" value="{{ $order->sender_name }}">
              <input type="hidden" name="sender_phone" value="{{ $order->sender_phone }}">
              <input type="hidden" name="sender_address" value="{{ $order->sender_address }}">
              <input type="hidden" name="sender_latitude" class="sender-latitude" value="{{ $order->sender_latitude }}">
              <input type="hidden" name="sender_longitude" class="sender-longitude" value="{{ $order->sender_longitude }}">
              <input type="hidden" class="pickup-time" value="{{ $order->pickup_time->format('Y-m-d\TH:i') }}">
            </div>

            <!-- ✅ CHO PHÉP ĐỔI BƯU CỤC -->
           <div class="mt-3">
              <label class="form-label fw-bold">Bưu cục nhận hàng</label>
              <div class="input-group">
                <select class="form-select" id="postOfficeSelect" name="post_office_id">
                  <option value="">-- Đang tải bưu cục... --</option>
                </select>
                <button class="btn btn-outline-secondary" type="button" id="refreshPostOfficeBtn" 
                        title="Tải lại danh sách bưu cục">
                  <i class="bi bi-arrow-clockwise"></i> Làm mới
                </button>
              </div>
              <small class="text-muted">Thay đổi bưu cục sẽ ảnh hưởng đến phí vận chuyển</small>
            </div>
          </div>
        </div>
      </div>

      <!-- CỘT PHẢI -->
      <div class="col-lg-7">
        <!-- NGƯỜI NHẬN -->
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0"><i class="bi bi-person"></i> Thông tin người nhận</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                  <input type="text" class="form-control recipient-name" name="recipient_name" 
                         value="{{ old('recipient_name', $order->recipient_name) }}" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                  <input type="text" class="form-control recipient-phone" name="recipient_phone" 
                         value="{{ old('recipient_phone', $order->recipient_phone) }}" required>
                </div>
              </div>
            </div>
            
            <!-- ✅ ĐỊA CHỈ: HÀ NỘI MẶC ĐỊNH, BẮT ĐẦU TỪ QUẬN/HUYỆN -->
            <div class="mb-3">
              <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
              <div class="row g-2">
                <!-- TỈNH/THÀNH PHỐ - READONLY HÀ NỘI -->
                <div class="col-12">
                  <label class="form-label">
                    Tỉnh/Thành phố 
                    <span class="badge bg-success text-white ms-2" style="font-size: 11px;">
                      <i class="bi bi-geo-alt-fill"></i> Hà Nội
                    </span>
                  </label>
                  <select class="form-select province-select" required disabled
                          style="background-color: #f5f5f5; cursor: not-allowed; color: #6c757d;">
                    <option value="">Đang tải Hà Nội...</option>
                  </select>
                  <input type="hidden" name="province_code" class="province-hidden">
                </div>
                
                <!-- QUẬN/HUYỆN -->
                <div class="col-12">
                  <select class="form-select district-select" name="district_code" required>
                    <option value="">Quận/Huyện</option>
                  </select>
                </div>
                
                <!-- PHƯỜNG/XÃ -->
                <div class="col-12">
                  <select class="form-select ward-select" name="ward_code" required>
                    <option value="">Phường/Xã</option>
                  </select>
                </div>
                
                <!-- CHI TIẾT -->
                <div class="col-12 position-relative">
                  <input type="text" class="form-control address-detail" name="address_detail" 
                         value="{{ old('address_detail', $order->address_detail) }}" 
                         placeholder="Số nhà, tên đường..." required autocomplete="off">
                  <div class="address-suggestions list-group"></div>
                </div>
              </div>
            </div>
            
            <!-- ĐỊA CHỈ ĐẦY ĐỦ -->
            <div class="mb-3">
              <label class="form-label">Địa chỉ đầy đủ</label>
              <div class="p-2 bg-light rounded">
                <small class="full-address text-muted">{{ $order->recipient_full_address ?? 'Chưa có địa chỉ đầy đủ' }}</small>
              </div>
              <input type="hidden" name="recipient_latitude" class="recipient-lat" value="{{ $order->recipient_latitude }}">
              <input type="hidden" name="recipient_longitude" class="recipient-lng" value="{{ $order->recipient_longitude }}">
              <input type="hidden" name="recipient_full_address" class="recipient-full-address" value="{{ $order->recipient_full_address }}">
              <div class="geocode-status mt-1"><small class="text-success">Đã có tọa độ</small></div>
            </div>
            
            <!-- THỜI GIAN GIAO -->
            <div class="mb-3">
              <label class="form-label">Thời gian giao <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control delivery-time-input" 
                     value="{{ old('delivery_time', $order->delivery_time->format('Y-m-d\TH:i')) }}" required>
            </div>
          </div>
        </div>

        <!-- HÀNG HÓA -->
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6 class="mb-0"><i class="bi bi-box"></i> Hàng hóa</h6>
          </div>
          <div class="card-body">
            <!-- LOẠI HÀNG -->
            <div class="mb-3">
              <div class="form-check form-check-inline">
                <input class="form-check-input item-type" type="radio" name="item_type" value="package" 
                       {{ $order->item_type === 'package' ? 'checked' : '' }}>
                <label class="form-check-label text-danger fw-bold">Bưu kiện</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input item-type" type="radio" name="item_type" value="document" 
                       {{ $order->item_type === 'document' ? 'checked' : '' }}>
                <label class="form-check-label text-danger fw-bold">Tài liệu</label>
              </div>
            </div>

            <!-- ✅ FORM ĐANG SỬA (Hiện khi click Edit) -->
            <div class="editing-product-form" id="editingProductForm" style="display:none;">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-warning"><i class="bi bi-pencil-square"></i> Đang sửa sản phẩm</h6>
                <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEditProduct()">
                  <i class="bi bi-x"></i> Hủy
                </button>
              </div>
              
              <input type="hidden" id="editingProductIndex">
              
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tên hàng <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit-product-name">
                </div>
                <div class="col-4">
                  <label class="form-label">Số lượng</label>
                  <input type="number" class="form-control" id="edit-product-quantity" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Khối lượng (g)</label>
                  <input type="number" class="form-control" id="edit-product-weight" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Giá trị (VNĐ)</label>
                  <input type="text" class="form-control" id="edit-product-value">
                </div>
              </div>
              
              <div class="row mt-2">
                <div class="col-4">
                  <input type="number" class="form-control" id="edit-product-length" placeholder="Dài (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="edit-product-width" placeholder="Rộng (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="edit-product-height" placeholder="Cao (cm)" min="0">
                </div>
              </div>
              
              <div class="mt-3">
                <h6 class="fw-bold mb-2">Tính chất hàng hóa</h6>
                <div class="row">
                  <div class="col-6">

                    <div class="form-check">
                      <input class="form-check-input edit-special-checkbox" type="checkbox" id="edit-chk-high-value" value="high_value"  data-product-index="0">
                      <label class="form-check-label" for="edit-chk-high-value">Giá trị cao</label>
                    </div>

                    <div class="form-check">
                    <input class="form-check-input edit-special-checkbox" type="checkbox" id="edit-chk-oversized" value="oversized">
                    <label class="form-check-label" for="edit-chk-oversized">Quá khổ</label>
                    </div>

                    <div class="form-check">
                     <input class="form-check-input edit-special-checkbox" type="checkbox" id="edit-chk-fragile" value="fragile">
<label class="form-check-label" for="edit-chk-fragile">Dễ vỡ</label>
                    </div>
                  </div>

                  <div class="col-6">
                     <div class="form-check">
                    <input class="form-check-input edit-special-checkbox" type="checkbox" id="edit-chk-liquid" value="liquid">
<label class="form-check-label" for="edit-chk-liquid">Chất lỏng</label>
                    </div>
                    <div class="form-check">
                   <input class="form-check-input edit-special-checkbox" type="checkbox" id="edit-chk-bulk" value="bulk">
<label class="form-check-label" for="edit-chk-bulk">Nguyên khối</label>
                    </div>
                    <div class="form-check">
                    <input class="form-check-input edit-special-checkbox" type="checkbox" id="edit-chk-battery" value="battery">
<label class="form-check-label" for="edit-chk-battery">Từ tính, Pin</label>
                    </div>
                  </div>
                </div>
              </div>
              
              <button type="button" class="btn btn-success w-100 mt-3" onclick="saveEditProduct()">
                <i class="bi bi-check-circle"></i> Lưu thay đổi
              </button>
            </div>
            
            <!-- FORM BƯU KIỆN (Thêm mới) -->
            <div class="product-input-section form-package" style="{{ $order->item_type === 'package' ? '' : 'display:none;' }}">
              <h6 class="fw-bold mb-3">Thêm bưu kiện mới</h6>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tên hàng <span class="text-danger">*</span></label>
                  <input type="text" class="form-control product-name" placeholder="VD: Áo thun, Sách...">
                </div>
                <div class="col-4">
                  <label class="form-label">Số lượng</label>
                  <input type="number" class="form-control product-quantity" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Khối lượng (g)</label>
                  <input type="number" class="form-control product-weight" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Giá trị (VNĐ)</label>
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
                  <div class="col-6">
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="high-value" value="high_value">
                      <label class="form-check-label" for="high-value">Giá trị cao</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="oversized" value="oversized">
                      <label class="form-check-label" for="oversized">Quá khổ</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="fragile" value="fragile">
                      <label class="form-check-label" for="fragile">Dễ vỡ</label>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="form-check">
                      <input class="form-check-input special-checkbox" type="checkbox" id="liquid" value="liquid">
                      <label class="form-check-label" for="liquid">Chất lỏng</label>
                    </div>
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
            
            <!-- FORM TÀI LIỆU (Thêm mới) -->
            <div class="product-input-section form-document" style="{{ $order->item_type === 'document' ? '' : 'display:none;' }}">
              <h6 class="fw-bold mb-3">Thêm tài liệu mới</h6>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tên tài liệu</label>
                  <input type="text" class="form-control document-name" placeholder="VD: Hóa đơn...">
                </div>
                <div class="col-4">
                  <label class="form-label">Số lượng</label>
                  <input type="number" class="form-control document-quantity" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Khối lượng (g)</label>
                  <input type="number" class="form-control document-weight" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Giá trị (VNĐ)</label>
                  <input type="text" class="form-control document-value" value="0">
                </div>
              </div>
              
              <div class="row mt-2">
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
                <div class="form-check">
                  <input class="form-check-input doc-special-checkbox" type="checkbox" id="doc-high-value" value="high_value">
                  <label class="form-check-label" for="doc-high-value">Giá trị cao</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input doc-special-checkbox" type="checkbox" id="doc-certificate" value="certificate">
                  <label class="form-check-label" for="doc-certificate">Hóa đơn, Giấy chứng nhận</label>
                </div>
              </div>
              
              <button type="button" class="btn btn-danger w-100 mt-3 add-document-btn">
                <i class="bi bi-plus-circle"></i> Thêm tài liệu
              </button>
            </div>
            
            <!-- DANH SÁCH SẢN PHẨM -->
            <div class="products-list mb-3 mt-3"></div>
            <input type="hidden" name="products_json" class="products-json">
            
            <!-- DỊCH VỤ -->
            <div class="mb-3">
              <label class="form-label fw-bold">Dịch vụ bổ sung</label>
              <div class="form-check">
                <input class="form-check-input service-checkbox" type="checkbox" id="priority" value="priority" 
                       {{ in_array('priority', $order->services ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="priority">Giao ưu tiên</label>
              </div>
              <div class="form-check">
                <input class="form-check-input service-checkbox" type="checkbox" id="insurance" value="insurance" 
                       {{ in_array('insurance', $order->services ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="insurance">Bảo hiểm</label>
              </div>
              <div class="form-check">
                <input class="form-check-input cod-checkbox" type="checkbox" id="cod" 
                       {{ in_array('cod', $order->services ?? []) || $order->cod_amount > 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="cod">Thu hộ COD</label>
              </div>
              
              <div class="cod-amount-container mt-2 {{ in_array('cod', $order->services ?? []) || $order->cod_amount > 0 ? '' : 'd-none' }}">
                <label class="form-label">Số tiền thu hộ (VNĐ)</label>
                <input type="text" class="form-control cod-amount-display" placeholder="Nhập số tiền" 
                       value="{{ $order->cod_amount > 0 ? number_format($order->cod_amount, 0, ',', '.') : '' }}">
                <input type="hidden" class="cod-amount-raw" name="cod_amount" value="{{ $order->cod_amount }}">
              </div>
            </div>
            
            <!-- NGƯỜI THANH TOÁN -->
            <div class="mb-3">
              <label class="form-label fw-bold">Người thanh toán cước phí</label>
              <div class="form-check">
                <input class="form-check-input payer-radio" type="radio" name="payer" id="payer-sender" value="sender" 
                       {{ $order->payer === 'sender' ? 'checked' : '' }}>
                <label class="form-check-label" for="payer-sender">Người gửi</label>
              </div>
              <div class="form-check">
                <input class="form-check-input payer-radio" type="radio" name="payer" id="payer-recipient" value="recipient" 
                       {{ $order->payer === 'recipient' ? 'checked' : '' }}>
                <label class="form-check-label" for="payer-recipient">Người nhận</label>
              </div>
            </div>
            
            <!-- CHI PHÍ -->
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
              <div class="cost-item distance-fee-row" style="{{ $order->distance_fee > 0 ? '' : 'display:none;' }}">
                <span>Phí khoảng cách:</span>
                <strong class="distance-fee text-info">{{ number_format($order->distance_fee ?? 0, 0, ',', '.') }} đ</strong>
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

        <!-- HÌNH ẢNH -->
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

        <!-- NÚT SUBMIT -->
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
<script src="{{ asset('assets2/js/customer/dashboard/orders/fetchNearbyPostOffices.js') }}"></script>
<script>
const GOONG_API_KEY = '{{ config("services.goong.api_key") }}';
let vietnamData = [];
let productsList = @json($productsData ?? []);
let selectedImages = [];
let imagesToDelete = [];
let geocodeTimeout = null;
let autocompleteTimeout = null;
let editingProductIndex = null; // Track đang sửa product nào

productsList = productsList.map(p => {
    return {
        ...p,
        specials: Array.isArray(p.specials)
            ? p.specials
            : (typeof p.specials === 'string' ? JSON.parse(p.specials) : [])
    };
});


$(document).ready(function() {
  console.log('🚀 Khởi tạo form sửa đơn...');
  console.log('📦 Products hiện tại:', productsList);
  
    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  loadProvinces().then(() => {
    console.log('✅ Đã load provinces');
    setupEventHandlers();
    setupEditFormEventHandlers();
    setupCurrencyFormatting();
    setupToggleForms();
    renderProductsList();
    preselectAddress();
    loadPostOffices(); // ✅ Load bưu cục
    calculateCost();
    formatExistingCurrencyValues();
  });
});

// ============ LOAD PROVINCES (CHỈ HÀ NỘI) ============
function loadProvinces() {
  return $.ajax({
    url: '/data/provinces.json',
    dataType: 'json',
    success: function(data) {
      vietnamData = filterHanoiOnly(data);
      populateProvinceSelect();
    },
    error: function() {
      $.ajax({
        url: "https://provinces.open-api.vn/api/?depth=3",
        dataType: 'json',
        success: function(data) {
          vietnamData = filterHanoiOnly(data);
          populateProvinceSelect();
        }
      });
    }
  });
}

function filterHanoiOnly(data) {
  const hanoi = data.find(p => 
    p.name.includes('Hà Nội') || 
    p.name.includes('Ha Noi') ||
    p.code === '01' ||
    p.code === 1
  );
  return hanoi ? [hanoi] : [];
}

function populateProvinceSelect() {
  if (vietnamData.length === 0) return;
  
  const hanoi = vietnamData[0];
  const hanoiCode = String(hanoi.code);
  
  $('.province-select').html(`<option value="${hanoiCode}">${hanoi.name}</option>`);
  $('.province-select').val(hanoiCode);
  $('.province-hidden').val(hanoiCode);
}

function preselectAddress() {
  const provinceCode = '{{ $order->province_code }}';
  const districtCode = '{{ $order->district_code }}';
  const wardCode = '{{ $order->ward_code }}';
  
  if (provinceCode && vietnamData.length > 0) {
    const hanoi = vietnamData[0];
    
    // Populate districts
    if (hanoi.districts && Array.isArray(hanoi.districts)) {
      let html = '<option value="">Quận/Huyện</option>';
      hanoi.districts.forEach(district => {
        html += `<option value="${district.code}">${district.name}</option>`;
      });
      $('.district-select').html(html).prop('disabled', false);
      
      if (districtCode) {
        $('.district-select').val(districtCode);
        
        // Populate wards
        const district = hanoi.districts.find(d => String(d.code) === String(districtCode));
        if (district && district.wards) {
          let wardHtml = '<option value="">Phường/Xã</option>';
          district.wards.forEach(ward => {
            wardHtml += `<option value="${ward.code}">${ward.name}</option>`;
          });
          $('.ward-select').html(wardHtml).prop('disabled', false);
          
          if (wardCode) {
            $('.ward-select').val(wardCode);
          }
        }
      }
    }
  }
  
  updateFullAddress();
}

// ============ LOAD BƯU CỤC ============
function loadPostOffices() {
  const senderLat = parseFloat($('.sender-latitude').val());
  const senderLng = parseFloat($('.sender-longitude').val());
  const savedPostOfficeId = '{{ $order->post_office_id }}';
  
  console.log('📍 Loading post offices:', {senderLat, senderLng, savedPostOfficeId});
  
  if (!senderLat || !senderLng || isNaN(senderLat) || isNaN(senderLng)) {
    $('#postOfficeSelect').html('<option value="">Không có tọa độ hợp lệ</option>');
    return;
  }
  
  // ✅ THÊM: Preselect ngay nếu có bưu cục cũ
  if (savedPostOfficeId) {
    @php
      $postOfficeName = $order->postOffice->name ?? 'Bưu cục đã chọn';
      $postOfficeLat = $order->postOffice->latitude ?? $order->sender_latitude;
      $postOfficeLng = $order->postOffice->longitude ?? $order->sender_longitude;
    @endphp
    
    $('#postOfficeSelect').html(
      `<option value="${savedPostOfficeId}" 
               data-lat="{{ $postOfficeLat }}" 
               data-lng="{{ $postOfficeLng }}" 
               selected>
        {{ $postOfficeName }}
      </option>`
    );
  }
  
  // Sau đó mới fetch danh sách mới
  fetchNearbyPostOffices(senderLat, senderLng);
  
  // ✅ Đợi tối đa 5 giây
  let attempts = 0;
  const checkInterval = setInterval(() => {
    attempts++;
    const optionsCount = $('#postOfficeSelect option').length;
    
    if (optionsCount > 1 || attempts > 10) {
      clearInterval(checkInterval);
      
      // Đảm bảo bưu cục cũ vẫn được selected
      if (savedPostOfficeId && $('#postOfficeSelect').val() !== savedPostOfficeId) {
        const optionExists = $(`#postOfficeSelect option[value="${savedPostOfficeId}"]`).length > 0;
        if (optionExists) {
          $('#postOfficeSelect').val(savedPostOfficeId);
        }
      }
    }
  }, 500);
}

// ============ XỬ LÝ KHI ĐỔI BƯU CỤC ============
$('#postOfficeSelect').on('change', function() {
  const selectedOption = $(this).find('option:selected');
  const officeId = $(this).val();
  const officeName = selectedOption.text();
  
  console.log('📍 Đổi bưu cục:', {
    id: officeId,
    name: officeName
  });
  
  if (officeId) {
    // ✅ CHỈ CẬP NHẬT POST_OFFICE_ID, KHÔNG ĐỘNG VÀO SENDER COORDINATES
    
    // Hiển thị thông báo
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2000
    });
    
    Toast.fire({
      icon: 'info',
      title: `Đã chọn ${officeName.split('-')[0].trim()}`
    });
    
    // ✅ Tính lại phí (backend sẽ tự động lấy tọa độ bưu cục từ post_office_id)
    calculateCost();
  }
});

// ============ SETUP EVENT HANDLERS ============
function setupEventHandlers() {
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
  
  // ✅ THÊM: Event cho checkboxes trong form THÊM MỚI
  $('.special-checkbox, .doc-special-checkbox').on('change', function() {
    console.log('🔄 Checkbox thay đổi trong form thêm mới');
    // Không cần tính lại phí ở đây vì chưa thêm vào productsList
    // Chỉ cần đánh dấu đã thay đổi
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
  
  $('.delivery-time-input').on('change', function() {
    const value = $(this).val();
    $('#delivery_time_formatted').val(formatDatetimeForDatabase(value));
  });
  
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.address-detail, .address-suggestions').length) {
      $('.address-suggestions').hide();
    }
  });
}

function handleDistrictChange() {
  const districtCode = String($('.district-select').val() || '');
  
  $('.ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
  
  if (!districtCode || vietnamData.length === 0) {
    updateFullAddress();
    return;
  }
  
  const hanoi = vietnamData[0];
  const district = hanoi.districts.find(d => String(d.code) === districtCode);
  
  if (district && district.wards && Array.isArray(district.wards)) {
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
  if (provinceText) addressParts.push(provinceText);
  
  const fullAddress = addressParts.join(', ');
  $('.full-address').text(fullAddress || 'Chưa có địa chỉ đầy đủ');
  $('.recipient-full-address').val(fullAddress);
  
  if (geocodeTimeout) clearTimeout(geocodeTimeout);
  
  if (fullAddress) {
    $('.geocode-status').html('<small class="text-warning"><i class="bi bi-hourglass-split"></i> Đang tìm tọa độ...</small>');
    geocodeTimeout = setTimeout(() => {
      fetchCoordinates(fullAddress);
    }, 1000);
  }
}

function goongAutocomplete(query) {
  const provinceText = $('.province-select option:selected').text();
  let input = query;
  if (provinceText) {
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
        
        $('.address-detail').val(description.split(',')[0].trim());
        updateFullAddress();
        calculateCost();
      }
    }
  });
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
        calculateCost();
      } else {
        $('.geocode-status').html('<small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Không tìm thấy tọa độ</small>');
      }
    },
    error: function() {
      $('.geocode-status').html('<small class="text-danger"><i class="bi bi-x-circle"></i> Lỗi Goong API</small>');
    }
  });
}

// ============ PRODUCTS: THÊM MỚI ============
function addProduct() {
  const name = $('.product-name').val().trim();
  const quantity = parseInt($('.product-quantity').val()) || 1;
  const weight = parseFloat($('.product-weight').val()) || 0;
  const value = getCurrencyValue($('.product-value'));
  const length = parseFloat($('.product-length').val()) || 0;
  const width = parseFloat($('.product-width').val()) || 0;
  const height = parseFloat($('.product-height').val()) || 0;
  
  if (!name || weight <= 0) {
    alert('⚠️ Vui lòng nhập đủ thông tin hàng');
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
  
  if (!name || weight <= 0) {
    alert('⚠️ Vui lòng nhập đủ thông tin tài liệu');
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
  renderProductsList();
  resetDocumentForm();
  calculateCost();
}

// ============ PRODUCTS: HIỂN THỊ DANH SÁCH (CÓ NÚT SỬA) ============
function renderProductsList() {
  const container = $('.products-list');
  
  if (!productsList || productsList.length === 0) {
    container.html('<div class="alert alert-warning">Chưa có hàng hóa</div>');
    $('.products-json').val('[]');
    return;
  }
  
  let html = '<h6 class="fw-bold mb-2">Danh sách hàng hóa:</h6>';
  productsList.forEach((item, idx) => {
    const icon = item.type === 'package' ? '📦' : '📄';
    
    // ✅ THÊM: Format specials labels
    let specialsHtml = '';
    if (item.specials && item.specials.length > 0) {
      const specialsLabels = item.specials.map(s => {
        const labelMap = {
          'high_value': 'Giá trị cao',
          'oversized': 'Quá khổ',
          'fragile': 'Dễ vỡ',
          'liquid': 'Chất lỏng',
          'bulk': 'Nguyên khối',
          'battery': 'Từ tính, Pin',
          'certificate': 'Hóa đơn, Giấy chứng nhận'
        };
        return labelMap[s] || s;
      }).join(', ');
      specialsHtml = `<br><small class="text-warning"><i class="bi bi-star-fill"></i> ${specialsLabels}</small>`;
    }
    
    html += `
      <div class="product-item">
        <button type="button" class="btn btn-sm btn-warning edit-product-btn" onclick="editProduct(${idx})">
          <i class="bi bi-pencil"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger remove-product-btn" onclick="removeProduct(${idx})">
          <i class="bi bi-trash"></i>
        </button>
        <div class="pe-5">
          <strong>${icon} ${item.name}</strong>
          <div class="text-muted small">
            SL: ${item.quantity} | KL: ${item.weight}g | GT: ${item.value.toLocaleString('vi-VN')}đ
            ${item.length || item.width || item.height ? `<br>Kích thước: ${item.length}×${item.width}×${item.height} cm` : ''}
            ${specialsHtml}
          </div>
        </div>
      </div>
    `;
  });
  
  container.html(html);
  $('.products-json').val(JSON.stringify(productsList));
}

// ============ PRODUCTS: SỬA (ĐỔ THÔNG TIN VÀO FORM) ============
function editProduct(idx) {
    const product = productsList[idx];
    
    console.log('📝 Đang sửa product:', product);
    
    // Hiện form sửa
    $('#editingProductForm').slideDown();
    editingProductIndex = idx;
    $('#editingProductIndex').val(idx);
    
    // Đổ thông tin cơ bản vào form
    $('#edit-product-name').val(product.name);
    $('#edit-product-quantity').val(product.quantity);
    $('#edit-product-weight').val(product.weight);
    setCurrencyValue($('#edit-product-value'), product.value);
    $('#edit-product-length').val(product.length || '');
    $('#edit-product-width').val(product.width || '');
    $('#edit-product-height').val(product.height || '');
    
    // ✅ BƯỚC 1: Clear tất cả checkboxes trước
    $('.edit-special-checkbox').prop('checked', false);
    
    // ✅ BƯỚC 2: Đánh dấu lại specials từ product
    if (product.specials && Array.isArray(product.specials) && product.specials.length > 0) {
        console.log('🏷️ Specials cần check:', product.specials);
        
        product.specials.forEach(specialValue => {
            // ✅ FIX: Tìm checkbox theo VALUE attribute
            // specialValue có thể là tiếng Anh (high_value) hoặc Việt (Giá trị cao)
            // Backend gửi về tiếng Việt, nhưng checkbox value là tiếng Anh
            
            // Lấy key tiếng Anh từ tên Việt
            const specialsTranslation = {
                'Giá trị cao': 'high_value',
                'Quá khổ': 'oversized',
                'Dễ vỡ': 'fragile',
                'Chất lỏng': 'liquid',
                'Nguyên khối': 'bulk',
                'Từ tính, Pin': 'battery',
                'Hóa đơn, Giấy chứng nhận': 'certificate'
            };
            
            // Nếu specialValue là tiếng Việt, convert sang tiếng Anh
            let specialKey = specialsTranslation[specialValue] || specialValue;
            
            // Tìm checkbox có value = specialKey
            const $checkbox = $(`.edit-special-checkbox[value="${specialKey}"]`);
            
            if ($checkbox.length > 0) {
                $checkbox.prop('checked', true);
                console.log(`✅ Đã check: ${specialKey} (${specialValue})`);
            } else {
                console.warn(`⚠️ Không tìm thấy checkbox cho: ${specialKey}`);
            }
        });
    } else {
        console.log('ℹ️ Product không có specials');
    }
    
    // Scroll đến form
    $('#editingProductForm')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ============ PRODUCTS: LƯU SAU KHI SỬA ============
function saveEditProduct() {
    if (editingProductIndex === null) {
        console.error('❌ editingProductIndex is null!');
        return;
    }
    
    const name = $('#edit-product-name').val().trim();
    const quantity = parseInt($('#edit-product-quantity').val()) || 1;
    const weight = parseFloat($('#edit-product-weight').val()) || 0;
    const value = getCurrencyValue($('#edit-product-value'));
    const length = parseFloat($('#edit-product-length').val()) || 0;
    const width = parseFloat($('#edit-product-width').val()) || 0;
    const height = parseFloat($('#edit-product-height').val()) || 0;
    
    if (!name || weight <= 0) {
        alert('⚠️ Vui lòng nhập đủ thông tin');
        return;
    }
    
    // ✅ LẤY SPECIALS TỪ CHECKBOXES
    // ⚠️ QUAN TRỌNG: Checkboxes có value="high_value", "oversized", etc.
    // Nhưng backend cần tiếng Anh để tính phí
    const specials = [];
    $('.edit-special-checkbox:checked').each(function() {
        const specialValue = $(this).val(); // Lấy value (tiếng Anh)
        specials.push(specialValue);
        console.log(`✅ Lưu special: ${specialValue}`);
    });
    
    console.log('💾 Specials sau khi lưu:', specials);
    
    // ✅ CẬP NHẬT PRODUCT
    productsList[editingProductIndex] = {
        type: productsList[editingProductIndex].type,
        name: name,
        quantity: quantity,
        weight: weight,
        value: value,
        length: length,
        width: width,
        height: height,
        specials: specials // ✅ LƯU DẠNG TIẾNG ANH VỀ BACKEND
    };
    
    console.log('✅ Product sau khi update:', productsList[editingProductIndex]);
    
    // Reset form
    cancelEditProduct();
    renderProductsList();
    
    // ✅ TÍNH LẠI PHÍ NGAY
    calculateCost();
}

// ============ PRODUCTS: HỦY SỬA ============
function cancelEditProduct() {
  $('#editingProductForm').slideUp();
  editingProductIndex = null;
  
  // Clear form
  $('#edit-product-name').val('');
  $('#edit-product-quantity').val('1');
  $('#edit-product-weight').val('1');
  $('#edit-product-value').val('0');
  $('#edit-product-length').val('');
  $('#edit-product-width').val('');
  $('#edit-product-height').val('');
  $('.edit-special-checkbox').prop('checked', false);
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
  $('.product-weight').val('1');
  $('.product-value').val('0');
  $('.product-length').val('');
  $('.product-width').val('');
  $('.product-height').val('');
  $('.special-checkbox').prop('checked', false);
}

function resetDocumentForm() {
  $('.document-name').val('');
  $('.document-quantity').val('1');
  $('.document-weight').val('1');
  $('.document-value').val('0');
  $('.document-length').val('');
  $('.document-width').val('');
  $('.document-height').val('');
  $('.doc-special-checkbox').prop('checked', false);
}

// ============ IMAGES ============
function markImageForDeletion(imageId) {
  if (confirm('Xóa ảnh này?')) {
    imagesToDelete.push(imageId);
    $(`.existing-image-item[data-image-id="${imageId}"]`).hide();
    
    let currentValue = $('.delete-images-input').val();
    let idsArray = currentValue ? currentValue.split(',').filter(Boolean) : [];
    idsArray.push(imageId);
    $('.delete-images-input').val(idsArray.join(','));
  }
}

function handleNewImageUpload(e) {
  const files = Array.from(e.target.files);
  const MAX_IMAGES = 5;
  const MAX_FILE_SIZE = 5 * 1024 * 1024;
  
  const existingCount = $('.existing-images-container .existing-image-item:visible').length;
  const newCount = selectedImages.length;
  
  if (existingCount + newCount + files.length > MAX_IMAGES) {
    alert(`⚠️ Tối đa ${MAX_IMAGES} ảnh`);
    $(e.target).val('');
    return;
  }
  
  for (let file of files) {
    if (!file.type.startsWith('image/')) {
      alert('⚠️ Chỉ chấp nhận file ảnh');
      continue;
    }
    if (file.size > MAX_FILE_SIZE) {
      alert(`⚠️ File vượt quá 5MB`);
      continue;
    }
    selectedImages.push(file);
  }
  
  renderNewImagePreviews();
}

function renderNewImagePreviews() {
  const container = $('.image-preview-container');
  container.html('');
  
  selectedImages.forEach((file, index) => {
    const reader = new FileReader();
    reader.onload = function(e) {
      const html = `
        <div class="col-md-6 col-6">
          <div class="image-preview-item">
            <button type="button" class="remove-image" onclick="removeNewImage(${index})">×</button>
            <img src="${e.target.result}" alt="Preview">
            <div class="p-2">
              <input type="text" class="form-control form-control-sm" name="image_notes[]" placeholder="Ghi chú ảnh">
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

// ============ CALCULATE COST ============
// ============ FIX: CALCULATE COST FUNCTION ============
// ❌ LỖI: Không gửi post_office_id → Backend không lấy được tọa độ bưu cục
// ✅ GIẢI PHÁP: Thêm post_office_id vào data gửi lên

function calculateCost() {
  if (!productsList || productsList.length === 0) {
    resetCostDisplay();
    return;
  }
  
  $('.cost-breakdown').css('opacity', '0.5');
  $('.total-cost').html('<span class="spinner-border spinner-border-sm"></span> Đang tính...');
  
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
  
  const senderLat = $('.sender-latitude').val();
  const senderLng = $('.sender-longitude').val();
  const recipientLat = $('.recipient-lat').val();
  const recipientLng = $('.recipient-lng').val();
  const postOfficeId = $('#postOfficeSelect').val();
  
  console.log('📊 Gửi tính phí với:', {
    post_office_id: postOfficeId,
    sender: [senderLat, senderLng],
    recipient: [recipientLat, recipientLng],
    products: productsList,
    services: services,
    codAmount: codAmount
  });
  
  const data = {
    products_json: JSON.stringify(productsList),
    services: services,
    cod_amount: codAmount,
    payer: payer,
    item_type: itemType,
    sender_latitude: senderLat,
    sender_longitude: senderLng,
    recipient_latitude: recipientLat,
    recipient_longitude: recipientLng,
    post_office_id: postOfficeId,
    // ✅ Không cần _token vì đã setup trong $.ajaxSetup
  };
  
  $.ajax({
    url: '{{ route("customer.orders.calculate") }}',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function(res) {
      console.log('✅ Kết quả tính phí:', res);
      
      $('.cost-breakdown').css('opacity', '1');
      if (res && res.success === true) {
        $('.base-cost').text((res.base_cost || 0).toLocaleString('vi-VN') + ' đ');
        $('.extra-cost').text((res.extra_cost || 0).toLocaleString('vi-VN') + ' đ');
        
        if (res.distance_fee && res.distance_fee > 0) {
          $('.distance-fee').text(res.distance_fee.toLocaleString('vi-VN') + ' đ');
          $('.distance-fee-row').show();
        } else {
          $('.distance-fee').text('0 đ');
          $('.distance-fee-row').hide();
        }
        
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
      } else {
        $('.total-cost').html('<span class="text-danger">Lỗi: ' + (res.message || 'Tính phí thất bại') + '</span>');
      }
    },
    error: function(xhr) {
      console.error('❌ Calculate error:', xhr);
      $('.cost-breakdown').css('opacity', '1');
      
      let errorMsg = 'Lỗi tính phí';
      
      if (xhr.status === 419) {
        errorMsg = 'Phiên làm việc hết hạn. Vui lòng tải lại trang.';
        // ✅ Tự động reload sau 2 giây
        setTimeout(() => location.reload(), 2000);
      } else {
        try {
          const error = JSON.parse(xhr.responseText);
          errorMsg = 'Lỗi: ' + (error.message || errorMsg);
        } catch (e) {
          // Giữ errorMsg mặc định
        }
      }
      
      $('.total-cost').html('<span class="text-danger">' + errorMsg + '</span>');
    }
  });
}

function resetCostDisplay() {
  $('.base-cost').text('0 đ');
  $('.extra-cost').text('0 đ');
  $('.distance-fee').text('0 đ');
  $('.distance-fee-row').hide();
  $('.total-cost').text('0 đ');
  $('.sender-pays').text('0 đ');
  $('.recipient-pays').text('0 đ');
  $('.cod-fee-row').hide();
}

function setupEditFormEventHandlers() {
    // ✅ Event khi checkbox thay đổi trong form SỬA
    $(document).on('change', '.edit-special-checkbox', function() {
        console.log('🔄 Edit form checkbox thay đổi:', {
            value: $(this).val(),
            checked: $(this).is(':checked')
        });
        
        // ✅ QUAN TRỌNG: Lưu thay đổi vào productsList NGAY LẬP TỨC
        if (editingProductIndex !== null) {
            const specials = [];
            $('.edit-special-checkbox:checked').each(function() {
                specials.push($(this).val());
            });
            
            // Update tạm thời vào productsList (chưa save)
            if (productsList[editingProductIndex]) {
                productsList[editingProductIndex].specials = specials;
                console.log('📦 Updated specials:', specials);
            }
        }
        
        // Tính lại phí
        calculateCost();
    });
    
    // Event khi thay đổi thông tin sản phẩm
    $(document).on('input change', '#edit-product-quantity, #edit-product-weight, #edit-product-value, #edit-product-length, #edit-product-width, #edit-product-height', function() {
        console.log('🔄 Product info thay đổi');
        
        // ✅ Update tạm thời vào productsList
        if (editingProductIndex !== null && productsList[editingProductIndex]) {
            const qty = parseInt($('#edit-product-quantity').val()) || 1;
            const weight = parseFloat($('#edit-product-weight').val()) || 0;
            const value = getCurrencyValue($('#edit-product-value'));
            
            productsList[editingProductIndex].quantity = qty;
            productsList[editingProductIndex].weight = weight;
            productsList[editingProductIndex].value = value;
            productsList[editingProductIndex].length = parseFloat($('#edit-product-length').val()) || 0;
            productsList[editingProductIndex].width = parseFloat($('#edit-product-width').val()) || 0;
            productsList[editingProductIndex].height = parseFloat($('#edit-product-height').val()) || 0;
        }
        
        calculateCost();
    });
}

// ============ CURRENCY FORMATTING ============
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

function setCurrencyValue(element, value) {
  const $el = typeof element === 'string' ? $(element) : element;
  const formatted = formatCurrencyDisplay(value);
  const actual = getActualValue(formatted);
  $el.val(formatted);
  $el.data('actual-value', actual);
}

function setupCurrencyFormatting() {
  const selector = 'input[id*="value"], input[id*="cod-amount"], input[class*="value"], input[class*="cod-amount"]';
  
  $(document).on('input', selector, function() {
    const $input = $(this);
    const rawValue = $input.val().replace(/\D/g, '');
    const formatted = formatCurrencyDisplay(rawValue);
    const actual = getActualValue(formatted);
    
    $input.val(formatted);
    $input.data('actual-value', actual);
  });
}

function formatExistingCurrencyValues() {
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

// ============ FORM SUBMIT ============
$('#orderEditForm').on('submit', function(e) {
  e.preventDefault();
  
  if (!validateForm()) {
    return false;
  }
  
  $('.products-json').val(JSON.stringify(productsList));
  
  const pickupValue = $('.pickup-time').val();
  if (pickupValue) {
    $('#pickup_time_formatted').val(formatDatetimeForDatabase(pickupValue));
  }
  
  const deliveryValue = $('.delivery-time-input').val();
  if (deliveryValue) {
    $('#delivery_time_formatted').val(formatDatetimeForDatabase(deliveryValue));
  }
  
  $('#submitUpdate').prop('disabled', true)
    .html('<span class="spinner-border spinner-border-sm me-2"></span>Đang cập nhật...');
  
  this.submit();
});

function validateForm() {
  if (!$('.recipient-name').val().trim()) {
    alert('⚠️ Vui lòng nhập tên người nhận');
    return false;
  }
  
  if (!$('.recipient-phone').val().trim()) {
    alert('⚠️ Vui lòng nhập số điện thoại người nhận');
    return false;
  }
  
  if (!$('.district-select').val() || !$('.ward-select').val()) {
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

    if (!$('#postOfficeSelect').val()) {
    alert('⚠️ Vui lòng chọn bưu cục nhận hàng');
    $('#postOfficeSelect').focus();
    return false;
  }
  
  return true;
}

// ============ GLOBAL FUNCTIONS ============
window.markImageForDeletion = markImageForDeletion;
window.removeNewImage = removeNewImage;
window.removeProduct = removeProduct;
window.editProduct = editProduct;
window.saveEditProduct = saveEditProduct;
window.cancelEditProduct = cancelEditProduct;
</script>
<script>
$(document).ready(function() {
  $('#refreshPostOfficeBtn').on('click', function(e) {
    e.preventDefault();
    
    const $btn = $(this);
    const $icon = $btn.find('i');
    
    // Disable button & show loading
    $btn.prop('disabled', true);
    $icon.addClass('spin');
    
    // Reset dropdown
    $('#postOfficeSelect').html('<option value="">-- Đang tải bưu cục... --</option>');
    
    // Lấy tọa độ sender
    const senderLat = parseFloat($('.sender-latitude').val());
    const senderLng = parseFloat($('.sender-longitude').val());
    
    if (!senderLat || !senderLng) {
      alert('⚠️ Không có tọa độ hợp lệ');
      $btn.prop('disabled', false);
      $icon.removeClass('spin');
      return;
    }
    
    // Gọi lại hàm fetch
    fetchNearbyPostOffices(senderLat, senderLng);
    
    // Đợi cho đến khi có dữ liệu (tối đa 10 giây)
    let attempts = 0;
    const checkInterval = setInterval(() => {
      attempts++;
      const optionsCount = $('#postOfficeSelect option').length;
      
      if (optionsCount > 1 || attempts > 20) {
        clearInterval(checkInterval);
        
        // Enable button
        $btn.prop('disabled', false);
        $icon.removeClass('spin');
        
        // Restore bưu cục cũ nếu có
        const savedPostOfficeId = '{{ $order->post_office_id }}';
        if (savedPostOfficeId && $('#postOfficeSelect').val() !== savedPostOfficeId) {
          const optionExists = $(`#postOfficeSelect option[value="${savedPostOfficeId}"]`).length > 0;
          if (optionExists) {
            $('#postOfficeSelect').val(savedPostOfficeId);
          }
        }
        
        // Log result
        if (optionsCount > 1) {
          console.log('✅ Tải lại bưu cục thành công');
        } else {
          console.warn('⚠️ Không tải được danh sách bưu cục');
        }
      }
    }, 500);
  });
});
</script>

@endsection