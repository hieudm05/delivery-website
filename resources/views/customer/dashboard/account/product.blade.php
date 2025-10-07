@extends('customer.dashboard.layouts.app')
@section('title', 'Danh sách hàng hóa')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-uppercase">Danh sách hàng hóa</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>

            <div class="input-group mb-3 w-20">
                <input type="text" class="form-control" placeholder="Nhập mã hàng hóa, tên hàng hóa">
                {{-- <button class="btn btn-success" type="button"><i class="fas fa-search"></i></button> --}}
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-striped text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Mã hàng hóa</th>
                            <th>Tên hàng hóa</th>
                            <th>Trọng lượng (gram)</th>
                            <th>Đơn giá</th>
                            <th>Kích thước (cm)</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                      @if (!$products->isEmpty())
                      @foreach ($products as $index => $product)
                        <tr>
                            <td>{{ $products->firstItem() + $index }}</td>
                            <td>{{ str_pad($product->id, 8, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ number_format($product->weight) }}</td>
                            <td>{{ number_format($product->price) }}</td>
                            <td>{{ $product->length }} x {{ $product->width }} x {{ $product->height }}</td>
                           <td>
                              <button class="btn btn-sm btn-warning btn-edit" data-id="{{ $product->id }}">
                                  <i class="fas fa-edit"></i> Sửa
                              </button>
                              <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $product->id }}">
                                  <i class="fas fa-trash-alt"></i> Xóa
                              </button>
                          </td>

                        </tr>
                      @endforeach
                      @else
                      <tr>
                          <td colspan="7" class="text-muted">Không có bản ghi nào</td>
                      </tr>
                      @endif
                    </tbody>
                </table>
            </div>
              <!-- Hiển thị nút phân trang -->
                  <div class="d-flex justify-content-end mt-3">
                      {{ $products->links() }}
                  </div>
        </div>
    </div>
</div>

