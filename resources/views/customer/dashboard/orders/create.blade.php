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
</style>
<div class="container-fluid py-4">
  <div class="row">
    <!-- CỘT TRÁI: THÔNG TIN NGƯỜI GỬI & NHẬN -->
    <div class="card col-lg-6">
      <div class="card-header pb-0">
        <h5>Tạo đơn hàng mới</h5>
      </div>

      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-3">
          <div class="row">
            {{-- Người gửi --}}
            <div class="card mb-4">
              <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                  <h6>Thông tin người gửi</h6>
                  <div>
                    <input type="checkbox" id="sameAsAccount">
                    <label for="sameAsAccount" class="form-label">Gửi tại bưu cục</label>
                  </div>
                </div>
              </div>

              <div class="card-body px-3 pt-3 pb-2">
                <div>
                   @php
                      $account = $user;
                    @endphp
                     @if (!$account || !$account->userInfo)
                      <a style="color:red !important;text-decoration: underline;" href="{{url('/customer/account')}}">Cập nhật thông tin tài khoản...</a>
                    @else
                  <select class="form-select mb-3" aria-label="Default select example">
                   
                    <option selected>Chọn thông tin người gửi</option>
                   
                      
                    <option>{{ $account->full_name }} - {{ $account->userInfo->full_address}} - {{ $account->phone }}</option>
                    @endif
                  </select>
                </div>

                <div id="post-office-selects" style="display:none;">
                  <label for="postOfficeSelect">Gợi ý bưu cục</label>
                  <select class="form-select mb-3" id="postOfficeSelect" aria-label="Chọn bưu cục">
                    <option value="">Chọn bưu cục gần nhất</option>
                  </select>
                </div>

                <div id="appointment-select" style="display:block;">
                  <label for="">Thời gian hẹn lấy</label>
                  <input type="datetime-local" class="form-control mb-3" placeholder="Chọn thời gian hẹn lấy hàng">
                </div>
              </div>
            </div>

            {{-- Người nhận --}}
            <div class="card mb-4">
              <div class="card-header pb-0">
                <h6>Thông tin người nhận</h6>
              </div>

              <div class="card-body px-3 pt-3 pb-2">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="recipientName" class="form-label">Tên người nhận <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="recipientName" placeholder="Nhập tên người nhận">
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="recipientPhone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="recipientPhone" placeholder="Nhập số điện thoại người nhận">
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
                      <div class="col-md-6">
                        <input type="text" id="address-detail" name="address_detail" class="form-control" placeholder="Số nhà, tên đường..." required>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-12 mb-3">
                    <label for="">Thời gian hẹn giao <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control">
                  </div>
                </div>
              </div>
            </div>
          </div> <!-- row -->
        </div> <!-- table-responsive -->
      </div> <!-- card-body -->
    </div> <!-- card trái -->

    <!-- CỘT PHẢI: THÔNG TIN HÀNG HÓA -->
    <div class="card col-lg-6">
      <div class="card-header pb-0">
        <h5>Thông tin hàng hoá</h5>
      </div>

      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-3">
          <!-- Chọn loại hàng hóa -->
          <div class="mb-3">
            <h6 class="fw-bold mb-2">LOẠI HÀNG HÓA</h6>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="loaiHang" id="buuKien" checked>
              <label class="form-check-label text-danger" for="buuKien">Bưu kiện</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="loaiHang" id="taiLieu">
              <label class="form-check-label text-danger" for="taiLieu">Tài liệu</label>
            </div>
          </div>

          <!-- Form Bưu kiện -->
          <div id="formBuuKien">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Tên hàng <span class="text-danger">*</span></label>
                @if (!$products || $products->isEmpty())
                  <a style="color:red !important;text-decoration: underline;" href="{{url('/customer/account/product')}}">Thêm hàng hoá...</a>
                @else
                   <select class="form-select mb-3" id="product-select" aria-label="Default select example">
                  <option value="">--Nhập tên hàng hoá--</option>
                  @foreach ($products as $product)
                    <option value="{{ $product->id }}"
                            data-quantity="{{ $product->quantity ?? 1 }}"
                            data-weight="{{ $product->weight ?? 10 }}"
                            data-value="{{ $product->price ?? 10000 }}"
                            data-length="{{ $product->length ?? 0 }}"
                            data-width="{{ $product->width ?? 0 }}"
                            data-height="{{ $product->height ?? 0 }}">
                      {{ $product->name }}
                    </option>
                  @endforeach
                </select>
                @endif
               
              </div>

              <div class="col-md-4">
                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="1">
              </div>

              <div class="col-md-4">
                <label class="form-label">Khối lượng <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="number" class="form-control" id="weight" name="weight" value="10">
                  <span class="input-group-text" style="padding-right: 10px">g</span>
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Giá trị (VNĐ)</label>
                <input type="number" class="form-control" id="value" name="value" value="10000">
              </div>
            </div>

            <div class="row mt-4">
              <label for="">Kích thước</label>
              <div class="form-group col-md-4">
                <input type="number" class="form-control" data-length="" id="length" name="length" placeholder="Dài (cm)">
              </div>
              <div class="form-group col-md-4">
                <input type="number" class="form-control" id="width" name="width" placeholder="Rộng (cm)">
              </div>
              <div class="form-group col-md-4">
                <input type="number" class="form-control" id="height" name="height" placeholder="Cao (cm)">
              </div>
            </div>

            <div class="text-center my-3">
              <button class="btn btn-outline-danger fw-semibold">+ Thêm hàng hóa</button>
            </div>

            <div class="d-flex justify-content-between total-info">
              <div>Tổng khối lượng: <span id="total-weight">10</span> g</div>
              <div>Tổng giá trị: <span id="total-value">10.000</span> đ</div>
            </div>

            <div class="mt-4 special-box">
              <h6 class="fw-bold mb-2"><i class="bi bi-box"></i> TÍNH CHẤT HÀNG HÓA ĐẶC BIỆT</h6>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="giaTriCao" name="specials[]" value="giaTriCao">
                    <label class="form-check-label" for="giaTriCao">Giá trị cao</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="quaKho" name="specials[]" value="quaKho">
                    <label class="form-check-label" for="quaKho">Quá khổ</label>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="deVo" name="specials[]" value="deVo">
                    <label class="form-check-label" for="deVo">Dễ vỡ</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="chatLong" name="specials[]" value="chatLong">
                    <label class="form-check-label" for="chatLong">Chất lỏng</label>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="nguyenKhoi" name="specials[]" value="nguyenKhoi">
                    <label class="form-check-label" for="nguyenKhoi">Nguyên khối</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="pin" name="specials[]" value="pin">
                    <label class="form-check-label" for="pin">Từ tính, Pin</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Tài liệu -->
          <div id="formTaiLieu" class="d-none">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Tên tài liệu <span class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="Nhập tên tài liệu...">
              </div>

              <div class="col-md-4">
                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                <input type="number" class="form-control" value="1">
              </div>

              <div class="col-md-4">
                <label class="form-label">Khối lượng <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="number" class="form-control" value="10">
                  <span class="input-group-text">g</span>
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Giá trị (VNĐ)</label>
                <input type="number" class="form-control" value="10000">
              </div>
            </div>

            <div class="text-center my-3">
              <button class="btn btn-outline-danger fw-semibold">+ Thêm tài liệu</button>
            </div>

            <div class="d-flex justify-content-between total-info">
              <div>Tổng khối lượng: 10 g</div>
              <div>Tổng giá trị: 10.000 đ</div>
            </div>

            <div class="mt-4 special-box">
              <h6 class="fw-bold mb-2">TÍNH CHẤT HÀNG HÓA ĐẶC BIỆT</h6>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="taiLieuGiaTri">
                    <label class="form-check-label" for="taiLieuGiaTri">Giá trị cao</label>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-check d-flex gap-2">
                    <input class="form-check-input" type="checkbox" id="hoaDon">
                    <label class="form-check-label" for="hoaDon">Hóa đơn, Giấy chứng nhận</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="card mt-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-truck"></i> Dịch vụ cộng thêm</h6>
              </div>
              <div class="card-body">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="fast" id="fastService">
                  <label class="form-check-label" for="fastService">Giao nhanh (+15%)</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="insurance" id="insuranceService">
                  <label class="form-check-label" for="insuranceService">Bảo hiểm hàng hóa (1%)</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="cod" id="codService">
                  <label class="form-check-label" for="codService">Dịch vụ thu hộ (1.000đ + 1%)</label>
                </div>
                <div class="text-end mt-3">
                  <strong>Tổng cước: <span id="tongCuoc" class="text-danger">20.000 đ</span></strong>
                </div>
              </div>
            </div>
          </div>
        </div> <!-- table-responsive -->
      </div> <!-- card-body -->
    </div> <!-- card phải -->
  </div> <!-- row -->
