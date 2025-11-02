@extends('customer.dashboard.layouts.app')
@section('title', 'Tạo đơn hàng')

@section('content')
<link rel="stylesheet" href="{{ asset('assets2/css/customer/dashboard/orders/style.css') }}">

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
  .product-item .remove-btn {
    cursor: pointer;
    color: #dc3545;
  }
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
  .address-input-wrapper {
    position: relative;
  }
  .product-input-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
  }
  .product-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    background: #fff;
  }
  .image-preview-item {
    position: relative;
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
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
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    line-height: 1;
    transition: all 0.2s;
  }
  .image-preview-item .remove-image:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
  }
  .image-preview-item .image-note {
    padding: 5px;
    background: white;
    font-size: 12px;
  }
  .image-preview-item .image-note input {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 11px;
  }
  
  /* NEW STYLES FOR MULTI-RECIPIENT */
  .recipient-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    background: #ffffff;
    position: relative;
    transition: all 0.3s;
  }
  .recipient-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: #dc3545;
  }
  .recipient-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
  }
  .recipient-number {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 14px;
  }
  .remove-recipient-btn {
    background: #fff;
    border: 2px solid #dc3545;
    color: #dc3545;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
  }
  .remove-recipient-btn:hover {
    background: #dc3545;
    color: white;
  }
  .add-recipient-btn {
    width: 100%;
    padding: 15px;
    border: 2px dashed #dc3545;
    background: #fff;
    color: #dc3545;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s;
    cursor: pointer;
  }
  .add-recipient-btn:hover {
    background: #dc3545;
    color: white;
    border-style: solid;
  }
  .recipients-summary {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
  }
  .summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
  }
  .summary-item strong {
    color: #495057;
  }
  .summary-total {
    border-top: 2px solid #dee2e6;
    margin-top: 10px;
    padding-top: 10px;
    font-size: 16px;
    font-weight: bold;
    color: #dc3545;
  }
  .order-mode-selector {
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
  }
  .mode-option {
    flex: 1;
    padding: 15px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #fff;
  }
  .mode-option:hover {
    border-color: #dc3545;
    background: #fff5f5;
  }
  .mode-option.active {
    border-color: #dc3545;
    background: linear-gradient(135deg, #fff5f5, #ffe5e5);
    font-weight: bold;
  }
  .mode-option i {
    font-size: 24px;
    display: block;
    margin-bottom: 8px;
  }
  
  /* SHARED PRODUCTS SECTION */
  .shared-products-section {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: 2px solid #2196f3;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
  }
  .shared-products-section h5 {
    color: #1976d2;
    margin-bottom: 15px;
  }
</style>

<div class="container-fluid py-4">
  <!-- ORDER MODE SELECTOR -->
  <div class="order-mode-selector">
    <h6 class="mb-3"><i class="bi bi-gear"></i> Chế độ tạo đơn</h6>
    <div class="d-flex gap-3">
      <div class="mode-option active" data-mode="single">
        <i class="bi bi-person text-primary"></i>
        <div>Đơn đơn giản</div>
        <small class="text-muted">1 người gửi → 1 người nhận</small>
      </div>
      <div class="mode-option" data-mode="multi">
        <i class="bi bi-people text-danger"></i>
        <div>Đơn nhiều người</div>
        <small class="text-muted">1 bưu kiện/tài liệu → Nhiều người nhận</small>
      </div>
    </div>
  </div>

  <form id="orderForm" method="POST" action="{{ route('customer.orders.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="order_mode" name="order_mode" value="single">
    <input type="hidden" id="pickup_time_formatted" name="pickup_time_formatted">

    <div class="row">
      <!-- CỘT TRÁI: THÔNG TIN NGƯỜI GỬI + HÀNG HÓA CHUNG -->
      <div class="col-lg-5">
        <!-- NGƯỜI GỬI -->
        <div class="card mb-4">
          <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="bi bi-box-seam"></i> Thông tin người gửi</h6>
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

            <div class="mt-3">
              <label class="form-label">Ghi chú chung (áp dụng cho tất cả đơn)</label>
              <textarea class="form-control" id="common-note" name="note" rows="2" placeholder="Ghi chú chung cho tất cả người nhận..."></textarea>
            </div>
          </div>
        </div>

        <!-- SHARED PRODUCTS SECTION (Only in multi mode) -->
        <div class="shared-products-section" id="shared-products-section" style="display:none;">
          <h5><i class="bi bi-box-seam-fill"></i> Thông tin hàng hóa chung</h5>
          <p class="text-muted small mb-3">Thông tin này sẽ được áp dụng cho tất cả người nhận</p>
          
          <div class="mb-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="shared_item_type" id="shared-package" value="package" checked>
              <label class="form-check-label text-danger fw-bold" for="shared-package">Bưu kiện</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="shared_item_type" id="shared-document" value="document">
              <label class="form-check-label text-danger fw-bold" for="shared-document">Tài liệu</label>
            </div>
          </div>
          
          <!-- FORM BƯU KIỆN CHUNG -->
          <div id="shared-package-form">
            <div class="product-input-section">
              <h6 class="fw-bold mb-3">Thông tin bưu kiện</h6>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tên hàng <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="shared-product-name" placeholder="VD: Áo thun, Sách, Điện thoại...">
                </div>
                <div class="col-4">
                  <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="shared-product-quantity" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="shared-product-weight" value="10" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="shared-product-value" value="10000" min="0">
                </div>
              </div>
              
              <div class="row mt-2">
                <div class="col-12 mb-2">
                  <label class="form-label">Kích thước (không bắt buộc)</label>
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="shared-product-length" placeholder="Dài (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="shared-product-width" placeholder="Rộng (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="shared-product-height" placeholder="Cao (cm)" min="0">
                </div>
              </div>
              
              <div class="mt-3 special-box">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-diamond"></i> Tính chất hàng hóa</h6>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input shared-special-checkbox" type="checkbox" id="shared-high-value" value="high_value">
                      <label class="form-check-label" for="shared-high-value">Giá trị cao</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input shared-special-checkbox" type="checkbox" id="shared-oversized" value="oversized">
                      <label class="form-check-label" for="shared-oversized">Quá khổ</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input shared-special-checkbox" type="checkbox" id="shared-fragile" value="fragile">
                      <label class="form-check-label" for="shared-fragile">Dễ vỡ</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input shared-special-checkbox" type="checkbox" id="shared-liquid" value="liquid">
                      <label class="form-check-label" for="shared-liquid">Chất lỏng</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input shared-special-checkbox" type="checkbox" id="shared-bulk" value="bulk">
                      <label class="form-check-label" for="shared-bulk">Nguyên khối</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input shared-special-checkbox" type="checkbox" id="shared-battery" value="battery">
                      <label class="form-check-label" for="shared-battery">Từ tính, Pin</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- FORM TÀI LIỆU CHUNG -->
          <div id="shared-document-form" style="display:none;">
            <div class="product-input-section">
              <h6 class="fw-bold mb-3">Thông tin tài liệu</h6>
              <div class="row g-2">
                <div class="col-12">
                  <label class="form-label">Tên tài liệu <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="shared-document-name" placeholder="VD: Hóa đơn, Giấy chứng chỉ...">
                </div>
                <div class="col-4">
                  <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="shared-document-quantity" value="1" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="shared-document-weight" value="10" min="1">
                </div>
                <div class="col-4">
                  <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="shared-document-value" value="10000" min="0">
                </div>
              </div>
              
              <div class="row mt-2">
                <div class="col-12 mb-2">
                  <label class="form-label">Kích thước (không bắt buộc)</label>
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="shared-document-length" placeholder="Dài (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="shared-document-width" placeholder="Rộng (cm)" min="0">
                </div>
                <div class="col-4">
                  <input type="number" class="form-control" id="shared-document-height" placeholder="Cao (cm)" min="0">
                </div>
              </div>
              
              <div class="mt-3 special-box">
                <h6 class="fw-bold mb-2">Tính chất tài liệu</h6>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-check">
                      <input class="form-check-input shared-doc-special-checkbox" type="checkbox" id="shared-doc-high-value" value="high_value">
                      <label class="form-check-label" for="shared-doc-high-value">Giá trị cao</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-check">
                      <input class="form-check-input shared-doc-special-checkbox" type="checkbox" id="shared-doc-certificate" value="certificate">
                      <label class="form-check-label" for="shared-doc-certificate">Hóa đơn, Giấy chứng nhận</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CỘT PHẢI: DANH SÁCH NGƯỜI NHẬN -->
      <div class="col-lg-7">
        <!-- RECIPIENTS SUMMARY (Only show in multi mode) -->
        <div id="recipients-summary" class="recipients-summary" style="display:none;">
          <h6 class="mb-2"><i class="bi bi-list-check"></i> Tổng quan đơn hàng</h6>
          <div class="summary-item">
            <span>Số người nhận:</span>
            <strong id="total-recipients">0</strong>
          </div>
          <div class="summary-item">
            <span>Tổng cước phí:</span>
            <strong id="total-shipping-summary">0 đ</strong>
          </div>
          <div class="summary-item">
            <span>Tổng phí COD:</span>
            <strong id="total-cod-summary">0 đ</strong>
          </div>
          <div class="summary-item summary-total">
            <span>Người gửi trả:</span>
            <strong id="total-sender-summary">0 đ</strong>
          </div>
          <div class="summary-item">
            <span>Người nhận trả:</span>
            <strong id="total-recipient-summary">0 đ</strong>
          </div>
        </div>

        <!-- RECIPIENTS CONTAINER -->
        <div id="recipients-container">
          <!-- Recipient cards will be dynamically added here -->
        </div>

        <!-- ADD RECIPIENT BUTTON (Only in multi mode) -->
        <button type="button" class="add-recipient-btn" id="addRecipientBtn" style="display:none;">
          <i class="bi bi-plus-circle me-2"></i> Thêm người nhận
        </button>

        <!-- SUBMIT BUTTONS -->
        <div class="mt-4 text-end">
          <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Hủy</button>
          <button type="submit" class="btn btn-danger btn-lg" id="submitOrder">
            <i class="bi bi-check-circle"></i> Tạo đơn hàng
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
let recipientsList = [];
let currentRecipientIndex = 0;
let geocodeTimeout = null;
let autocompleteTimeout = null;
let orderMode = 'single'; // 'single' or 'multi'
let sharedProductData = null; // Store shared product data

$(document).ready(function() {
   loadProvinces().then(() => {
        setupEventHandlers();
        setDefaultDateTime();
        setupGoongAutocomplete();
        setupToggleForms();
        setupModeSelector();
        setupSharedProductForm();
        addRecipient();
    });
});

// ============ MODE SELECTOR ============
function setupModeSelector() {
    $('.mode-option').on('click', function() {
        $('.mode-option').removeClass('active');
        $(this).addClass('active');
        
        orderMode = $(this).data('mode');
        $('#order_mode').val(orderMode);
        
        if (orderMode === 'multi') {
            $('#recipients-summary').show();
            $('#addRecipientBtn').show();
            $('#shared-products-section').show();
        } else {
            $('#recipients-summary').hide();
            $('#addRecipientBtn').hide();
            $('#shared-products-section').hide();
            
            // Keep only first recipient in single mode
            if (recipientsList.length > 1) {
                recipientsList = [recipientsList[0]];
                renderRecipients();
            }
        }
        
        console.log('📋 Chế độ:', orderMode);
    });
}

// ============ SHARED PRODUCT FORM ============
function setupSharedProductForm() {
    // Toggle between package and document
    $('input[name="shared_item_type"]').on('change', function() {
        const itemType = $(this).val();
        if (itemType === 'package') {
            $('#shared-package-form').show();
            $('#shared-document-form').hide();
        } else {
            $('#shared-package-form').hide();
            $('#shared-document-form').show();
        }
        updateSharedProductData();
    });
    
    // Update shared product data when any field changes
    $('#shared-products-section input').on('change input', function() {
        updateSharedProductData();
    });
}

function updateSharedProductData() {
    if (orderMode !== 'multi') return;
    
    const itemType = $('input[name="shared_item_type"]:checked').val();
    
    if (itemType === 'package') {
        const specials = [];
        $('.shared-special-checkbox:checked').each(function() {
            specials.push($(this).val());
        });
        
        sharedProductData = {
            type: 'package',
            name: $('#shared-product-name').val().trim(),
            quantity: parseInt($('#shared-product-quantity').val()) || 1,
            weight: parseFloat($('#shared-product-weight').val()) || 0,
            value: parseFloat($('#shared-product-value').val()) || 0,
            length: parseFloat($('#shared-product-length').val()) || 0,
            width: parseFloat($('#shared-product-width').val()) || 0,
            height: parseFloat($('#shared-product-height').val()) || 0,
            specials: specials
        };
    } else {
        const specials = [];
        $('.shared-doc-special-checkbox:checked').each(function() {
            specials.push($(this).val());
        });
        
        sharedProductData = {
            type: 'document',
            name: $('#shared-document-name').val().trim(),
            quantity: parseInt($('#shared-document-quantity').val()) || 1,
            weight: parseFloat($('#shared-document-weight').val()) || 0,
            value: parseFloat($('#shared-document-value').val()) || 0,
            length: parseFloat($('#shared-document-length').val()) || 0,
            width: parseFloat($('#shared-document-width').val()) || 0,
            height: parseFloat($('#shared-document-height').val()) || 0,
            specials: specials
        };
    }
    
    console.log('📦 Cập nhật thông tin hàng chung:', sharedProductData);
    
    // Recalculate all recipients' costs
    recipientsList.forEach(recipient => {
        calculateCost(recipient.id);
    });
}

// ============ RECIPIENT MANAGEMENT ============
function addRecipient() {
    const recipientId = currentRecipientIndex++;
    const recipient = {
        id: recipientId,
        products: [],
        selectedImages: [],
        data: {}
    };
    
    recipientsList.push(recipient);
    renderRecipients();
    
    console.log('➕ Đã thêm người nhận #' + recipientId);
}

$('#addRecipientBtn').on('click', function() {
    addRecipient();
});

function removeRecipient(recipientId) {
    if (recipientsList.length <= 1) {
        alert('⚠️ Phải có ít nhất 1 người nhận');
        return;
    }
    
    if (confirm('Xóa người nhận này?')) {
        recipientsList = recipientsList.filter(r => r.id !== recipientId);
        renderRecipients();
        updateSummary();
        console.log('🗑️ Đã xóa người nhận #' + recipientId);
    }
}

function renderRecipients() {
    const container = $('#recipients-container');
    
    recipientsList.forEach((recipient, index) => {
        const existingCard = $(`.recipient-card[data-recipient-id="${recipient.id}"]`);
        
        if (existingCard.length > 0) {
            // Update existing card's header
            existingCard.find('.recipient-number').text(`Người nhận #${index + 1}`);
        } else {
            // Create new card
            const html = createRecipientCard(recipient, index);
            container.append(html);
        }
    });
    
    // Remove cards that no longer exist
    $('.recipient-card').each(function() {
        const cardId = $(this).data('recipient-id');
        if (!recipientsList.find(r => r.id === cardId)) {
            $(this).remove();
        }
    });
    
    // Re-setup event handlers for new elements
    setupRecipientEventHandlers();
    updateSummary();
}

function createRecipientCard(recipient, index) {
    const canRemove = recipientsList.length > 1 && orderMode === 'multi';
    const showProductSection = orderMode === 'single';
    
    return `
        <div class="recipient-card" data-recipient-id="${recipient.id}">
            <div class="recipient-card-header">
                <span class="recipient-number">Người nhận #${index + 1}</span>
                ${canRemove ? `<button type="button" class="remove-recipient-btn" onclick="removeRecipient(${recipient.id})">
                    <i class="bi bi-trash"></i> Xóa
                </button>` : ''}
            </div>
            
            <div class="row">
                <!-- THÔNG TIN NGƯỜI NHẬN -->
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3"><i class="bi bi-person"></i> Thông tin người nhận</h6>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary load-saved-address" data-recipient-id="${recipient.id}">
                            <i class="bi bi-bookmark"></i> Địa chỉ đã lưu
                        </button>
                    </div>
                    
                    <div class="saved-addresses-container-${recipient.id} mb-3 d-none">
                        <div class="mb-2">
                            <small class="text-muted">Chọn địa chỉ đã lưu:</small>
                        </div>
                        <div class="saved-addresses-list-${recipient.id}"></div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                        <input type="text" class="form-control recipient-name" data-recipient-id="${recipient.id}" 
                               name="recipients[${recipient.id}][recipient_name]" placeholder="Nhập tên người nhận" required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" class="form-control recipient-phone" data-recipient-id="${recipient.id}"
                               name="recipients[${recipient.id}][recipient_phone]" placeholder="Nhập số điện thoại" required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-12">
                                <select class="form-select province-select" data-recipient-id="${recipient.id}"
                                        name="recipients[${recipient.id}][province_code]" required>
                                    <option value="">Tỉnh/Thành phố</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <select class="form-select district-select" data-recipient-id="${recipient.id}"
                                        name="recipients[${recipient.id}][district_code]" required disabled>
                                    <option value="">Quận/Huyện</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <select class="form-select ward-select" data-recipient-id="${recipient.id}"
                                        name="recipients[${recipient.id}][ward_code]" required disabled>
                                    <option value="">Phường/Xã</option>
                                </select>
                            </div>
                            <div class="col-12 address-input-wrapper">
                                <input type="text" class="form-control address-detail" data-recipient-id="${recipient.id}"
                                       name="recipients[${recipient.id}][address_detail]" placeholder="Số nhà, tên đường..." required autocomplete="off">
                                <div class="address-suggestions-${recipient.id} list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Địa chỉ đầy đủ</label>
                        <div class="p-2 bg-light rounded">
                            <small class="full-address-${recipient.id} text-muted">Chưa có địa chỉ đầy đủ</small>
                        </div>
                        <input type="hidden" name="recipients[${recipient.id}][recipient_latitude]" class="recipient-lat-${recipient.id}">
                        <input type="hidden" name="recipients[${recipient.id}][recipient_longitude]" class="recipient-lng-${recipient.id}">
                        <input type="hidden" name="recipients[${recipient.id}][recipient_full_address]" class="recipient-full-address-${recipient.id}">
                        <div class="geocode-status-${recipient.id} mt-1">
                            <small class="text-muted">Chưa tìm tọa độ</small>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Thời gian giao <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control delivery-time" data-recipient-id="${recipient.id}"
                               name="recipients[${recipient.id}][delivery_time_formatted]" required>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="recipients[${recipient.id}][save_address]">
                        <label class="form-check-label">Lưu địa chỉ này</label>
                    </div>
                </div>
                
                <!-- HÀNG HÓA & DỊCH VỤ -->
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3"><i class="bi bi-box"></i> ${showProductSection ? 'Hàng hóa' : 'Dịch vụ & Chi phí'}</h6>
                    
                    ${showProductSection ? `
                    <div class="mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input item-type" type="radio" name="recipients[${recipient.id}][item_type]" value="package" data-recipient-id="${recipient.id}" checked>
                            <label class="form-check-label text-danger">Bưu kiện</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input item-type" type="radio" name="recipients[${recipient.id}][item_type]" value="document" data-recipient-id="${recipient.id}">
                            <label class="form-check-label text-danger">Tài liệu</label>
                        </div>
                    </div>
                    
                    <!-- FORM BƯU KIỆN -->
                    <div class="product-input-section form-package-${recipient.id}">
                        <h6 class="fw-bold mb-3">Thêm bưu kiện</h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Tên hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control product-name-${recipient.id}" placeholder="VD: Áo thun, Sách, Điện thoại...">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" class="form-control product-quantity-${recipient.id}" value="1" min="1">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control product-weight-${recipient.id}" value="10" min="1">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control product-value-${recipient.id}" value="10000" min="0">
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-12 mb-2">
                                <label class="form-label">Kích thước (không bắt buộc)</label>
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control product-length-${recipient.id}" placeholder="Dài (cm)" min="0">
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control product-width-${recipient.id}" placeholder="Rộng (cm)" min="0">
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control product-height-${recipient.id}" placeholder="Cao (cm)" min="0">
                            </div>
                        </div>
                        
                        <div class="mt-3 special-box">
                            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-diamond"></i> Tính chất hàng hóa</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input special-checkbox-${recipient.id}" type="checkbox" id="giaTriCao-${recipient.id}" value="high_value">
                                        <label class="form-check-label" for="giaTriCao-${recipient.id}">Giá trị cao</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input special-checkbox-${recipient.id}" type="checkbox" id="quaKho-${recipient.id}" value="oversized">
                                        <label class="form-check-label" for="quaKho-${recipient.id}">Quá khổ</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input special-checkbox-${recipient.id}" type="checkbox" id="deVo-${recipient.id}" value="fragile">
                                        <label class="form-check-label" for="deVo-${recipient.id}">Dễ vỡ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input special-checkbox-${recipient.id}" type="checkbox" id="chatLong-${recipient.id}" value="liquid">
                                        <label class="form-check-label" for="chatLong-${recipient.id}">Chất lỏng</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input special-checkbox-${recipient.id}" type="checkbox" id="nguyenKhoi-${recipient.id}" value="bulk">
                                        <label class="form-check-label" for="nguyenKhoi-${recipient.id}">Nguyên khối</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input special-checkbox-${recipient.id}" type="checkbox" id="pin-${recipient.id}" value="battery">
                                        <label class="form-check-label" for="pin-${recipient.id}">Từ tính, Pin</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-primary mt-3 add-product-btn" data-recipient-id="${recipient.id}">
                            <i class="bi bi-plus-circle"></i> Thêm hàng
                        </button>
                    </div>
                    
                    <!-- FORM TÀI LIỆU -->
                    <div class="product-input-section form-document-${recipient.id} d-none">
                        <h6 class="fw-bold mb-3">Thêm tài liệu</h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Tên tài liệu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control document-name-${recipient.id}" placeholder="VD: Hóa đơn, Giấy chứng chỉ...">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" class="form-control document-quantity-${recipient.id}" value="1" min="1">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Khối lượng (g) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control document-weight-${recipient.id}" value="10" min="1">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Giá trị (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control document-value-${recipient.id}" value="10000" min="0">
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-12 mb-2">
                                <label class="form-label">Kích thước (không bắt buộc)</label>
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control document-length-${recipient.id}" placeholder="Dài (cm)" min="0">
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control document-width-${recipient.id}" placeholder="Rộng (cm)" min="0">
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control document-height-${recipient.id}" placeholder="Cao (cm)" min="0">
                            </div>
                        </div>
                        
                        <div class="mt-3 special-box">
                            <h6 class="fw-bold mb-2">Tính chất tài liệu</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input doc-special-checkbox-${recipient.id}" type="checkbox" id="taiLieuGiaTri-${recipient.id}" value="high_value">
                                        <label class="form-check-label" for="taiLieuGiaTri-${recipient.id}">Giá trị cao</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input doc-special-checkbox-${recipient.id}" type="checkbox" id="hoaDon-${recipient.id}" value="certificate">
                                        <label class="form-check-label" for="hoaDon-${recipient.id}">Hóa đơn, Giấy chứng nhận</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-primary mt-3 add-document-btn" data-recipient-id="${recipient.id}">
                            <i class="bi bi-plus-circle"></i> Thêm tài liệu
                        </button>
                    </div>
                    
                    <div class="products-list-${recipient.id} mb-3"></div>
                    ` : ''}
                    
                    <h6 class="fw-bold mb-2"><i class="bi bi-truck"></i> Dịch vụ</h6>
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input service-checkbox" type="checkbox" value="fast" data-recipient-id="${recipient.id}" name="recipients[${recipient.id}][services][]">
                            <label class="form-check-label">Giao nhanh <span class="text-muted">(+15%)</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input service-checkbox" type="checkbox" value="insurance" data-recipient-id="${recipient.id}" name="recipients[${recipient.id}][services][]">
                            <label class="form-check-label">Bảo hiểm <span class="text-muted">(1%)</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input service-checkbox cod-checkbox" type="checkbox" value="cod" data-recipient-id="${recipient.id}" name="recipients[${recipient.id}][services][]">
                            <label class="form-check-label">COD <span class="text-muted">(1.000đ + 1%)</span></label>
                        </div>
                    </div>
                    
                    <div class="cod-amount-container-${recipient.id} d-none mb-2">
                        <label class="form-label">Số tiền COD (VNĐ)</label>
                        <input type="number" class="form-control cod-amount" data-recipient-id="${recipient.id}" name="recipients[${recipient.id}][cod_amount]" min="0" placeholder="Nhập số tiền">
                    </div>
                    
                    <div class="mb-2 p-2 bg-light border rounded">
                        <label class="form-label fw-bold">Người trả cước <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input payer-radio" type="radio" name="recipients[${recipient.id}][payer]" value="sender" data-recipient-id="${recipient.id}" checked>
                                <label class="form-check-label">Người gửi</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input payer-radio" type="radio" name="recipients[${recipient.id}][payer]" value="recipient" data-recipient-id="${recipient.id}">
                                <label class="form-check-label">Người nhận</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cost-breakdown">
                        <div class="cost-item">
                            <span>Cước chính:</span>
                            <span class="base-cost-${recipient.id}">0 đ</span>
                        </div>
                        <div class="cost-item">
                            <span>Phụ phí:</span>
                            <span class="extra-cost-${recipient.id}">0 đ</span>
                        </div>
                        <div class="cost-item cod-fee-row-${recipient.id}" style="display:none;">
                            <span>Phí COD:</span>
                            <span class="cod-fee-${recipient.id} text-warning">0 đ</span>
                        </div>
                        <div class="cost-item">
                            <span>Tổng cước:</span>
                            <span class="total-cost-${recipient.id} text-danger fw-bold">0 đ</span>
                        </div>
                        <hr>
                        <div class="cost-item">
                            <span><strong>Người gửi trả:</strong></span>
                            <span class="sender-pays-${recipient.id} text-primary fw-bold">0 đ</span>
                        </div>
                        <div class="cost-item">
                            <span><strong>Người nhận trả:</strong></span>
                            <span class="recipient-pays-${recipient.id} text-success fw-bold">0 đ</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label">Hình ảnh (tối đa 5)</label>
                        <input type="file" class="form-control order-images" data-recipient-id="${recipient.id}" accept="image/*" multiple>
                        <small class="text-muted">JPG, PNG, GIF - Max 5MB/ảnh</small>
                    </div>
                    
                    <div class="image-preview-container-${recipient.id} row g-2 mt-2"></div>
                    
                    <div class="mt-2">
                        <label class="form-label">Ghi chú riêng</label>
                        <textarea class="form-control" name="recipients[${recipient.id}][note]" rows="2" placeholder="Ghi chú cho người nhận này..."></textarea>
                    </div>
                    
                    <input type="hidden" name="recipients[${recipient.id}][products_json]" class="products-json-${recipient.id}">
                </div>
            </div>
        </div>
    `;
}

// ============ SETUP EVENT HANDLERS FOR RECIPIENTS ============
function setupRecipientEventHandlers() {
    // Province/District/Ward selects
    $('.province-select').each(function() {
        const recipientId = $(this).data('recipient-id');
        if ($(this).find('option').length <= 1) {
            populateProvinceSelect(recipientId);
        }
    });
    
    $('.province-select').off('change').on('change', function() {
        const recipientId = $(this).data('recipient-id');
        handleProvinceChange(recipientId);
    });
    
    $('.district-select').off('change').on('change', function() {
        const recipientId = $(this).data('recipient-id');
        handleDistrictChange(recipientId);
    });
    
    $('.ward-select, .address-detail').off('change keyup').on('change keyup', function() {
        const recipientId = $(this).data('recipient-id');
        updateFullAddress(recipientId);
    });
    
    // Address autocomplete
    $('.address-detail').off('input').on('input', function() {
        const recipientId = $(this).data('recipient-id');
        const query = $(this).val().trim();
        
        if (autocompleteTimeout) clearTimeout(autocompleteTimeout);
        
        if (query.length < 3) {
            $(`.address-suggestions-${recipientId}`).hide().html('');
            return;
        }
        
        autocompleteTimeout = setTimeout(() => {
            goongAutocomplete(query, recipientId);
        }, 500);
    });
    
    // Products (only in single mode)
    $('.add-product-btn').off('click').on('click', function() {
        const recipientId = $(this).data('recipient-id');
        addProduct(recipientId);
    });
    
    $('.add-document-btn').off('click').on('click', function() {
        const recipientId = $(this).data('recipient-id');
        addDocument(recipientId);
    });
    
    // Services
    $('.service-checkbox').off('change').on('change', function() {
        const recipientId = $(this).data('recipient-id');
        calculateCost(recipientId);
    });
    
    $('.cod-checkbox').off('change').on('change', function() {
        const recipientId = $(this).data('recipient-id');
        if ($(this).is(':checked')) {
            $(`.cod-amount-container-${recipientId}`).removeClass('d-none');
        } else {
            $(`.cod-amount-container-${recipientId}`).addClass('d-none');
            $(`.cod-amount[data-recipient-id="${recipientId}"]`).val('');
        }
        calculateCost(recipientId);
    });
    
    $('.cod-amount').off('input').on('input', function() {
        const recipientId = $(this).data('recipient-id');
        calculateCost(recipientId);
    });
    
    $('.payer-radio').off('change').on('change', function() {
        const recipientId = $(this).data('recipient-id');
        calculateCost(recipientId);
    });
    
    // Images
    $('.order-images').off('change').on('change', function(e) {
        const recipientId = $(this).data('recipient-id');
        handleImageUpload(e, recipientId);
    });
    
    // Load saved addresses
    $('.load-saved-address').off('click').on('click', function() {
        const recipientId = $(this).data('recipient-id');
        $(`.saved-addresses-container-${recipientId}`).toggleClass('d-none');
        if (!$(`.saved-addresses-container-${recipientId}`).hasClass('d-none')) {
            loadSavedAddresses(recipientId);
        }
    });
    
    // Set default delivery time
    $('.delivery-time').each(function() {
        if (!$(this).val()) {
            const now = new Date();
            const deliveryTime = new Date(now.getTime() + 3 * 60 * 60 * 1000);
            $(this).val(toDatetimeLocalString(deliveryTime));
        }
    });
    
    // Item type toggle (only in single mode)
    $('.item-type').off('change').on('change', function() {
        const recipientId = $(this).data('recipient-id');
        const itemType = $(this).val();
        
        if (itemType === 'package') {
            $(`.form-package-${recipientId}`).removeClass('d-none');
            $(`.form-document-${recipientId}`).addClass('d-none');
        } else {
            $(`.form-package-${recipientId}`).addClass('d-none');
            $(`.form-document-${recipientId}`).removeClass('d-none');
        }
    });
}

// ============ PROVINCE/DISTRICT/WARD ============
function populateProvinceSelect(recipientId) {
    if (vietnamData.length > 0) {
        let html = '<option value="">Tỉnh/Thành phố</option>';
        vietnamData.forEach(province => {
            html += `<option value="${province.code}">${province.name}</option>`;
        });
        $(`.province-select[data-recipient-id="${recipientId}"]`).html(html);
    }
}

function handleProvinceChange(recipientId) {
    const provinceCode = parseInt($(`.province-select[data-recipient-id="${recipientId}"]`).val());
    
    $(`.district-select[data-recipient-id="${recipientId}"]`).html('<option value="">Quận/Huyện</option>').prop('disabled', true);
    $(`.ward-select[data-recipient-id="${recipientId}"]`).html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!provinceCode) {
        updateFullAddress(recipientId);
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    if (province?.districts) {
        let html = '<option value="">Quận/Huyện</option>';
        province.districts.forEach(district => {
            html += `<option value="${district.code}">${district.name}</option>`;
        });
        $(`.district-select[data-recipient-id="${recipientId}"]`).html(html).prop('disabled', false);
    }
    updateFullAddress(recipientId);
}

function handleDistrictChange(recipientId) {
    const districtCode = parseInt($(`.district-select[data-recipient-id="${recipientId}"]`).val());
    const provinceCode = parseInt($(`.province-select[data-recipient-id="${recipientId}"]`).val());
    
    $(`.ward-select[data-recipient-id="${recipientId}"]`).html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!districtCode) {
        updateFullAddress(recipientId);
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    const district = province?.districts.find(d => d.code === districtCode);
    
    if (district?.wards) {
        let html = '<option value="">Phường/Xã</option>';
        district.wards.forEach(ward => {
            html += `<option value="${ward.code}">${ward.name}</option>`;
        });
        $(`.ward-select[data-recipient-id="${recipientId}"]`).html(html).prop('disabled', false);
    }
    updateFullAddress(recipientId);
}

function updateFullAddress(recipientId) {
    const detail = $(`.address-detail[data-recipient-id="${recipientId}"]`).val().trim();
    const wardText = $(`.ward-select[data-recipient-id="${recipientId}"] option:selected`).text();
    const districtText = $(`.district-select[data-recipient-id="${recipientId}"] option:selected`).text();
    const provinceText = $(`.province-select[data-recipient-id="${recipientId}"] option:selected`).text();

    let addressParts = [];
    
    if (detail) addressParts.push(detail);
    if ($(`.ward-select[data-recipient-id="${recipientId}"]`).val() && wardText !== 'Phường/Xã') addressParts.push(wardText);
    if ($(`.district-select[data-recipient-id="${recipientId}"]`).val() && districtText !== 'Quận/Huyện') addressParts.push(districtText);
    if ($(`.province-select[data-recipient-id="${recipientId}"]`).val() && provinceText !== 'Tỉnh/Thành phố') addressParts.push(provinceText);

    const fullAddress = addressParts.join(', ');
    $(`.full-address-${recipientId}`).text(fullAddress || 'Chưa có địa chỉ đầy đủ');
    $(`.recipient-full-address-${recipientId}`).val(fullAddress);

    if (geocodeTimeout) clearTimeout(geocodeTimeout);
    
    if ($(`.province-select[data-recipient-id="${recipientId}"]`).val() && 
        $(`.district-select[data-recipient-id="${recipientId}"]`).val() && fullAddress) {
        $(`.geocode-status-${recipientId}`).html('<small class="text-warning"><i class="bi bi-hourglass-split"></i> Đang tìm tọa độ...</small>');
        
        geocodeTimeout = setTimeout(() => {
            fetchCoordinates(fullAddress, recipientId);
        }, 1000);
    } else {
        $(`.recipient-lat-${recipientId}`).val('');
        $(`.recipient-lng-${recipientId}`).val('');
        $(`.geocode-status-${recipientId}`).html('<small class="text-muted">Chưa tìm tọa độ</small>');
    }
}

// ============ GOONG AUTOCOMPLETE & GEOCODING ============
function goongAutocomplete(query, recipientId) {
    const provinceText = $(`.province-select[data-recipient-id="${recipientId}"] option:selected`).text();
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
                displayAutocompleteSuggestions(data.predictions, recipientId);
            } else {
                $(`.address-suggestions-${recipientId}`).hide().html('');
            }
        },
        error: function() {
            console.warn('⚠️ Goong Autocomplete API lỗi');
        }
    });
}

