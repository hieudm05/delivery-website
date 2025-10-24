@extends('customer.dashboard.layouts.app')
@section('title', 'Tạo đơn hàng')

@section('content')
<link rel="stylesheet" href="{{ asset('assets2/css/customer/dashboard/orders/style.css') }}">

<div class="container-fluid py-4">
  <form id="orderForm" method="POST" action="{{ route('customer.orders.store') }}">
    @csrf
    <input type="hidden" id="products_json" name="products_json">
    <input type="hidden" id="pickup_time_formatted" name="pickup_time_formatted">
    <input type="hidden" id="delivery_time_formatted" name="delivery_time_formatted">

    <div class="row">
      <!-- CỘT TRÁI: THÔNG TIN NGƯỜI GỬI & NHẬN -->
      <div class="col-lg-6">
        <!-- NGƯỜI GỬI -->
        <div class="card mb-4">
          <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0">Thông tin người gửi</h6>
              <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input" id="sameAsAccount">
                <label for="sameAsAccount" class="form-check-label">Gửi tại bưu cục</label>
              </div>
            </div>
          </div>

          <div class="card-body">
            @php
              $account = $user;
            @endphp
            @if (!$account || !$account->userInfo)
              <div class="alert alert-warning">
                <a href="{{url('/customer/account')}}" class="alert-link">⚠️ Vui lòng cập nhật thông tin tài khoản trước</a>
              </div>
            @else
              <div class="mb-3">
                <label class="form-label">Chọn thông tin người gửi</label>
                <select class="form-select" id="sender-select" name="sender_id" required>
                  <option value="">-- Chọn người gửi --</option>
                  <option value="{{ $account->id }}" 
                          data-name="{{ $account->full_name }}"
                          data-phone="{{ $account->phone }}"
                          data-lat="{{ $account->userInfo->latitude ?? '' }}" 
                          data-lng="{{ $account->userInfo->longitude ?? '' }}"
                          data-address="{{ $account->userInfo->full_address ?? '' }}">
                    {{ $account->full_name }} - {{ $account->phone }} - {{ $account->userInfo->full_address}}
                  </option>
                </select>
              </div>

              <div id="sender-info" class="d-none">
                <div class="p-3 bg-light rounded">
                  <div><strong>Họ tên:</strong> <span id="sender-name-display"></span></div>
                  <div><strong>SĐT:</strong> <span id="sender-phone-display"></span></div>
                  <div><strong>Địa chỉ:</strong> <span id="sender-address-display"></span></div>
                </div>
              </div>

              <input type="hidden" id="sender-latitude" name="sender_latitude">
              <input type="hidden" id="sender-longitude" name="sender_longitude">
              <input type="hidden" id="sender-address" name="sender_address">
              <input type="hidden" id="sender-name" name="sender_name">
              <input type="hidden" id="sender-phone" name="sender_phone">
            @endif

            <div id="post-office-selects" style="display:none;">
              <label for="postOfficeSelect" class="form-label">Bưu cục gần bạn</label>
              <select class="form-select mb-3" id="postOfficeSelect" name="post_office_id">
                <option value="">-- Chọn bưu cục --</option>
              </select>
            </div>

            <div id="appointment-select" style="display:block;">
              <label for="pickup-time" class="form-label">Thời gian hẹn lấy hàng <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control" id="pickup-time" name="pickup_time" required>
            </div>
          </div>
        </div>

        <!-- NGƯỜI NHẬN -->
        <div class="card mb-4">
          <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0">Thông tin người nhận</h6>
              <button type="button" class="btn btn-sm btn-outline-primary" id="loadSavedAddress">
                <i class="bi bi-bookmark"></i> Địa chỉ đã lưu
              </button>
            </div>
          </div>

          <div class="card-body">
            <div id="saved-addresses-container" class="mb-3 d-none">
              <div class="mb-2">
                <small class="text-muted">Chọn địa chỉ đã lưu:</small>
              </div>
              <div id="saved-addresses-list"></div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="recipientName" class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="recipientName" name="recipient_name" placeholder="Nhập tên người nhận" required>
              </div>

              <div class="col-md-6 mb-3">
                <label for="recipientPhone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="recipientPhone" name="recipient_phone" placeholder="Nhập số điện thoại" required>
              </div>

              <div class="col-md-12 mb-3">
                <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <select class="form-select" id="province-select" name="province_code" required>
                      <option value="">Tỉnh/Thành phố</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <select class="form-select" id="district-select" name="district_code" required disabled>
                      <option value="">Quận/Huyện</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <select class="form-select" id="ward-select" name="ward_code" required disabled>
                      <option value="">Phường/Xã</option>
                    </select>
                  </div>
                  <div class="col-md-6 address-input-wrapper">
                    <input type="text" id="address-detail" name="address_detail" class="form-control" placeholder="Số nhà, tên đường..." required autocomplete="off">
                    <div id="address-suggestions" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                  </div>
                </div>
              </div>

              <div class="col-md-12 mb-3">
                <label class="form-label">Địa chỉ đầy đủ</label>
                <div class="p-2 bg-light rounded">
                  <small id="full-address" class="text-muted">Chưa có địa chỉ đầy đủ</small>
                </div>
                <input type="hidden" id="latitude" name="recipient_latitude">
                <input type="hidden" id="longitude" name="recipient_longitude">
                <input type="hidden" name="recipient_full_address" id="recipient-full-address">
                <div id="geocode-status" class="mt-1">
                  <small class="text-muted">Chưa tìm tọa độ</small>
                </div>
              </div>

              <div class="col-md-12 mb-3">
                <label for="delivery-time" class="form-label">Thời gian hẹn giao <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="delivery-time" name="delivery_time" required>
              </div>

              <div class="col-md-12">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="saveAddress" name="save_address">
                  <label class="form-check-label" for="saveAddress">Lưu địa chỉ này cho lần sau</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CỘT PHẢI: THÔNG TIN HÀNG HÓA -->
      <div class="col-lg-6">
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h5 class="mb-0">Thông tin hàng hoá</h5>
          </div>

          <div class="card-body">
            <!-- LOẠI HÀNG -->
            <div class="mb-3">
              <label class="form-label fw-bold">LOẠI HÀNG HÓA</label>
              <div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="item_type" id="buuKien" value="package" checked>
                  <label class="form-check-label text-danger" for="buuKien">Bưu kiện</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="item_type" id="taiLieu" value="document">
                  <label class="form-check-label text-danger" for="taiLieu">Tài liệu</label>
                </div>
              </div>
            </div>

            <!-- FORM NHẬP BƯUUUU KIỆ -->
            <div id="formBuuKien">
              <div class="product-input-section">
                <h6 class="fw-bold mb-3">Thêm hàng hoá</h6>
                
                <div class="row g-3">
                  <!-- Tên hàng -->
                  <div class="col-12">
                    <label class="form-label">Tên hàng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="product-name" 
                           placeholder="VD: Áo thun, Sách, Điện thoại..." >
                  </div>

                  <!-- Số lượng, KL, GT -->
                  <div class="col-md-4">
                    <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="quantity" value="" min="1" >
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="weight" value="" min="1" >
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="value" value="" min="0" >
                  </div>
                </div>

                <!-- Kích thước (optional) -->
                <div class="row mt-3">
                  <div class="col-12 mb-2">
                    <label class="form-label">Kích thước (không bắt buộc)</label>
                  </div>
                  <div class="col-md-4">
                    <input type="number" class="form-control" id="length" placeholder="Dài (cm)" min="0">
                  </div>
                  <div class="col-md-4">
                    <input type="number" class="form-control" id="width" placeholder="Rộng (cm)" min="0">
                  </div>
                  <div class="col-md-4">
                    <input type="number" class="form-control" id="height" placeholder="Cao (cm)" min="0">
                  </div>
                </div>

                <!-- Tính chất đặc biệt -->
                <div class="mt-4 special-box">
                  <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-diamond"></i> Tính chất hàng hóa</h6>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input special-checkbox" type="checkbox" id="giaTriCao" value="high_value">
                        <label class="form-check-label" for="giaTriCao">Giá trị cao</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input special-checkbox" type="checkbox" id="quaKho" value="oversized">
                        <label class="form-check-label" for="quaKho">Quá khổ</label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input special-checkbox" type="checkbox" id="deVo" value="fragile">
                        <label class="form-check-label" for="deVo">Dễ vỡ</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input special-checkbox" type="checkbox" id="chatLong" value="liquid">
                        <label class="form-check-label" for="chatLong">Chất lỏng</label>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input special-checkbox" type="checkbox" id="nguyenKhoi" value="bulk">
                        <label class="form-check-label" for="nguyenKhoi">Nguyên khối</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input special-checkbox" type="checkbox" id="pin" value="battery">
                        <label class="form-check-label" for="pin">Từ tính, Pin</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Nút thêm -->
                <div class="mt-3 text-end">
                  <button type="button" class="btn btn-primary" id="addProductBtn">
                    <i class="bi bi-plus-circle"></i> Thêm hàng
                  </button>
                </div>
              </div>

              <!-- Danh sách sản phẩm đã thêm -->
              <div id="products-list"></div>
            </div>

            <!-- FORM NHẬP TÀI LIỆU -->
            <div id="formTaiLieu" class="d-none">
              <div class="product-input-section">
                <h6 class="fw-bold mb-3">Thêm tài liệu (Nhập tay)</h6>
                
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Tên tài liệu <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="document-name" placeholder="VD: Hóa đơn, Giấy chứng chỉ...">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="doc-quantity" value="1" min="1">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="doc-weight" value="10" min="1">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="doc-value" value="10000" min="0">
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-12 mb-2">
                    <label class="form-label">Kích thước (không bắt buộc)</label>
                  </div>
                  <div class="col-md-4">
                    <input type="number" class="form-control" id="doc-length" placeholder="Dài (cm)" min="0">
                  </div>
                  <div class="col-md-4">
                    <input type="number" class="form-control" id="doc-width" placeholder="Rộng (cm)" min="0">
                  </div>
                  <div class="col-md-4">
                    <input type="number" class="form-control" id="doc-height" placeholder="Cao (cm)" min="0">
                  </div>
                </div>

                <div class="mt-4 special-box">
                  <h6 class="fw-bold mb-2">Tính chất hàng hóa</h6>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-check">
                        <input class="form-check-input doc-special-checkbox" type="checkbox" id="taiLieuGiaTri" value="high_value">
                        <label class="form-check-label" for="taiLieuGiaTri">Giá trị cao</label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check">
                        <input class="form-check-input doc-special-checkbox" type="checkbox" id="hoaDon" value="certificate">
                        <label class="form-check-label" for="hoaDon">Hóa đơn, Giấy chứng nhận</label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-3 text-end">
                  <button type="button" class="btn btn-primary" id="addDocumentBtn">
                    <i class="bi bi-plus-circle"></i> Thêm tài liệu
                  </button>
                </div>
              </div>

              <div id="documents-list"></div>
            </div>

            <!-- DỊCH VỤ CỘNG THÊM -->
            <div class="card mt-4">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-truck"></i> Dịch vụ cộng thêm</h6>
              </div>
              <div class="card-body">
                <!-- Dịch vụ giao nhanh -->
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="fast" id="fastService" name="services[]">
                  <label class="form-check-label" for="fastService">
                    Giao nhanh <span class="text-muted">(+15%)</span>
                  </label>
                </div>

                <!-- Bảo hiểm -->
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="insurance" id="insuranceService" name="services[]">
                  <label class="form-check-label" for="insuranceService">
                    Bảo hiểm hàng hóa <span class="text-muted">(1% giá trị)</span>
                  </label>
                </div>

                <!-- ✅ COD -->
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" value="cod" id="codService" name="services[]">
                  <label class="form-check-label" for="codService">
                    Dịch vụ thu hộ (COD) <span class="text-muted">(1.000đ + 1%)</span>
                  </label>
                </div>

                <!-- Số tiền COD -->
                <div id="cod-amount-container" class="d-none mb-3">
                  <label class="form-label">Số tiền thu hộ (VNĐ) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="cod-amount" name="cod_amount" min="0" placeholder="Nhập số tiền cần thu">
                </div>

                <!-- ✅ NGƯỜI TRẢ CƯỚC -->
                <div class="mb-3 p-3 bg-light border rounded">
                  <label class="form-label fw-bold">Người trả cước phí <span class="text-danger">*</span></label>
                  <div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="payer" id="payerSender" value="sender" checked>
                      <label class="form-check-label" for="payerSender">Người gửi</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="payer" id="payerRecipient" value="recipient">
                      <label class="form-check-label" for="payerRecipient">Người nhận</label>
                    </div>
                  </div>
                  <small class="text-muted d-block mt-2" id="payer-note">Người gửi thanh toán cước phí</small>
                </div>

                <!-- CHI TIẾT PHÍ -->
                <div class="cost-breakdown mt-3">
                  <div class="cost-item">
                    <span>Cước chính:</span>
                    <span id="baseCost">0 đ</span>
                  </div>
                  <div class="cost-item">
                    <span>Phụ phí:</span>
                    <span id="extraCost">0 đ</span>
                  </div>
                  <!-- ✅ THÊM PHÍ COD -->
                  <div class="cost-item" id="codFeeRow" style="display:none;">
                    <span>Phí COD:</span>
                    <span id="codFee" class="text-warning">0 đ</span>
                  </div>
                  <div class="cost-item">
                    <span>Tổng cước:</span>
                    <span id="tongCuoc" class="text-danger fw-bold">0 đ</span>
                  </div>
                  <hr>
                  <div class="cost-item">
                    <span><strong>Người gửi trả:</strong></span>
                    <span id="senderPays" class="text-primary fw-bold">0 đ</span>
                  </div>
                  <div class="cost-item">
                    <span><strong>Người nhận trả:</strong></span>
                    <span id="recipientPays" class="text-success fw-bold">0 đ</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- GHI CHÚ -->
            <div class="mt-3">
              <label class="form-label">Ghi chú</label>
              <textarea class="form-control" id="note" name="note" rows="3" placeholder="Nhập ghi chú cho đơn hàng (không bắt buộc)"></textarea>
            </div>
            <div class="card mt-4">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-images"></i> Hình ảnh đơn hàng </h6>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label">Thêm hình ảnh hàng hóa (tối đa 5 ảnh, mỗi ảnh max 5MB)</label>
                <input type="file" 
                      class="form-control" 
                      id="order-images" 
                      name="images[]" 
                      accept="image/*" 
                      multiple>
                <small class="text-muted">Hỗ trợ: JPG, PNG, GIF</small>
              </div>

              <!-- Preview ảnh đã chọn -->
              <div id="image-preview-container" class="row g-2"></div>
            </div>
          </div>

            <!-- NÚT SUBMIT -->
            <div class="mt-4 text-end">
              <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Hủy</button>
              <button type="submit" class="btn btn-danger" id="submitOrder">
                <i class="bi bi-check-circle"></i> Tạo đơn hàng
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  const GOONG_API_KEY = '{{ config("services.goong.api_key") }}';