</div> <!-- container-fluid -->

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Load dữ liệu tỉnh thành
    let vietnamData = [];
    let geocodeTimeout = null;
    const GEOAPIFY_API_KEY = '{{ config("services.geoapify.api_key") }}';

    $.get("https://provinces.open-api.vn/api/?depth=3", function(data) {
        vietnamData = data;
        
        // Populate tỉnh/thành
        let html = '<option value="">Tỉnh/Thành phố</option>';
        data.forEach(function(province) {
            html += `<option value="${province.code}">${province.name}</option>`;
        });
        $('#province-select').html(html);
        
        // Khôi phục dữ liệu cũ nếu có
        restoreSavedAddress();
    });
    
    // Event handlers for address
    $('#province-select').on('change', handleProvinceChange);
    $('#district-select').on('change', handleDistrictChange);
    $('#ward-select').on('change', updateFullAddressWithDebounce);
    $('#address-detail').on('keyup', updateFullAddressWithDebounce);

    // Handle product selection
    $('#product-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const quantity = selectedOption.data('quantity') || 1;
        const weight = selectedOption.data('weight') || 10;
        const value = selectedOption.data('value') || 10000;
        const length = selectedOption.data('length') || 0;
        const width = selectedOption.data('width') || 0;
        const height = selectedOption.data('height') || 0;

        // Update input fields
        $('#quantity').val(quantity);
        $('#weight').val(weight);
        $('#value').val(value);
        $('#length').val(length);
        $('#width').val(width);
        $('#height').val(height);

        // Update total displays
        $('#total-weight').text(weight);
        $('#total-value').text(value.toLocaleString('vi-VN'));

        // Trigger cost calculation
        calculateCost();
    });

    // Handle post office checkbox
    $('#sameAsAccount').change(function() {
        if ($(this).is(':checked')) {
            $('#post-office-selects').show();
            $('#appointment-select').hide();
        } else {
            $('#post-office-selects').hide();
            $('#appointment-select').show();
        }
    });

    // Toggle between Bưu kiện and Tài liệu forms
    const buuKienRadio = document.getElementById('buuKien');
    const taiLieuRadio = document.getElementById('taiLieu');
    const formBuuKien = document.getElementById('formBuuKien');
    const formTaiLieu = document.getElementById('formTaiLieu');

    function toggleForms() {
        if (buuKienRadio.checked) {
            formBuuKien.classList.remove('d-none');
            formTaiLieu.classList.add('d-none');
        } else if (taiLieuRadio.checked) {
            formTaiLieu.classList.remove('d-none');
            formBuuKien.classList.add('d-none');
        }
    }

    toggleForms();
    buuKienRadio.addEventListener('change', toggleForms);
    taiLieuRadio.addEventListener('change', toggleForms);

    // Handle cost calculation
    $('input[type=checkbox], input[type=number]').on('change', calculateCost);

    function calculateCost() {
        const data = {
            weight: $('#weight').val(),
            length: $('#length').val(),
            width: $('#width').val(),
            height: $('#height').val(),
            specials: $('input[name="specials[]"]:checked').map((_, e) => e.value).get(),
            services: $('input[type=checkbox][id$="Service"]:checked').map((_, e) => e.value).get(),
            _token: '{{ csrf_token() }}'
        };

        $.post('{{ route('customer.orders.calculate') }}', data, function(res) {
            $('#tongCuoc').text(res.total.toLocaleString('vi-VN') + ' đ');
        });
    }
});