{{-- Modal thêm hàng hóa --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bage-primary text-white">
        <h5 class="modal-title" id="addProductModalLabel">Thêm hàng hóa mới</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <form id="addProductForm">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên hàng hóa</label>
              <input type="text" class="form-control" name="name" placeholder="VD: Táo Mỹ">
              <div class="text-danger small mt-1 error-message" data-error-for="name"></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Trọng lượng (gram)</label>
              <input type="number" class="form-control" name="weight">
              <div class="text-danger small mt-1 error-message" data-error-for="weight"></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Đơn giá</label>
              <input type="number" class="form-control" name="price">
              <div class="text-danger small mt-1 error-message" data-error-for="price"></div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Dài (cm)</label>
              <input type="number" class="form-control" name="length" placeholder="VD: 10">
              <div class="text-danger small mt-1 error-message" data-error-for="length"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Rộng (cm)</label>
              <input type="number" class="form-control" name="width" placeholder="VD: 10">
              <div class="text-danger small mt-1 error-message" data-error-for="width"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Cao (cm)</label>
              <input type="number" class="form-control" name="height" placeholder="VD: 10">
              <div class="text-danger small mt-1 error-message" data-error-for="height"></div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- Modal sửa hàng hóa --}}
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="editProductModalLabel">Chỉnh sửa hàng hóa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <form id="editProductForm">
        @csrf
        <input type="hidden" name="id">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tên hàng hóa</label>
              <input type="text" class="form-control" name="name">
            </div>

            <div class="col-md-6">
              <label class="form-label">Trọng lượng (gram)</label>
              <input type="number" class="form-control" name="weight">
            </div>

            <div class="col-md-6">
              <label class="form-label">Đơn giá</label>
              <input type="number" class="form-control" name="price">
            </div>

            <div class="col-md-4">
              <label class="form-label">Dài (cm)</label>
              <input type="number" class="form-control" name="length">
            </div>
            <div class="col-md-4">
              <label class="form-label">Rộng (cm)</label>
              <input type="number" class="form-control" name="width">
            </div>
            <div class="col-md-4">
              <label class="form-label">Cao (cm)</label>
              <input type="number" class="form-control" name="height">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-warning">Cập nhật</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const $form = $('#addProductForm');
    const modalEl = document.getElementById('addProductModal');
    const modal = new bootstrap.Modal(modalEl);
    const modalEditEl = document.getElementById('editProductModal');
    const editModal = new bootstrap.Modal(modalEditEl);
    const $editForm = $('#editProductForm');
    // Gắn CSRF token cho Ajax
   $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
        }
    });

    function showError(field, message) {
        const $error = $form.find(`.error-message[data-error-for="${field}"]`);
        if (message) {
            $error.text(message);
            $form.find(`[name="${field}"]`).addClass('is-invalid');
        } else {
            $error.text('');
            $form.find(`[name="${field}"]`).removeClass('is-invalid');
        }
    }

    function validateForm() {
        let valid = true;
        const fields = ['name', 'weight', 'price', 'length', 'width', 'height'];

        // Reset lỗi
        $form.find('.error-message').text('');
        $form.find('.form-control').removeClass('is-invalid');

        const data = {};
        fields.forEach(f => data[f] = $form.find(`[name="${f}"]`).val().trim());

        // Kiểm tra từng trường
        if (!data.name) { showError('name', 'Tên hàng hóa không được để trống.'); valid = false; }
        if (!data.weight || data.weight <= 0) { showError('weight', 'Trọng lượng phải lớn hơn 0.'); valid = false; }
        if (data.price === '' || data.price < 0) { showError('price', 'Đơn giá phải >= 0.'); valid = false; }
        if (!data.length || data.length <= 0) { showError('length', 'Chiều dài không hợp lệ.'); valid = false; }
        if (!data.width || data.width <= 0) { showError('width', 'Chiều rộng không hợp lệ.'); valid = false; }
        if (!data.height || data.height <= 0) { showError('height', 'Chiều cao không hợp lệ.'); valid = false; }

        return valid ? data : null;
    }

    $form.on('submit', function(e) {
        e.preventDefault();
        const data = validateForm();
        if (!data) return;

        $.ajax({
            url: "{{ route('customer.account.product.store') }}",
            method: "POST",
            data: data,
            success: function(res) {
                if (res.success) {
                    Swal.fire('Thành công!', 'Đã thêm hàng hóa mới.', 'success');
                    modal.hide();
                    $form[0].reset();
                } else {
                    Swal.fire('Thất bại!', 'Không thể lưu hàng hóa.', 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Laravel trả lỗi validate
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(key => {
                        showError(key, errors[key][0]);
                    });
                } else {
                    Swal.fire('Lỗi!', xhr.responseJSON?.message || 'Đã xảy ra lỗi hệ thống.', 'error');
                }
            }
        });
    });

      // === XÓA SẢN PHẨM ===
$(document).on('click', '.btn-delete', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: "Bạn có chắc muốn xóa?",
        text: "Dữ liệu sẽ không thể khôi phục!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Xóa",
        cancelButtonText: "Hủy",
        confirmButtonColor: "#d33"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/customer/account/product/" + id,
                method: "DELETE",
                success: function(res) {
                    if (res.success) {
                        Swal.fire("Đã xóa!", "Hàng hóa đã bị xóa.", "success");
                        // location.reload();
                    } else {
                        Swal.fire("Lỗi!", "Không thể xóa hàng hóa.", "error");
                    }
                },
                error: function() {
                    Swal.fire("Lỗi!", "Không thể xóa hàng hóa.", "error");
                }
            });
        }
    });
});


// === SỬA SẢN PHẨM ===
// Bấm nút sửa -> lấy dữ liệu sản phẩm
$(document).on('click', '.btn-edit', function() {
    const id = $(this).data('id');
    $.ajax({
       url: "/customer/account/product-show/" + id,
        method: "GET",
        success: function(res) {
            if (res.success) {
                const p = res.product;
                $editForm.find('[name="id"]').val(p.id);
                $editForm.find('[name="name"]').val(p.name);
                $editForm.find('[name="weight"]').val(p.weight);
                $editForm.find('[name="price"]').val(p.price);
                $editForm.find('[name="length"]').val(p.length);
                $editForm.find('[name="width"]').val(p.width);
                $editForm.find('[name="height"]').val(p.height);
                editModal.show();
            } else {
                Swal.fire('Lỗi!', 'Không tìm thấy hàng hóa.', 'error');
            }
        },
        error: function() {
            Swal.fire('Lỗi!', 'Không thể lấy dữ liệu sản phẩm.', 'error');
        }
    });
});

// Gửi form sửa
$editForm.on('submit', function(e) {
    e.preventDefault();
    const id = $editForm.find('[name="id"]').val();
    const data = $editForm.serialize();

    $.ajax({
        url: "/customer/account/product-update/" + id,
        method: "PUT",
        data: data,
        success: function(res) {
            if (res.success) {
                Swal.fire('Thành công!', 'Đã cập nhật hàng hóa.', 'success');
                editModal.hide();
                // location.reload();
            } else {
                Swal.fire('Lỗi!', 'Không thể cập nhật hàng hóa.', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Lỗi!', xhr.responseJSON?.message || 'Đã xảy ra lỗi hệ thống.', 'error');
        }
    });
});

});

</script>