let vietnamData = [];
let productsList = [];
let geocodeTimeout = null;
let autocompleteTimeout = null;

$(document).ready(function() {
    console.log('🚀 Khởi tạo form tạo đơn hàng');
    loadProvinces();
    setupEventHandlers();
    setDefaultDateTime();
    setupGoongAutocomplete();
    setupToggleForms();
});

// ============ DATETIME HANDLING ============
function setDefaultDateTime() {
  const now = new Date();
  const pickupTime = new Date(now.getTime() + 2 * 60 * 60 * 1000);
  const deliveryTime = new Date(now.getTime() + 3 * 60 * 60 * 1000);

  $('#pickup-time').val(toDatetimeLocalString(pickupTime));
  $('#delivery-time').val(toDatetimeLocalString(deliveryTime));
}

function toDatetimeLocalString(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  
  return `${year}-${month}-${day}T${hours}:${minutes}`;
}

function formatDatetimeForDatabase(datetimeLocalValue) {
  if (!datetimeLocalValue) return null;
  const [date, time] = datetimeLocalValue.split('T');
  return `${date} ${time}:00`;
}

function validateDatetimes() {
  const pickupValue = $('#pickup-time').val();
  const deliveryValue = $('#delivery-time').val();
  
  if (!pickupValue || !deliveryValue) {
    alert('⚠️ Vui lòng chọn thời gian hẹn');
    return false;
  }
  
  const pickup = new Date(pickupValue);
  const delivery = new Date(deliveryValue);
  const now = new Date();
  
  if (pickup <= now) {
    alert('⚠️ Thời gian hẹn lấy phải trong tương lai');
    return false;
  }
  
  const minDeliveryTime = new Date(pickup.getTime() + 60 * 60 * 1000);
  if (delivery < minDeliveryTime) {
    alert('⚠️ Thời gian giao phải ít nhất 1 giờ sau thời gian lấy');
    return false;
  }
  
  return true;
}