// Khôi phục địa chỉ đã lưu
function restoreSavedAddress() {
    const savedProvinceCode = "{{ old('province_code', $account->userInfo->province_code ?? '') }}";
    const savedDistrictCode = "{{ old('district_code', $account->userInfo->district_code ?? '') }}";
    const savedWardCode = "{{ old('ward_code', $account->userInfo->ward_code ?? '') }}";
    const addressDetail = "{{ old('address_detail', $account->userInfo->address_detail ?? '') }}";
    
    if (savedProvinceCode) {
        $('#province-select').val(savedProvinceCode).trigger('change');
    }
    
    if (addressDetail) {
        $('#address-detail').val(addressDetail);
    }
    
    if (savedDistrictCode) {
        setTimeout(() => {
            $('#district-select').val(savedDistrictCode).trigger('change');
        }, 500);
    }
    
    if (savedWardCode) {
        setTimeout(() => {
            $('#ward-select').val(savedWardCode).trigger('change');
        }, 1000);
    }
}

// Xử lý khi chọn tỉnh
function handleProvinceChange() {
    const provinceCode = parseInt($(this).val());
    
    $('#district-select').html('<option value="">Quận/Huyện</option>').prop('disabled', true);
    $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!provinceCode || isNaN(provinceCode)) {
        updateFullAddressWithDebounce();
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    
    if (province?.districts?.length > 0) {
        let html = '<option value="">Quận/Huyện</option>';
        province.districts.forEach(district => {
            html += `<option value="${district.code}">${district.name}</option>`;
        });
        $('#district-select').html(html).prop('disabled', false);
    }
    
    updateFullAddressWithDebounce();
}

