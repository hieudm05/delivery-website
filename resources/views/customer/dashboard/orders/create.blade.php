@extends('customer.dashboard.layouts.app')
@section('title', 'Tạo đơn hàng')

@section('content')
<style>
    .special-box {
      border: 1px solid #eee;
      border-radius: 10px;
      padding: 15px;
      background: #fafafa;
    }
    .address-saved-item {
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: all 0.3s;
    }
    .address-saved-item:hover {
      border-color: #dc3545;
      background: #fff5f5;
    }
    .address-saved-item.active {
      border-color: #dc3545;
      background: #fff5f5;
    }
    .quick-select-btn {
      font-size: 0.875rem;
      padding: 0.25rem 0.75rem;
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
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 10px;
      background: #fff;
    }
    .product-item .remove-btn {
      cursor: pointer;
      color: #dc3545;
    }
    /* AUTOCOMPLETE STYLES */
    #address-suggestions {
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      border: 1px solid #dee2e6;
      border-radius: 8px;
      margin-top: 2px;
    }
    #address-suggestions .list-group-item {
      border: none;
      border-bottom: 1px solid #f0f0f0;
      padding: 10px 15px;
      cursor: pointer;
    }
    #address-suggestions .list-group-item:hover {
      background-color: #f8f9fa;
    }
    #address-suggestions .list-group-item:last-child {
      border-bottom: none;
    }
    .address-input-wrapper {
      position: relative;
    }
</style>