// ============ GOONG AUTOCOMPLETE ============
function setupGoongAutocomplete() {
    $('#address-detail').on('input', function() {
        const query = $(this).val().trim();
        
        if (autocompleteTimeout) clearTimeout(autocompleteTimeout);
        
        if (query.length < 3) {
            $('#address-suggestions').hide().html('');
            return;
        }
        
        autocompleteTimeout = setTimeout(() => {
            goongAutocomplete(query);
        }, 500);
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#address-detail, #address-suggestions').length) {
            $('#address-suggestions').hide();
        }
    });
}

function goongAutocomplete(query) {
    const provinceText = $('#province-select option:selected').text();
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
            if (data && data.predictions && data.predictions.length > 0) {
                displayAutocompleteSuggestions(data.predictions);
            } else {
                $('#address-suggestions').hide().html('');
            }
        },
        error: function() {
            console.warn('⚠️ Goong Autocomplete API lỗi');
        }
    });
}

function displayAutocompleteSuggestions(predictions) {
    let html = '';
    predictions.forEach(pred => {
        html += `
            <button type="button" class="list-group-item list-group-item-action" 
                    data-place-id="${pred.place_id}"
                    data-description="${pred.description}">
                <i class="bi bi-geo-alt text-danger"></i> ${pred.description}
            </button>
        `;
    });
    
    $('#address-suggestions').html(html).show();
    
    $('.list-group-item', '#address-suggestions').on('click', function(e) {
        e.preventDefault();
        const placeId = $(this).data('place-id');
        const description = $(this).data('description');
        
        goongPlaceDetail(placeId, description);
        $('#address-suggestions').hide();
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
            if (data && data.result) {
                const result = data.result;
                const lat = result.geometry.location.lat;
                const lng = result.geometry.location.lng;
                
                $('#latitude').val(lat);
                $('#longitude').val(lng);
                $('#geocode-status').html(`
                    <small class="text-success">
                        <i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ
                    </small>
                `);
                
                parseGoongAddress(result, description);
                
                console.log('✅ Địa chỉ từ Goong:', { lat, lng, address: description });
            }
        },
        error: function() {
            console.error('❌ Không thể lấy chi tiết địa điểm');
        }
    });
}