function displayAutocompleteSuggestions(predictions, recipientId) {
    let html = '';
    predictions.forEach(pred => {
        html += `
            <button type="button" class="list-group-item list-group-item-action" 
                    data-place-id="${pred.place_id}"
                    data-description="${pred.description}"
                    data-recipient-id="${recipientId}">
                <i class="bi bi-geo-alt text-danger"></i> ${pred.description}
            </button>
        `;
    });
    
    $(`.address-suggestions-${recipientId}`).html(html).show();
    
    $(`.address-suggestions-${recipientId} .list-group-item`).on('click', function(e) {
        e.preventDefault();
        const placeId = $(this).data('place-id');
        const description = $(this).data('description');
        const rid = $(this).data('recipient-id');
        
        goongPlaceDetail(placeId, description, rid);
        $(`.address-suggestions-${rid}`).hide();
    });
}

function goongPlaceDetail(placeId, description, recipientId) {
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
                
                $(`.recipient-lat-${recipientId}`).val(lat);
                $(`.recipient-lng-${recipientId}`).val(lng);
                $(`.geocode-status-${recipientId}`).html(`
                    <small class="text-success">
                        <i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ
                    </small>
                `);
                
                parseGoongAddress(result, description, recipientId);
                
                console.log('✅ Địa chỉ từ Goong:', { lat, lng, address: description });
            }
        },
        error: function() {
            console.error('❌ Không thể lấy chi tiết địa điểm');
        }
    });
}