// Xử lý khi chọn quận/huyện
function handleDistrictChange() {
    const districtCode = parseInt($(this).val());
    const provinceCode = parseInt($('#province-select').val());
    
    $('#ward-select').html('<option value="">Phường/Xã</option>').prop('disabled', true);
    
    if (!districtCode || isNaN(districtCode)) {
        updateFullAddressWithDebounce();
        return;
    }
    
    const province = vietnamData.find(p => p.code === provinceCode);
    if (!province) {
        updateFullAddressWithDebounce();
        return;
    }
    
    const district = province.districts.find(d => d.code === districtCode);
    
    if (district?.wards?.length > 0) {
        let html = '<option value="">Phường/Xã</option>';
        district.wards.forEach(ward => {
            html += `<option value="${ward.code}">${ward.name}</option>`;
        });
        $('#ward-select').html(html).prop('disabled', false);
    }
    
    updateFullAddressWithDebounce();
}

// Debounce để tránh gọi API liên tục
function updateFullAddressWithDebounce() {
    clearTimeout(geocodeTimeout);
    geocodeTimeout = setTimeout(updateFullAddress, 800);
}

// Cập nhật địa chỉ đầy đủ và lấy tọa độ
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

    if ($('#province-select').val() && $('#district-select').val()) {
        fetchCoordinates(fullAddress);
    } else {
        $('#latitude').val('');
        $('#longitude').val('');
    }
}

// Gọi API Geoapify để lấy tọa độ
function fetchCoordinates(address) {
    if (!GEOAPIFY_API_KEY || GEOAPIFY_API_KEY === '') {
        return;
    }

    $('#full-address').html(`${address} <span class="spinner-border spinner-border-sm ms-2" role="status"></span>`);

    const url = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(address)}&filter=countrycode:vn&limit=1&apiKey=${GEOAPIFY_API_KEY}`;

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.features?.length > 0) {
                const [lon, lat] = data.features[0].geometry.coordinates;
                
                $('#latitude').val(lat.toFixed(6));
                $('#longitude').val(lon.toFixed(6));
                
                $('#full-address').html(`${address} <span class="text-success ms-2">✓</span>`);
                console.log('📍 Tọa độ:', { lat, lon });
            } else {
                console.warn('⚠️ Không tìm thấy tọa độ');
                $('#latitude').val('');
                $('#longitude').val('');
                $('#full-address').html(`${address} <span class="text-warning ms-2">⚠ Không tìm thấy tọa độ</span>`);
            }
        })
        .catch(err => {
            console.error('❌ Lỗi Geoapify:', err);
            $('#full-address').html(`${address} <span class="text-danger ms-2">✗ Lỗi lấy tọa độ</span>`);
        });
}
</script>
<script src="{{ asset('assets2/js/customer/dashboard/orders/fetchNearbyPostOffices.js') }}"></script>
@endpush