function parseGoongAddress(result, description) {
    $('#address-detail').val(description.split(',')[0].trim());
    
    const addressComponents = result.address_components || [];
    
    addressComponents.forEach(component => {
        const types = component.types || [];
        
        if (types.includes('administrative_area_level_1')) {
            const provinceName = component.long_name;
            $('#province-select option').each(function() {
                if ($(this).text().includes(provinceName)) {
                    $('#province-select').val($(this).val()).trigger('change');
                }
            });
        }
        
        if (types.includes('administrative_area_level_2')) {
            setTimeout(() => {
                const districtName = component.long_name;
                $('#district-select option').each(function() {
                    if ($(this).text().includes(districtName)) {
                        $('#district-select').val($(this).val()).trigger('change');
                    }
                });
            }, 500);
        }
        
        if (types.includes('sublocality_level_1') || types.includes('administrative_area_level_3')) {
            setTimeout(() => {
                const wardName = component.long_name;
                $('#ward-select option').each(function() {
                    if ($(this).text().includes(wardName)) {
                        $('#ward-select').val($(this).val()).trigger('change');
                    }
                });
            }, 1000);
        }
    });
    
    setTimeout(() => {
        updateFullAddress();
    }, 1500);
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
            if (data && data.results && data.results.length > 0) {
                const result = data.results[0];
                const lat = result.geometry.location.lat;
                const lng = result.geometry.location.lng;
                
                $('#latitude').val(lat);
                $('#longitude').val(lng);
                $('#geocode-status').html(`
                    <small class="text-success">
                        <i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ
                    </small>
                `);
            } else {
                $('#geocode-status').html(`
                    <small class="text-warning">
                        <i class="bi bi-exclamation-triangle"></i> Không tìm thấy tọa độ chính xác
                    </small>
                `);
            }
        },
        error: function() {
            $('#geocode-status').html(`
                <small class="text-danger">
                    <i class="bi bi-x-circle"></i> Lỗi kết nối Goong API
                </small>
            `);
        }
    });
}