function parseGoongAddress(result, description, recipientId) {
    $(`.address-detail[data-recipient-id="${recipientId}"]`).val(description.split(',')[0].trim());
    
    const addressComponents = result.address_components || [];
    
    addressComponents.forEach(component => {
        const types = component.types || [];
        
        if (types.includes('administrative_area_level_1')) {
            const provinceName = component.long_name;
            $(`.province-select[data-recipient-id="${recipientId}"] option`).each(function() {
                if ($(this).text().includes(provinceName)) {
                    $(`.province-select[data-recipient-id="${recipientId}"]`).val($(this).val()).trigger('change');
                }
            });
        }
        
        if (types.includes('administrative_area_level_2')) {
            setTimeout(() => {
                const districtName = component.long_name;
                $(`.district-select[data-recipient-id="${recipientId}"] option`).each(function() {
                    if ($(this).text().includes(districtName)) {
                        $(`.district-select[data-recipient-id="${recipientId}"]`).val($(this).val()).trigger('change');
                    }
                });
            }, 500);
        }
        
        if (types.includes('sublocality_level_1') || types.includes('administrative_area_level_3')) {
            setTimeout(() => {
                const wardName = component.long_name;
                $(`.ward-select[data-recipient-id="${recipientId}"] option`).each(function() {
                    if ($(this).text().includes(wardName)) {
                        $(`.ward-select[data-recipient-id="${recipientId}"]`).val($(this).val()).trigger('change');
                    }
                });
            }, 1000);
        }
    });
    
    setTimeout(() => {
        updateFullAddress(recipientId);
    }, 1500);
}