<div class="container-fluid py-4">
  <form id="orderForm" method="POST" action="{{ route('customer.orders.store') }}">
    @csrf
    <input type="hidden" id="products_json" name="products_json">
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
                  <option value="account" 
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
                    <!-- Autocomplete suggestions -->
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

      <!-- CỘT PHẢI: THÔNG TIN HÀNG HÓA (giữ nguyên như cũ) -->
      <div class="col-lg-6">
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h5 class="mb-0">Thông tin hàng hoá</h5>
          </div>

          <div class="card-body">
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

            <div id="products-list" class="mb-3"></div>

            <div id="formBuuKien">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Tên hàng</label>
                  @if (!$products || $products->isEmpty())
                    <div class="alert alert-warning">
                      <a href="{{url('/customer/account/product')}}" class="alert-link">⚠️ Vui lòng thêm hàng hoá trước</a>
                    </div>
                  @else
                    <select class="form-select mb-3" id="product-select">
                      <option value="">-- Chọn hàng hoá --</option>
                      @foreach ($products as $product)
                        <option value="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-quantity="{{ $product->quantity ?? 1 }}"
                                data-weight="{{ $product->weight ?? 10 }}"
                                data-value="{{ $product->price ?? 10000 }}"
                                data-length="{{ $product->length ?? 0 }}"
                                data-width="{{ $product->width ?? 0 }}"
                                data-height="{{ $product->height ?? 0 }}">
                          {{ $product->name }}
                        </option>
                      @endforeach
                      <option value="custom">+ Nhập hàng mới</option>
                    </select>
                    <input type="text" class="form-control mb-3 d-none" id="custom-product-name" placeholder="Nhập tên hàng hoá mới">
                  @endif
                </div>

                <div class="col-md-4">
                  <label class="form-label">Số lượng</label>
                  <input type="number" class="form-control" id="quantity" value="1" min="1">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Khối lượng</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="weight" value="10" min="1">
                    <span class="input-group-text">g</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Giá trị (VNĐ)</label>
                  <input type="number" class="form-control" id="value" value="10000" min="0">
                </div>
              </div>

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

              <div class="mt-4 special-box">
                <h6 class="fw-bold mb-2"><i class="bi bi-box"></i> TÍNH CHẤT HÀNG HÓA ĐẶC BIỆT</h6>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="giaTriCao" value="high_value">
                      <label class="form-check-label" for="giaTriCao">Giá trị cao</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="quaKho" value="oversized">
                      <label class="form-check-label" for="quaKho">Quá khổ</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="deVo" value="fragile">
                      <label class="form-check-label" for="deVo">Dễ vỡ</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="chatLong" value="liquid">
                      <label class="form-check-label" for="chatLong">Chất lỏng</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="nguyenKhoi" value="bulk">
                      <label class="form-check-label" for="nguyenKhoi">Nguyên khối</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="pin" value="battery">
                      <label class="form-check-label" for="pin">Từ tính, Pin</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-3 text-end">
                <button type="button" class="btn btn-primary" id="addProductBtn">
                  <i class="bi bi-plus-circle"></i> Thêm hàng
                </button>
              </div>
            </div>

            <div id="formTaiLieu" class="d-none">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Tên tài liệu</label>
                  <input type="text" class="form-control" id="document-name" placeholder="Nhập tên tài liệu...">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Số lượng</label>
                  <input type="number" class="form-control" id="doc-quantity" value="1" min="1">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Khối lượng</label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="doc-weight" value="10" min="1">
                    <span class="input-group-text">g</span>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Giá trị (VNĐ)</label>
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
                <h6 class="fw-bold mb-2">TÍNH CHẤT HÀNG HÓA ĐẶC BIỆT</h6>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="taiLieuGiaTri" value="high_value">
                      <label class="form-check-label" for="taiLieuGiaTri">Giá trị cao</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="hoaDon" value="certificate">
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

            <div class="card mt-4">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-truck"></i> Dịch vụ cộng thêm</h6>
              </div>
              <div class="card-body">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="fast" id="fastService" name="services[]">
                  <label class="form-check-label" for="fastService">
                    Giao nhanh <span class="text-muted">(+15%)</span>
                  </label>
                </div>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="insurance" id="insuranceService" name="services[]">
                  <label class="form-check-label" for="insuranceService">
                    Bảo hiểm hàng hóa <span class="text-muted">(1% giá trị)</span>
                  </label>
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" value="cod" id="codService" name="services[]">
                  <label class="form-check-label" for="codService">
                    Dịch vụ thu hộ <span class="text-muted">(1.000đ + 1%)</span>
                  </label>
                </div>

                <div id="cod-amount-container" class="d-none mb-3">
                  <label class="form-label">Số tiền thu hộ (VNĐ)</label>
                  <input type="number" class="form-control" id="cod-amount" name="cod_amount" min="0" placeholder="Nhập số tiền cần thu">
                </div>

                <div class="cost-breakdown mt-3">
                  <div class="cost-item">
                    <span>Cước chính:</span>
                    <span id="baseCost">0 đ</span>
                  </div>
                  <div class="cost-item">
                    <span>Phụ phí:</span>
                    <span id="extraCost">0 đ</span>
                  </div>
                  <div class="cost-item">
                    <span>Tổng cước:</span>
                    <span id="tongCuoc" class="text-danger">0 đ</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label">Ghi chú</label>
              <textarea class="form-control" id="note" name="note" rows="3" placeholder="Nhập ghi chú cho đơn hàng (không bắt buộc)"></textarea>
            </div>

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
  // ⚠️ QUAN TRỌNG: Thay YOUR_GOONG_API_KEY bằng API key thật của bạn
const GOONG_API_KEY = '{{ config("services.goong.api_key") }}';

let vietnamData = [];
let savedAddresses = [];
let productsList = [];
let geocodeTimeout = null;
let autocompleteTimeout = null;

$(document).ready(function() {
    console.log('🚀 Khởi tạo form với Goong API');
    initializeForm();
    loadProvinces();
    setupEventHandlers();
    setDefaultDateTime();
    setupGoongAutocomplete();
});

function initializeForm() {
    console.log('📝 Form tạo đơn hàng đã sẵn sàng');
}

function setDefaultDateTime() {
    const now = new Date();
    now.setHours(now.getHours() + 2);
    const dateString = now.toISOString().slice(0, 16);
    $('#pickup-time, #delivery-time').val(dateString);
}