// ============ LOAD DỮ LIỆU TỈNH/HUYỆN ============
function loadProvinces() {
    $.get("https://provinces.open-api.vn/api/?depth=3", function(data) {
        vietnamData = data;
        let html = '<option value="">Tỉnh/Thành phố</option>';
        data.forEach(province => {
            html += `<option value="${province.code}">${province.name}</option>`;
        });
        $('#province-select').html(html);
    }).fail(function() {
        console.error('❌ Không thể tải dữ liệu tỉnh thành');
    });
}

// ============ NGƯỜI GỬI ============
$('#sender-select').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const name = selectedOption.data('name');
    const phone = selectedOption.data('phone');
    const lat = selectedOption.data('lat');
    const lng = selectedOption.data('lng');
    const address = selectedOption.data('address');
    
    if (lat && lng) {
        $('#sender-name').val(name);
        $('#sender-phone').val(phone);
        $('#sender-latitude').val(lat);
        $('#sender-longitude').val(lng);
        $('#sender-address').val(address);
        
        $('#sender-name-display').text(name);
        $('#sender-phone-display').text(phone);
        $('#sender-address-display').text(address);
        $('#sender-info').removeClass('d-none');
    } else {
        $('#sender-info').addClass('d-none');
    }
});

$('#sameAsAccount').on('change', function() {
    if ($(this).is(':checked')) {
        $('#post-office-selects').slideDown();
        $('#appointment-select').slideUp();
        
        const lat = $('#sender-latitude').val();
        const lng = $('#sender-longitude').val();
        if (lat && lng) {
            fetchNearbyPostOffices(parseFloat(lat), parseFloat(lng));
        }
    } else {
        $('#post-office-selects').slideUp();
        $('#appointment-select').slideDown();
    }
});

// ============ NGƯỜI NHẬN - ĐỊA CHỈ ============
$('#loadSavedAddress').on('click', function() {
    $('#saved-addresses-container').toggleClass('d-none');
    if (!$('#saved-addresses-container').hasClass('d-none')) {
        loadSavedAddresses();
    }
});

function loadSavedAddresses() {
    $.get('{{ route("customer.orders.addresses.list") }}', function(data) {
        displaySavedAddresses(data);
    }).fail(function() {
        alert('Không thể tải địa chỉ đã lưu');
    });
}