function fetchCoordinates(address, recipientId) {
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
                
                $(`.recipient-lat-${recipientId}`).val(lat);
                $(`.recipient-lng-${recipientId}`).val(lng);
                $(`.geocode-status-${recipientId}`).html(`
                    <small class="text-success">
                        <i class="bi bi-check-circle"></i> Đã tìm thấy tọa độ
                    </small>
                `);
            } else {
                $(`.geocode-status-${recipientId}`).html(`
                    <small class="text-warning">
                        <i class="bi bi-exclamation-triangle"></i> Không tìm thấy tọa độ chính xác
                    </small>
                `);
            }
        },
        error: function() {
            $(`.geocode-status-${recipientId}`).html(`
                <small class="text-danger">
                    <i class="bi bi-x-circle"></i> Lỗi kết nối Goong API
                </small>
            `);
        }
    });
}

// ============ PRODUCTS MANAGEMENT (SINGLE MODE) ============
function addProduct(recipientId) {
    const name = $(`.product-name-${recipientId}`).val().trim();
    const quantity = parseInt($(`.product-quantity-${recipientId}`).val()) || 1;
    const weight = parseFloat($(`.product-weight-${recipientId}`).val()) || 0;
    const value = parseFloat($(`.product-value-${recipientId}`).val()) || 0;
    const length = parseFloat($(`.product-length-${recipientId}`).val()) || 0;
    const width = parseFloat($(`.product-width-${recipientId}`).val()) || 0;
    const height = parseFloat($(`.product-height-${recipientId}`).val()) || 0;

    if (!name) {
        alert('⚠️ Vui lòng nhập tên hàng');
        return;
    }

    if (weight <= 0) {
        alert('⚠️ Khối lượng phải lớn hơn 0');
        return;
    }

    const specials = [];
    $(`.special-checkbox-${recipientId}:checked`).each(function() {
        specials.push($(this).val());
    });

    const recipient = recipientsList.find(r => r.id === recipientId);
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

    recipient.products.push(product);
    console.log('✅ Đã thêm hàng:', name);

    renderProductsList(recipientId);
    resetProductForm(recipientId);
    calculateCost(recipientId);
}