// ========== GOONG AUTOCOMPLETE ==========
function setupGoongAutocomplete() {
    console.log('🔍 Đã kích hoạt Goong Autocomplete');
    
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
    
    $('.list-group-item', '#address-suggestions').on('click', function() {
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
                        <i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ (Goong API)
                    </small>
                `);
                
                parseGoongAddress(result, description);
                
                console.log('✅ Đã chọn địa chỉ từ Goong:', {
                    lat: lat,
                    lng: lng,
                    address: description
                });
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

// ========== GEOCODING VỚI GOONG ==========
function fetchCoordinates(address) {
    console.log('🗺️ Lấy tọa độ cho địa chỉ:', address);
    
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
                        <i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ (Goong API)
                    </small>
                `);
                
                console.log('✅ Goong API tìm thấy:', {
                    lat: lat,
                    lng: lng,
                    formatted_address: result.formatted_address
                });
            } else {
                console.warn('⚠️ Goong API không tìm thấy kết quả');
                $('#geocode-status').html(`
                    <small class="text-warning">
                        <i class="bi bi-exclamation-triangle"></i> Không tìm thấy tọa độ chính xác
                    </small>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Goong API lỗi:', error);
            $('#geocode-status').html(`
                <small class="text-danger">
                    <i class="bi bi-x-circle"></i> Lỗi kết nối Goong API
                </small>
            `);
        }
    });
}

// ========== LOAD DỮ LIỆU ==========
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

function loadSavedAddresses() {
    $.get('{{ route("customer.orders.addresses.list") }}', function(data) {
        savedAddresses = data;
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
            <div class="address-saved-item" data-address='${JSON.stringify(addr)}'>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>${addr.recipient_name}</strong> - ${addr.recipient_phone}
                        <div class="text-muted small">${addr.full_address}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger quick-select-btn" onclick='selectSavedAddress(${JSON.stringify(addr)})'>Chọn</button>
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
    
    console.log('✅ Đã chọn địa chỉ:', addr.recipient_name);
}

// ========== XỬ LÝ NGƯỜI GỬI ==========
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
        
        if ($('#sameAsAccount').is(':checked')) {
            fetchNearbyPostOffices(parseFloat(lat), parseFloat(lng));
        }
        
        console.log('✅ Đã chọn người gửi:', name);
    } else {
        $('#sender-info').addClass('d-none');
        console.warn('⚠️ Không có tọa độ người gửi');
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

// ========== XỬ LÝ ĐỊA CHỈ NGƯỜI NHẬN ==========
$('#loadSavedAddress').on('click', function() {
    $('#saved-addresses-container').toggleClass('d-none');
    if (!$('#saved-addresses-container').hasClass('d-none')) {
        loadSavedAddresses();
    }
});

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

// ========== XỬ LÝ SẢN PHẨM ==========
$('#product-select').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const value = $(this).val();
    
    if (value === 'custom') {
        $('#custom-product-name').removeClass('d-none').focus();
        $('#quantity').val(1);
        $('#weight').val(10);
        $('#value').val(10000);
        $('#length, #width, #height').val('');
    } else if (value) {
        $('#custom-product-name').addClass('d-none');
        $('#quantity').val(selectedOption.data('quantity') || 1);
        $('#weight').val(selectedOption.data('weight') || 10);
        $('#value').val(selectedOption.data('value') || 10000);
        $('#length').val(selectedOption.data('length') || '');
        $('#width').val(selectedOption.data('width') || '');
        $('#height').val(selectedOption.data('height') || '');
        
        // ✅ TỰ ĐỘNG THÊM SẢN PHẨM VÀO DANH SÁCH
        setTimeout(() => {
            $('#addProductBtn').trigger('click');
        }, 100);
    } else {
        $('#custom-product-name').addClass('d-none');
    }
});

// ✅ TÍNH PREVIEW KHI THAY ĐỔI SỐ LƯỢNG/KHỐI LƯỢNG/GIÁ TRỊ (trước khi thêm)
$('#quantity, #weight, #value, #doc-quantity, #doc-weight, #doc-value').on('input', function() {
    if (productsList.length > 0) {
        // Đã có sản phẩm trong list → Không tính preview, chờ user nhấn "Thêm hàng"
        return;
    }
    
    // Chưa có sản phẩm → Hiển thị preview
    const weight = parseFloat($('#weight').val() || $('#doc-weight').val() || 0);
    const value = parseFloat($('#value').val() || $('#doc-value').val() || 0);
    const quantity = parseInt($('#quantity').val() || $('#doc-quantity').val() || 1);
    
    if (weight > 0) {
        calculatePreviewCost(weight * quantity, value * quantity);
    }
});

function calculatePreviewCost(totalWeight, totalValue) {
    const services = $('input[name="services[]"]:checked').map((_, e) => e.value).get();
    const codAmount = parseFloat($('#cod-amount').val()) || 0;
    
    const data = {
        weight: totalWeight,
        value: totalValue,
        length: 0,
        width: 0,
        height: 0,
        specials: [],
        services: services,
        cod_amount: codAmount,
        item_type: $('#buuKien').is(':checked') ? 'package' : 'document',
        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
    };
    
    console.log('👁️ Preview cước phí:', data);
    
    $.post('{{ route("customer.orders.calculate") }}', data)
        .done(function(res) {
            if (res && res.success === true) {
                $('#baseCost').text(res.base_cost.toLocaleString('vi-VN') + ' đ (dự kiến)');
                $('#extraCost').text(res.extra_cost.toLocaleString('vi-VN') + ' đ');
                $('#tongCuoc').text(res.total.toLocaleString('vi-VN') + ' đ');
            }
        })
        .fail(function() {
            console.warn('⚠️ Không thể tính preview');
        });
}

$('#addProductBtn').on('click', function() {
    const productSelect = $('#product-select').val();
    const customName = $('#custom-product-name').val().trim();
    
    let productName = '';
    if (productSelect === 'custom') {
        if (!customName) {
            alert('⚠️ Vui lòng nhập tên hàng hoá');
            $('#custom-product-name').focus();
            return;
        }
        productName = customName;
    } else if (productSelect) {
        productName = $('#product-select option:selected').data('name');
    } else {
        alert('⚠️ Vui lòng chọn hàng hoá');
        return;
    }
    
    const quantity = parseInt($('#quantity').val()) || 1;
    const weight = parseFloat($('#weight').val()) || 0;
    const value = parseFloat($('#value').val()) || 0;
    const length = parseFloat($('#length').val()) || 0;
    const width = parseFloat($('#width').val()) || 0;
    const height = parseFloat($('#height').val()) || 0;
    
    if (weight <= 0) {
        alert('⚠️ Khối lượng phải lớn hơn 0');
        $('#weight').focus();
        return;
    }
    
    const specials = [];
    $('#formBuuKien input[type="checkbox"]:checked').each(function() {
        specials.push($(this).val());
    });
    
    const product = {
        type: 'package',
        name: productName,
        quantity: quantity,
        weight: weight,
        value: value,
        length: length,
        width: width,
        height: height,
        specials: specials
    };
    
    productsList.push(product);
    console.log('✅ Đã thêm sản phẩm:', productName, '- Tổng:', productsList.length);
    console.log('📦 productsList:', productsList);
    
    renderProductsList();
    resetProductForm();
    calculateCost();
});

$('#addDocumentBtn').on('click', function() {
    const documentName = $('#document-name').val().trim();
    
    if (!documentName) {
        alert('⚠️ Vui lòng nhập tên tài liệu');
        $('#document-name').focus();
        return;
    }
    
    const quantity = parseInt($('#doc-quantity').val()) || 1;
    const weight = parseFloat($('#doc-weight').val()) || 0;
    const value = parseFloat($('#doc-value').val()) || 0;
    const length = parseFloat($('#doc-length').val()) || 0;
    const width = parseFloat($('#doc-width').val()) || 0;
    const height = parseFloat($('#doc-height').val()) || 0;
    
    if (weight <= 0) {
        alert('⚠️ Khối lượng phải lớn hơn 0');
        $('#doc-weight').focus();
        return;
    }
    
    const specials = [];
    $('#formTaiLieu input[type="checkbox"]:checked').each(function() {
        specials.push($(this).val());
    });
    
    const document = {
        type: 'document',
        name: documentName,
        quantity: quantity,
        weight: weight,
        value: value,
        length: length,
        width: width,
        height: height,
        specials: specials
    };
    
    productsList.push(document);
    console.log('✅ Đã thêm tài liệu:', documentName, '- Tổng:', productsList.length);
    
    renderProductsList();
    resetDocumentForm();
    calculateCost();
});

function renderProductsList() {
    if (productsList.length === 0) {
        $('#products-list').html('');
        return;
    }
    
    let html = '<div class="mb-3"><label class="form-label fw-bold">Danh sách hàng hóa đã thêm:</label></div>';
    
    productsList.forEach((item, index) => {
        const dimensionText = (item.length || item.width || item.height) 
            ? ` - ${item.length}×${item.width}×${item.height}cm` 
            : '';
        
        html += `
            <div class="product-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${item.name}</strong>
                        <div class="text-muted small">
                            ${item.type === 'package' ? '📦 Bưu kiện' : '📄 Tài liệu'} | 
                            SL: ${item.quantity} | 
                            KL: ${item.weight}g | 
                            GT: ${item.value.toLocaleString('vi-VN')}đ${dimensionText}
                        </div>
                        ${item.specials.length > 0 ? `<div class="text-danger small">⚠️ ${item.specials.join(', ')}</div>` : ''}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-btn" onclick="removeProduct(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    $('#products-list').html(html);
    $('#products_json').val(JSON.stringify(productsList));
}

function removeProduct(index) {
    if (confirm('Xóa hàng hóa này?')) {
        productsList.splice(index, 1);
        renderProductsList();
        calculateCost();
        console.log('🗑️ Đã xóa sản phẩm tại vị trí:', index);
    }
}

function resetProductForm() {
    $('#product-select').val('');
    $('#custom-product-name').val('').addClass('d-none');
    $('#quantity').val(1);
    $('#weight').val(10);
    $('#value').val(10000);
    $('#length, #width, #height').val('');
    $('#formBuuKien input[type="checkbox"]').prop('checked', false);
}

function resetDocumentForm() {
    $('#document-name').val('');
    $('#doc-quantity').val(1);
    $('#doc-weight').val(10);
    $('#doc-value').val(10000);
    $('#doc-length, #doc-width, #doc-height').val('');
    $('#formTaiLieu input[type="checkbox"]').prop('checked', false);
}

// ========== TOGGLE LOẠI HÀNG ==========
const buuKienRadio = document.getElementById('buuKien');
const taiLieuRadio = document.getElementById('taiLieu');
const formBuuKien = document.getElementById('formBuuKien');
const formTaiLieu = document.getElementById('formTaiLieu');

function toggleForms() {
    if (buuKienRadio.checked) {
        formBuuKien.classList.remove('d-none');
        formTaiLieu.classList.add('d-none');
    } else {
        formTaiLieu.classList.remove('d-none');
        formBuuKien.classList.add('d-none');
    }
}

buuKienRadio.addEventListener('change', toggleForms);
taiLieuRadio.addEventListener('change', toggleForms);

// ========== XỬ LÝ DỊCH VỤ COD ==========
$('#codService').on('change', function() {
    if ($(this).is(':checked')) {
        $('#cod-amount-container').removeClass('d-none');
    } else {
        $('#cod-amount-container').addClass('d-none');
        $('#cod-amount').val('');
    }
    calculateCost();
});

// ========== TÍNH CƯỚC PHÍ - FIXED VERSION ==========
function setupEventHandlers() {
    // ✅ Chỉ tính lại khi thay đổi services/COD VÀ đã có sản phẩm
    $('input[type=checkbox][name="services[]"]').on('change', function() {
        console.log('🔄 Service thay đổi');
        if (productsList.length > 0) {
            calculateCost();
        }
    });
    
    $('#cod-amount').on('input', function() {
        console.log('🔄 COD amount thay đổi');
        if (productsList.length > 0) {
            calculateCost();
        }
    });
}

function calculateCost() {
    console.log('🧮 calculateCost() được gọi. Số sản phẩm:', productsList.length);
    
    // ✅ Nếu chưa có sản phẩm, chỉ tính phí COD (nếu có)
    if (!productsList || productsList.length === 0) {
        console.log('⏭️ Chưa có sản phẩm trong productsList');
        
        const services = $('input[name="services[]"]:checked').map((_, e) => e.value).get();
        const codAmount = parseFloat($('#cod-amount').val()) || 0;
        
        let extraCost = 0;
        
        if (services.includes('cod') && codAmount > 0) {
            extraCost = 1000 + (codAmount * 0.01);
        }
        
        $('#baseCost').text('0 đ');
        $('#extraCost').text(extraCost.toLocaleString('vi-VN') + ' đ');
        $('#tongCuoc').text(extraCost.toLocaleString('vi-VN') + ' đ');
        
        return;
    }
    
    // ✅ Có sản phẩm rồi, tính đầy đủ
    let totalWeight = 0;
    let totalValue = 0;
    let allSpecials = [];
    
    productsList.forEach(item => {
        totalWeight += item.weight * item.quantity;
        totalValue += item.value * item.quantity;
        allSpecials = allSpecials.concat(item.specials);
    });
    
    allSpecials = [...new Set(allSpecials)];
    
    const services = $('input[name="services[]"]:checked').map((_, e) => e.value).get();
    const codAmount = parseFloat($('#cod-amount').val()) || 0;
    
    const data = {
        weight: totalWeight,
        value: totalValue,
        length: 0,
        width: 0,
        height: 0,
        specials: allSpecials,
        services: services,
        cod_amount: codAmount,
        item_type: productsList[0].type,
        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
    };
    
    console.log('💰 Gọi API tính cước với:', data);
    
    $.post('{{ route("customer.orders.calculate") }}', data)
        .done(function(res) {
            console.log('📩 Response:', res);
            
            if (res && res.success === true) {
                $('#baseCost').text(res.base_cost.toLocaleString('vi-VN') + ' đ');
                $('#extraCost').text(res.extra_cost.toLocaleString('vi-VN') + ' đ');
                $('#tongCuoc').text(res.total.toLocaleString('vi-VN') + ' đ');
                console.log('✅ Cập nhật cước phí thành công');
            } else {
                console.error('❌ Response không hợp lệ:', res);
                $('#baseCost').text('Lỗi');
                $('#extraCost').text('Lỗi');
                $('#tongCuoc').text('Lỗi');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('❌ AJAX Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            
            $('#baseCost').text('Lỗi API');
            $('#extraCost').text('Lỗi API');
            $('#tongCuoc').text('Lỗi API');
        });
}

// ========== XỬ LÝ SUBMIT FORM ==========
// $('#orderForm').on('submit', function(e) {
//     e.preventDefault();
    
//     if (!validateForm()) {
//         return false;
//     }
    
//     $('#submitOrder').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
    
//     const formData = new FormData(this);
//     formData.append('products', JSON.stringify(productsList));
    
//     console.log('📦 Gửi đơn hàng với', productsList.length, 'sản phẩm');
//     console.log('📦 productsList data:', productsList);
    
//     $.ajax({
//         url: $(this).attr('action'),
//         method: 'POST',
//         data: formData,
//         processData: false,
//         contentType: false,
//         success: function(res) {
//             if (res.success) {
//                 alert('✅ Tạo đơn hàng thành công!');
//                 window.location.href = '{{ route("customer.orders.create") }}';
//             } else {
//                 alert('❌ Lỗi: ' + (res.message || 'Không thể tạo đơn hàng'));
//                 $('#submitOrder').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Tạo đơn hàng');
//             }
//         },
//         error: function(xhr) {
//             let errorMsg = 'Không thể tạo đơn hàng';
//             if (xhr.responseJSON && xhr.responseJSON.message) {
//                 errorMsg = xhr.responseJSON.message;
//             } else if (xhr.responseText) {
//                 try {
//                     const response = JSON.parse(xhr.responseText);
//                     errorMsg = response.message || errorMsg;
//                 } catch (e) {
//                     console.error('Parse error:', e);
//                 }
//             }
//             alert('❌ ' + errorMsg);
//             console.error('Submit error:', xhr);
//             $('#submitOrder').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Tạo đơn hàng');
//         }
//     });
// });

function validateForm() {
    console.log('🔍 Validate form - productsList:', productsList);
    
    if (!$('#sender-select').val()) {
        alert('⚠️ Vui lòng chọn thông tin người gửi');
        $('#sender-select').focus();
        return false;
    }
    
    if (!$('#recipientName').val().trim()) {
        alert('⚠️ Vui lòng nhập tên người nhận');
        $('#recipientName').focus();
        return false;
    }
    
    if (!$('#recipientPhone').val().trim()) {
        alert('⚠️ Vui lòng nhập số điện thoại người nhận');
        $('#recipientPhone').focus();
        return false;
    }
    
    const phonePattern = /^(0|\+84)[0-9]{9,10}$/;
    if (!phonePattern.test($('#recipientPhone').val().trim())) {
        alert('⚠️ Số điện thoại không hợp lệ');
        $('#recipientPhone').focus();
        return false;
    }
    
    if (!$('#province-select').val()) {
        alert('⚠️ Vui lòng chọn Tỉnh/Thành phố');
        $('#province-select').focus();
        return false;
    }
    
    if (!$('#district-select').val()) {
        alert('⚠️ Vui lòng chọn Quận/Huyện');
        $('#district-select').focus();
        return false;
    }
    
    if (!$('#ward-select').val()) {
        alert('⚠️ Vui lòng chọn Phường/Xã');
        $('#ward-select').focus();
        return false;
    }
    
    if (!$('#address-detail').val().trim()) {
        alert('⚠️ Vui lòng nhập số nhà, tên đường');
        $('#address-detail').focus();
        return false;
    }
    
    // ✅ KIỂM TRA SẢN PHẨM
    if (!productsList || productsList.length === 0) {
        alert('⚠️ Vui lòng thêm ít nhất 1 hàng hóa');
        console.error('❌ productsList:', productsList);
        return false;
    }
    
    for (let i = 0; i < productsList.length; i++) {
        const item = productsList[i];
        if (!item.name || !item.weight || item.weight <= 0) {
            alert(`⚠️ Hàng hoá #${i + 1} không hợp lệ`);
            return false;
        }
    }
    
    if (!$('#pickup-time').val()) {
        alert('⚠️ Vui lòng chọn thời gian hẹn lấy hàng');
        $('#pickup-time').focus();
        return false;
    }
    
    if (!$('#delivery-time').val()) {
        alert('⚠️ Vui lòng chọn thời gian hẹn giao');
        $('#delivery-time').focus();
        return false;
    }
    
    console.log('✅ Validate thành công!');
    return true;
}

function fetchNearbyPostOffices(lat, lng) {
    console.log('🏢 Tìm bưu cục gần:', { lat, lng });
    
    $.get('{{ route("customer.orders.getNearby") }}', {
        latitude: lat,
        longitude: lng,
        limit: 5
    }, function(data) {
        if (data && data.length > 0) {
            let html = '<option value="">-- Chọn bưu cục --</option>';
            data.forEach(office => {
                html += `<option value="${office.id}" data-lat="${office.latitude}" data-lng="${office.longitude}">
                    ${office.name} - ${office.address} (${office.distance.toFixed(2)} km)
                </option>`;
            });
            $('#postOfficeSelect').html(html);
            console.log('✅ Tìm thấy', data.length, 'bưu cục');
        } else {
            $('#postOfficeSelect').html('<option value="">Không tìm thấy bưu cục gần đây</option>');
        }
    }).fail(function() {
        console.error('❌ Không thể tải bưu cục');
    });
}
</script>

<script src="{{ asset('assets2/js/customer/dashboard/orders/fetchNearbyPostOffices.js') }}"></script>
@endsection