function displaySavedAddresses(addresses) {
    if (!addresses || addresses.length === 0) {
        $('#saved-addresses-list').html('<p class="text-muted">Chưa có địa chỉ nào được lưu</p>');
        return;
    }

    let html = '';
    addresses.forEach(addr => {
        html += `
            <div class="address-saved-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>${addr.recipient_name}</strong> - ${addr.recipient_phone}
                        <div class="text-muted small">${addr.full_address}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick='selectSavedAddress(${JSON.stringify(addr)})'>Chọn</button>
                </div>
            </div>
        `;
    });
    $('#saved-addresses-list').html(html);
}

function selectSavedAddress(addr) {
    $('#recipientName').val(addr.recipient_name);
    $('#recipientPhone').val(addr.recipient_phone);
    $('#address-detail').val(addr.address_detail);
    
    $('#province-select').val(addr.province_code).trigger('change');
    
    setTimeout(() => {
        $('#district-select').val(addr.district_code).trigger('change');
        setTimeout(() => {
            $('#ward-select').val(addr.ward_code).trigger('change');
        }, 300);
    }, 300);

    $('#saved-addresses-container').addClass('d-none');
}

$('#province-select').on('change', function() {
    const provinceCode = parseInt($(this).val());
    
    $('#district-select').html('<option value="">Quận/Huyện</option>').prop('disabled', true);
    $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!provinceCode) {
        updateFullAddress();
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    if (province?.districts) {
        let html = '<option value="">Quận/Huyện</option>';
        province.districts.forEach(district => {
            html += `<option value="${district.code}">${district.name}</option>`;
        });
        $('#district-select').html(html).prop('disabled', false);
    }
    updateFullAddress();
});

$('#district-select').on('change', function() {
    const districtCode = parseInt($(this).val());
    const provinceCode = parseInt($('#province-select').val());
    
    $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!districtCode) {
        updateFullAddress();
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    const district = province?.districts.find(d => d.code === districtCode);
    
    if (district?.wards) {
        let html = '<option value="">Phường/Xã</option>';
        district.wards.forEach(ward => {
            html += `<option value="${ward.code}">${ward.name}</option>`;
        });
        $('#ward-select').html(html).prop('disabled', false);
    }
    updateFullAddress();
});

$('#ward-select, #address-detail').on('change keyup', updateFullAddress);

function updateFullAddress() {
    const detail = $('#address-detail').val().trim();
    const wardText = $('#ward-select option:selected').text();
    const districtText = $('#district-select option:selected').text();
    const provinceText = $('#province-select option:selected').text();

    let addressParts = [];
    
    if (detail) addressParts.push(detail);
    if ($('#ward-select').val() && wardText !== 'Phường/Xã') addressParts.push(wardText);
    if ($('#district-select').val() && districtText !== 'Quận/Huyện') addressParts.push(districtText);
    if ($('#province-select').val() && provinceText !== 'Tỉnh/Thành phố') addressParts.push(provinceText);

    const fullAddress = addressParts.join(', ');
    $('#full-address').text(fullAddress || 'Chưa có địa chỉ đầy đủ');
    $('#recipient-full-address').val(fullAddress);

    if (geocodeTimeout) clearTimeout(geocodeTimeout);
    
    if ($('#province-select').val() && $('#district-select').val() && fullAddress) {
        $('#geocode-status').html('<small class="text-warning"><i class="bi bi-hourglass-split"></i> Đang tìm tọa độ...</small>');
        
        geocodeTimeout = setTimeout(() => {
            fetchCoordinates(fullAddress);
        }, 1000);
    } else {
        $('#latitude').val('');
        $('#longitude').val('');
        $('#geocode-status').html('<small class="text-muted">Chưa tìm tọa độ</small>');
    }
}

// ============ TOGGLE LOẠI HÀNG HÓA ============
function setupToggleForms() {
    $('input[name="item_type"]').on('change', function() {
        if ($('#buuKien').is(':checked')) {
            $('#formBuuKien').removeClass('d-none');
            $('#formTaiLieu').addClass('d-none');
        } else {
            $('#formTaiLieu').removeClass('d-none');
            $('#formBuuKien').addClass('d-none');
        }
    });
}