function addDocument(recipientId) {
    const name = $(`.document-name-${recipientId}`).val().trim();
    const quantity = parseInt($(`.document-quantity-${recipientId}`).val()) || 1;
    const weight = parseFloat($(`.document-weight-${recipientId}`).val()) || 0;
    const value = parseFloat($(`.document-value-${recipientId}`).val()) || 0;
    const length = parseFloat($(`.document-length-${recipientId}`).val()) || 0;
    const width = parseFloat($(`.document-width-${recipientId}`).val()) || 0;
    const height = parseFloat($(`.document-height-${recipientId}`).val()) || 0;

    if (!name) {
        alert('⚠️ Vui lòng nhập tên tài liệu');
        return;
    }

    if (weight <= 0) {
        alert('⚠️ Khối lượng phải lớn hơn 0');
        return;
    }

    const specials = [];
    $(`.doc-special-checkbox-${recipientId}:checked`).each(function() {
        specials.push($(this).val());
    });

    const recipient = recipientsList.find(r => r.id === recipientId);
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

    recipient.products.push(product);
    console.log('✅ Đã thêm tài liệu:', name);

    renderProductsList(recipientId);
    resetDocumentForm(recipientId);
    calculateCost(recipientId);
}

function renderProductsList(recipientId) {
    const recipient = recipientsList.find(r => r.id === recipientId);
    const container = $(`.products-list-${recipientId}`);
    
    if (!recipient.products || recipient.products.length === 0) {
        container.html('');
        return;
    }

    let html = '';
    recipient.products.forEach((item, idx) => {
        const icon = item.type === 'package' ? '📦' : '📄';
        html += `
            <div class="product-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${icon} ${item.name}</strong>
                        <div class="text-muted small">
                            SL: ${item.quantity} | KL: ${item.weight}g | GT: ${item.value.toLocaleString('vi-VN')}đ
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-btn" onclick="removeProduct(${recipientId}, ${idx})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.html(html);
    $(`.products-json-${recipientId}`).val(JSON.stringify(recipient.products));
}

function removeProduct(recipientId, idx) {
    if (confirm('Xóa hàng này?')) {
        const recipient = recipientsList.find(r => r.id === recipientId);
        recipient.products.splice(idx, 1);
        renderProductsList(recipientId);
        calculateCost(recipientId);
    }
}

function resetProductForm(recipientId) {
    $(`.product-name-${recipientId}`).val('');
    $(`.product-quantity-${recipientId}`).val('1');
    $(`.product-weight-${recipientId}`).val('10');
    $(`.product-value-${recipientId}`).val('10000');
    $(`.product-length-${recipientId}`).val('');
    $(`.product-width-${recipientId}`).val('');
    $(`.product-height-${recipientId}`).val('');
    $(`.special-checkbox-${recipientId}`).prop('checked', false);
}

function resetDocumentForm(recipientId) {
    $(`.document-name-${recipientId}`).val('');
    $(`.document-quantity-${recipientId}`).val('1');
    $(`.document-weight-${recipientId}`).val('10');
    $(`.document-value-${recipientId}`).val('10000');
    $(`.document-length-${recipientId}`).val('');
    $(`.document-width-${recipientId}`).val('');
    $(`.document-height-${recipientId}`).val('');
    $(`.doc-special-checkbox-${recipientId}`).prop('checked', false);
}

// ============ IMAGE UPLOAD ============
function handleImageUpload(e, recipientId) {
    const recipient = recipientsList.find(r => r.id === recipientId);
    const files = Array.from(e.target.files);
    const MAX_IMAGES = 5;
    const MAX_FILE_SIZE = 5 * 1024 * 1024;
    
    if (recipient.selectedImages.length + files.length > MAX_IMAGES) {
        alert(`⚠️ Chỉ được tải tối đa ${MAX_IMAGES} ảnh`);
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
        
        recipient.selectedImages.push(file);
    }
    
    renderImagePreviews(recipientId);
    $(e.target).val('');
}

function renderImagePreviews(recipientId) {
    const recipient = recipientsList.find(r => r.id === recipientId);
    const container = $(`.image-preview-container-${recipientId}`);
    container.html('');
    
    if (!recipient.selectedImages || recipient.selectedImages.length === 0) return;
    
    recipient.selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const html = `
                <div class="col-md-6 col-6">
                    <div class="image-preview-item">
                        <button type="button" class="remove-image" onclick="removeImage(${recipientId}, ${index})">×</button>
                        <img src="${e.target.result}" alt="Preview">
                        <div class="image-note">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="recipients[${recipientId}][image_notes][]" 
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

function removeImage(recipientId, index) {
    const recipient = recipientsList.find(r => r.id === recipientId);
    recipient.selectedImages.splice(index, 1);
    renderImagePreviews(recipientId);
}

// ============ CALCULATE COST ============
function calculateCost(recipientId) {
    let productsData;
    
    if (orderMode === 'multi') {
        // Use shared product data
        if (!sharedProductData || !sharedProductData.name) {
            $(`.base-cost-${recipientId}`).text('0 đ');
            $(`.extra-cost-${recipientId}`).text('0 đ');
            $(`.total-cost-${recipientId}`).text('0 đ');
            $(`.sender-pays-${recipientId}`).text('0 đ');
            $(`.recipient-pays-${recipientId}`).text('0 đ');
            return;
        }
        productsData = [sharedProductData];
    } else {
        // Use recipient's own products
        const recipient = recipientsList.find(r => r.id === recipientId);
        if (!recipient.products || recipient.products.length === 0) {
            $(`.base-cost-${recipientId}`).text('0 đ');
            $(`.extra-cost-${recipientId}`).text('0 đ');
            $(`.total-cost-${recipientId}`).text('0 đ');
            $(`.sender-pays-${recipientId}`).text('0 đ');
            $(`.recipient-pays-${recipientId}`).text('0 đ');
            return;
        }
        productsData = recipient.products;
    }
    
    const services = [];
    $(`.service-checkbox[data-recipient-id="${recipientId}"]:checked`).each(function() {
        services.push($(this).val());
    });
    
    const codAmount = parseFloat($(`.cod-amount[data-recipient-id="${recipientId}"]`).val()) || 0;
    const payer = $(`input[name="recipients[${recipientId}][payer]"]:checked`).val();
    
    const data = {
        products_json: JSON.stringify(productsData),
        services: services,
        cod_amount: codAmount,
        payer: payer,
        item_type: productsData[0]?.type || 'package',
        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
    };
    
    $.post('{{ route("customer.orders.calculate") }}', data)
        .done(function(res) {
            if (res && res.success === true) {
                $(`.base-cost-${recipientId}`).text((res.base_cost || 0).toLocaleString('vi-VN') + ' đ');
                $(`.extra-cost-${recipientId}`).text((res.extra_cost || 0).toLocaleString('vi-VN') + ' đ');
                
                if (res.cod_fee > 0) {
                    $(`.cod-fee-${recipientId}`).text(res.cod_fee.toLocaleString('vi-VN') + ' đ');
                    $(`.cod-fee-row-${recipientId}`).show();
                } else {
                    $(`.cod-fee-row-${recipientId}`).hide();
                }
                
                $(`.total-cost-${recipientId}`).text((res.total || 0).toLocaleString('vi-VN') + ' đ');
                $(`.sender-pays-${recipientId}`).text((res.sender_pays || 0).toLocaleString('vi-VN') + ' đ');
                $(`.recipient-pays-${recipientId}`).text((res.recipient_pays || 0).toLocaleString('vi-VN') + ' đ');
                
                updateSummary();
            }
        })
        .fail(function(xhr) {
            console.error('❌ Lỗi tính cước:', xhr.responseText);
        });
}

// ============ UPDATE SUMMARY ============
function updateSummary() {
    let totalRecipients = recipientsList.length;
    let totalShipping = 0;
    let totalCOD = 0;
    let totalSenderPays = 0;
    let totalRecipientPays = 0;
    
    recipientsList.forEach(recipient => {
        const costText = $(`.total-cost-${recipient.id}`).text().replace(/[^\d]/g, '');
        const codFeeText = $(`.cod-fee-${recipient.id}`).text().replace(/[^\d]/g, '');
        const senderPaysText = $(`.sender-pays-${recipient.id}`).text().replace(/[^\d]/g, '');
        const recipientPaysText = $(`.recipient-pays-${recipient.id}`).text().replace(/[^\d]/g, '');
        
        totalShipping += parseInt(costText) || 0;
        totalCOD += parseInt(codFeeText) || 0;
        totalSenderPays += parseInt(senderPaysText) || 0;
        totalRecipientPays += parseInt(recipientPaysText) || 0;
    });
    
    $('#total-recipients').text(totalRecipients);
    $('#total-shipping-summary').text(totalShipping.toLocaleString('vi-VN') + ' đ');
    $('#total-cod-summary').text(totalCOD.toLocaleString('vi-VN') + ' đ');
    $('#total-sender-summary').text(totalSenderPays.toLocaleString('vi-VN') + ' đ');
    $('#total-recipient-summary').text(totalRecipientPays.toLocaleString('vi-VN') + ' đ');
}

// ============ SAVED ADDRESSES ============
function loadSavedAddresses(recipientId) {
    $.get('{{ route("customer.orders.addresses.list") }}', function(data) {
        displaySavedAddresses(data, recipientId);
    }).fail(function() {
        alert('Không thể tải địa chỉ đã lưu');
    });
}

function displaySavedAddresses(addresses, recipientId) {
    if (!addresses || addresses.length === 0) {
        $(`.saved-addresses-list-${recipientId}`).html('<p class="text-muted">Chưa có địa chỉ nào được lưu</p>');
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
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick='selectSavedAddress(${recipientId}, ${JSON.stringify(addr)})'>Chọn</button>
                </div>
            </div>
        `;
    });
    $(`.saved-addresses-list-${recipientId}`).html(html);
}

function selectSavedAddress(recipientId, addr) {
    $(`.recipient-name[data-recipient-id="${recipientId}"]`).val(addr.recipient_name);
    $(`.recipient-phone[data-recipient-id="${recipientId}"]`).val(addr.recipient_phone);
    $(`.address-detail[data-recipient-id="${recipientId}"]`).val(addr.address_detail);
    
    $(`.province-select[data-recipient-id="${recipientId}"]`).val(addr.province_code).trigger('change');
    
    setTimeout(() => {
        $(`.district-select[data-recipient-id="${recipientId}"]`).val(addr.district_code).trigger('change');
        setTimeout(() => {
            $(`.ward-select[data-recipient-id="${recipientId}"]`).val(addr.ward_code).trigger('change');
        }, 300);
    }, 300);

    $(`.saved-addresses-container-${recipientId}`).addClass('d-none');
}

// ============ DATETIME HANDLING ============
function setDefaultDateTime() {
  const now = new Date();
  const pickupTime = new Date(now.getTime() + 2 * 60 * 60 * 1000);

  $('#pickup-time').val(toDatetimeLocalString(pickupTime));
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
  
  if (!pickupValue) {
    alert('⚠️ Vui lòng chọn thời gian hẹn lấy hàng');
    return false;
  }
  
  const pickup = new Date(pickupValue);
  const now = new Date();
  
  if (pickup <= now) {
    alert('⚠️ Thời gian hẹn lấy phải trong tương lai');
    return false;
  }
  
  // Validate each recipient's delivery time
  let allValid = true;
  recipientsList.forEach(recipient => {
    const deliveryValue = $(`.delivery-time[data-recipient-id="${recipient.id}"]`).val();
    
    if (!deliveryValue) {
      alert(`⚠️ Vui lòng chọn thời gian giao cho người nhận #${recipientsList.indexOf(recipient) + 1}`);
      allValid = false;
      return false;
    }
    
    const delivery = new Date(deliveryValue);
    const minDeliveryTime = new Date(pickup.getTime() + 60 * 60 * 1000);
    
    if (delivery < minDeliveryTime) {
      alert(`⚠️ Thời gian giao cho người nhận #${recipientsList.indexOf(recipient) + 1} phải ít nhất 1 giờ sau thời gian lấy`);
      allValid = false;
      return false;
    }
  });
  
  return allValid;
}

// ============ LOAD PROVINCES ============
function loadProvinces() {
    return new Promise((resolve, reject) => {
        $.get("https://provinces.open-api.vn/api/?depth=3", function(data) {
            vietnamData = data;
            console.log('✅ Đã tải', data.length, 'tỉnh thành');
            resolve(data);
        }).fail(reject);
    });
}

// ============ SENDER INFO ============
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

// ============ TOGGLE FORMS ============
function setupToggleForms() {
    // This is handled in setupRecipientEventHandlers
}

// ============ SETUP EVENT HANDLERS ============
function setupEventHandlers() {
    // Global event handlers
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.address-detail, [class*="address-suggestions"]').length) {
            $('[class*="address-suggestions"]').hide();
        }
    });
}