// ============ THÊM HÀNG HÓA (BƯUUU KIỆ) ============
$('#addProductBtn').on('click', function() {
    const name = $('#product-name').val().trim();
    const quantity = parseInt($('#quantity').val()) || 1;
    const weight = parseFloat($('#weight').val()) || 0;
    const value = parseFloat($('#value').val()) || 0;
    const length = parseFloat($('#length').val()) || 0;
    const width = parseFloat($('#width').val()) || 0;
    const height = parseFloat($('#height').val()) || 0;

    if (!name) {
        alert('⚠️ Vui lòng nhập tên hàng');
        $('#product-name').focus();
        return;
    }

    if (weight <= 0) {
        alert('⚠️ Khối lượng phải lớn hơn 0');
        $('#weight').focus();
        return;
    }

    const specials = [];
    $('#formBuuKien .special-checkbox:checked').each(function() {
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
});

// ============ THÊM TÀI LIỆU ============
$('#addDocumentBtn').on('click', function() {
    const name = $('#document-name').val().trim();
    const quantity = parseInt($('#doc-quantity').val()) || 1;
    const weight = parseFloat($('#doc-weight').val()) || 0;
    const value = parseFloat($('#doc-value').val()) || 0;
    const length = parseFloat($('#doc-length').val()) || 0;
    const width = parseFloat($('#doc-width').val()) || 0;
    const height = parseFloat($('#doc-height').val()) || 0;

    if (!name) {
        alert('⚠️ Vui lòng nhập tên tài liệu');
        $('#document-name').focus();
        return;
    }

    if (weight <= 0) {
        alert('⚠️ Khối lượng phải lớn hơn 0');
        $('#doc-weight').focus();
        return;
    }

    const specials = [];
    $('#formTaiLieu .doc-special-checkbox:checked').each(function() {
        specials.push($(this).val());
    });

    const doc = {
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

    productsList.push(doc);
    console.log('✅ Đã thêm tài liệu:', name);

    renderProductsList();
    resetDocumentForm();
    calculateCost();
});

function renderProductsList() {
    const container = $('#products-list');
    
    if (productsList.length === 0) {
        container.html('');
        return;
    }

    let html = '<div class="mb-3"><label class="form-label fw-bold">Danh sách hàng hóa đã thêm:</label></div>';

    productsList.forEach((item, idx) => {
        const dims = (item.length || item.width || item.height) 
            ? ` | ${item.length}×${item.width}×${item.height}cm` 
            : '';
        const icon = item.type === 'package' ? '📦' : '📄';

        html += `
            <div class="product-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${icon} ${item.name}</strong>
                        <div class="text-muted small">
                            SL: ${item.quantity} | KL: ${item.weight}g | GT: ${item.value.toLocaleString('vi-VN')}đ${dims}
                        </div>
                        ${item.specials.length > 0 ? `<div class="text-danger small">⚠️ ${item.specials.join(', ')}</div>` : ''}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-btn" onclick="removeProduct(${idx})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.html(html);
    $('#products_json').val(JSON.stringify(productsList));
}

function removeProduct(idx) {
    if (confirm('Xóa hàng này?')) {
        productsList.splice(idx, 1);
        renderProductsList();
        calculateCost();
    }
}

function resetProductForm() {
    $('#product-name').val('');
    $('#quantity').val('1');
    $('#weight').val('10');
    $('#value').val('10000');
    $('#length, #width, #height').val('');
    $('#formBuuKien .special-checkbox').prop('checked', false);
    $('#product-name').focus();
}

function resetDocumentForm() {
    $('#document-name').val('');
    $('#doc-quantity').val('1');
    $('#doc-weight').val('10');
    $('#doc-value').val('10000');
    $('#doc-length, #doc-width, #doc-height').val('');
    $('#formTaiLieu .doc-special-checkbox').prop('checked', false);
    $('#document-name').focus();
}

// ============ DỊCH VỤ COD ============
$('#codService').on('change', function() {
    if ($(this).is(':checked')) {
        $('#cod-amount-container').removeClass('d-none');
    } else {
        $('#cod-amount-container').addClass('d-none');
        $('#cod-amount').val('');
    }
    calculateCost();
});

// ============ TÍNH CƯỚC PHÍ ============
function setupEventHandlers() {
    $('input[type=checkbox][name="services[]"]').on('change', function() {
        if (productsList.length > 0) {
            calculateCost();
        }
    });
    
    $('#cod-amount').on('input', function() {
        if (productsList.length > 0) {
            calculateCost();
        }
    });
}

// ============ NGƯỜI TRẢ CƯỚC ============
$('input[name="payer"]').on('change', function() {
    const payer = $('input[name="payer"]:checked').val();
    const hasCOD = $('#codService').is(':checked') && parseFloat($('#cod-amount').val()) > 0;
    
    let note = '';
    if (payer === 'sender') {
        note = hasCOD 
            ? 'Người gửi KHÔNG trả phí (có COD), người nhận trả tiền hàng' 
            : 'Người gửi trả phí ship';
    } else {
        note = hasCOD 
            ? 'Người nhận trả cả tiền hàng + tiền ship' 
            : 'Người nhận trả phí ship';
    }
    
    $('#payer-note').text(note);
    
    if (productsList.length > 0) {
        calculateCost();
    }
});
function calculateCost() {
    if (!productsList || productsList.length === 0) {
        $('#baseCost').text('0 đ');
        $('#extraCost').text('0 đ');
        $('#tongCuoc').text('0 đ');
        return;
    }
    
    const services = $('input[name="services[]"]:checked').map((_, e) => e.value).get();
    const codAmount = parseFloat($('#cod-amount').val()) || 0;
    const payer = $('input[name="payer"]:checked').val();
    
    const data = {
        products_json: JSON.stringify(productsList),
        services: services,
        cod_amount: codAmount,
        payer: payer,
        item_type: productsList[0]?.type || 'package',
        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
    };
    
    $.post('{{ route("customer.orders.calculate") }}', data)
        .done(function(res) {
            if (res && res.success === true) {
                $('#baseCost').text((res.base_cost || 0).toLocaleString('vi-VN') + ' đ');
                $('#extraCost').text((res.extra_cost || 0).toLocaleString('vi-VN') + ' đ');
                
                // ✅ HIỂN THỊ PHÍ COD (nếu có)
                if (res.cod_fee > 0) {
                    $('#codFee').text(res.cod_fee.toLocaleString('vi-VN') + ' đ');
                    $('#codFeeRow').show();
                } else {
                    $('#codFeeRow').hide();
                }
                
                $('#tongCuoc').text((res.total || 0).toLocaleString('vi-VN') + ' đ');
                $('#senderPays').text((res.sender_pays || 0).toLocaleString('vi-VN') + ' đ');
                $('#recipientPays').text((res.recipient_pays || 0).toLocaleString('vi-VN') + ' đ');
            } else {
                console.error('❌ Lỗi tính cước:', res.message || 'Không xác định');
                resetCostDisplay();
            }
        })
        .fail(function(xhr) {
            console.error('❌ Lỗi tính cước:', xhr.responseText);
        });
  }

function resetCostDisplay() {
    $('#baseCost, #extraCost, #tongCuoc, #senderPays, #recipientPays').text('0 đ');
}
// ============ VALIDATE & SUBMIT FORM ============
function validateForm() {
    if (!$('#sender-select').val()) {
        alert('⚠️ Vui lòng chọn thông tin người gửi');
        return false;
    }
    
    if (!$('#recipientName').val().trim()) {
        alert('⚠️ Vui lòng nhập tên người nhận');
        return false;
    }
    
    if (!$('#recipientPhone').val().trim()) {
        alert('⚠️ Vui lòng nhập số điện thoại người nhận');
        return false;
    }
    
    const phonePattern = /^(0|\+84)[0-9]{9,10}$/;
    if (!phonePattern.test($('#recipientPhone').val().trim())) {
        alert('⚠️ Số điện thoại không hợp lệ');
        return false;
    }
    
    if (!$('#province-select').val() || !$('#district-select').val() || !$('#ward-select').val()) {
        alert('⚠️ Vui lòng chọn địa chỉ đầy đủ');
        return false;
    }
    
    if (!$('#address-detail').val().trim()) {
        alert('⚠️ Vui lòng nhập số nhà, tên đường');
        return false;
    }
    
    if (!productsList || productsList.length === 0) {
        alert('⚠️ Vui lòng thêm ít nhất 1 hàng hóa');
        return false;
    }
    
    if (!validateDatetimes()) {
        return false;
    }
    
    return true;
}

$('#orderForm').on('submit', function(e) {
    console.log('📤 Chuẩn bị submit form');
    
    $('#products_json').val(JSON.stringify(productsList));
    
    if (!validateForm()) {
        e.preventDefault();
        return false;
    }
    
    const pickupValue = $('#pickup-time').val();
    const deliveryValue = $('#delivery-time').val();
    
    $('#pickup_time_formatted').val(formatDatetimeForDatabase(pickupValue));
    $('#delivery_time_formatted').val(formatDatetimeForDatabase(deliveryValue));
    
    $('#submitOrder').prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
    
    console.log('✅ Form sẵn sàng submit');
    return true;
});

// ============ POST OFFICE ============
function fetchNearbyPostOffices(lat, lng) {
    $.get('{{ route("customer.orders.getNearby") }}', {
        latitude: lat,
        longitude: lng,
        limit: 5
    }, function(data) {
        if (data && data.length > 0) {
            let html = '<option value="">-- Chọn bưu cục --</option>';
            data.forEach(office => {
                html += `<option value="${office.id}" data-lat="${office.latitude}" data-lng="${office.longitude}">
                    ${office.name} - ${office.address}
                </option>`;
            });
            $('#postOfficeSelect').html(html);
        } else {
            $('#postOfficeSelect').html('<option value="">Không tìm thấy bưu cục gần đây</option>');
        }
    }).fail(function() {
        console.error('❌ Không thể tải bưu cục');
    });
}
</script>

<script src="{{ asset('assets2/js/customer/dashboard/orders/fetchNearbyPostOffices.js') }}"></script>
<script src="{{ asset('assets2/js/customer/dashboard/orders/handleImage.js') }}"></script>
@endsection