// ============ SETUP GOONG AUTOCOMPLETE ============
function setupGoongAutocomplete() {
    // Autocomplete is setup in setupRecipientEventHandlers
}

// ============ VALIDATE & SUBMIT FORM ============
function validateForm() {
    if (!$('#sender-select').val()) {
        alert('⚠️ Vui lòng chọn thông tin người gửi');
        return false;
    }
    
    if (!validateDatetimes()) {
        return false;
    }
    
    // Validate shared product in multi mode
    if (orderMode === 'multi') {
        if (!sharedProductData || !sharedProductData.name) {
            alert('⚠️ Vui lòng nhập thông tin hàng hóa chung');
            return false;
        }
        
        if (sharedProductData.weight <= 0) {
            alert('⚠️ Khối lượng hàng hóa phải lớn hơn 0');
            return false;
        }
    }
    
    // Validate each recipient
    for (let recipient of recipientsList) {
        const index = recipientsList.indexOf(recipient) + 1;
        
        if (!$(`.recipient-name[data-recipient-id="${recipient.id}"]`).val().trim()) {
            alert(`⚠️ Vui lòng nhập tên người nhận #${index}`);
            return false;
        }
        
        if (!$(`.recipient-phone[data-recipient-id="${recipient.id}"]`).val().trim()) {
            alert(`⚠️ Vui lòng nhập số điện thoại người nhận #${index}`);
            return false;
        }
        
        const phonePattern = /^(0|\+84)[0-9]{9,10}$/;
        if (!phonePattern.test($(`.recipient-phone[data-recipient-id="${recipient.id}"]`).val().trim())) {
            alert(`⚠️ Số điện thoại người nhận #${index} không hợp lệ`);
            return false;
        }
        
        if (!$(`.province-select[data-recipient-id="${recipient.id}"]`).val() || 
            !$(`.district-select[data-recipient-id="${recipient.id}"]`).val() || 
            !$(`.ward-select[data-recipient-id="${recipient.id}"]`).val()) {
            alert(`⚠️ Vui lòng chọn địa chỉ đầy đủ cho người nhận #${index}`);
            return false;
        }
        
        if (!$(`.address-detail[data-recipient-id="${recipient.id}"]`).val().trim()) {
            alert(`⚠️ Vui lòng nhập số nhà, tên đường cho người nhận #${index}`);
            return false;
        }
        
        // In single mode, check if recipient has products
        if (orderMode === 'single' && (!recipient.products || recipient.products.length === 0)) {
            alert(`⚠️ Vui lòng thêm ít nhất 1 hàng hóa cho người nhận #${index}`);
            return false;
        }
    }
    
    return true;
}

$('#orderForm').on('submit', function(e) {
    e.preventDefault();
    
    console.log('📤 Chuẩn bị submit form');
    
    if (!validateForm()) {
        return false;
    }
    
    // Format pickup time
    const pickupValue = $('#pickup-time').val();
    $('#pickup_time_formatted').val(formatDatetimeForDatabase(pickupValue));
    
    // Format delivery times for each recipient
    recipientsList.forEach(recipient => {
        const deliveryValue = $(`.delivery-time[data-recipient-id="${recipient.id}"]`).val();
        const hiddenInput = $(`<input type="hidden" name="recipients[${recipient.id}][delivery_time_formatted]">`);
        hiddenInput.val(formatDatetimeForDatabase(deliveryValue));
        $(this).append(hiddenInput);
    });
    
    // Handle images for each recipient
    const formData = new FormData(this);
    
    // Add shared product data in multi mode
    if (orderMode === 'multi' && sharedProductData) {
        formData.append('shared_product_json', JSON.stringify(sharedProductData));
        
        // For each recipient, add the shared product as their product
        recipientsList.forEach(recipient => {
            formData.set(`recipients[${recipient.id}][products_json]`, JSON.stringify([sharedProductData]));
        });
    }
    
    recipientsList.forEach(recipient => {
        if (recipient.selectedImages && recipient.selectedImages.length > 0) {
            recipient.selectedImages.forEach((file, index) => {
                formData.append(`recipients[${recipient.id}][images][]`, file);
            });
        }
    });
    
    $('#submitOrder').prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
    
    // Submit with FormData
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('✅ Tạo đơn thành công');
            if (response.success) {
                alert('✅ Tạo đơn hàng thành công!');
                window.location.href = response.redirect || '{{ route("customer.orders.create") }}';
            } else {
                alert('❌ ' + (response.message || 'Có lỗi xảy ra'));
                $('#submitOrder').prop('disabled', false)
                    .html('<i class="bi bi-check-circle"></i> Tạo đơn hàng');
            }
        },
        error: function(xhr) {
            console.error('❌ Lỗi tạo đơn:', xhr.responseText);
            let errorMsg = 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMsg = errors.join('\n');
            }
            
            alert('❌ ' + errorMsg);
            $('#submitOrder').prop('disabled', false)
                .html('<i class="bi bi-check-circle"></i> Tạo đơn hàng');
        }
    });
    
    return false;
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
@endsection