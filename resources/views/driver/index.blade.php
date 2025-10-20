@extends('driver.layouts.app')
@section('title','Trang quản trị')
@section('content')
     <h4 class="mb-4">
        <i class="fas fa-list me-2"></i>Danh Sách Đơn Hàng
      </h4>

      <!-- Statistics -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm" style="border-top: 3px solid #0d6efd !important;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h3 class="mb-1 fw-bold stat-card-value">12</h3>
                  <p class="text-secondary small text-uppercase mb-0 stat-card-title" style="font-size: 0.75rem; font-weight: 600;">Đơn Hôm Nay</p>
                </div>
                <i class="fas fa-box text-primary stat-icon" style="font-size: 2rem; opacity: 0.2;"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm" style="border-top: 3px solid #198754 !important;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h3 class="mb-1 fw-bold stat-card-value">8</h3>
                  <p class="text-secondary small text-uppercase mb-0 stat-card-title" style="font-size: 0.75rem; font-weight: 600;">Đã Giao</p>
                </div>
                <i class="fas fa-check-circle text-success stat-icon" style="font-size: 2rem; opacity: 0.2;"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm" style="border-top: 3px solid #ffc107 !important;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h3 class="mb-1 fw-bold stat-card-value">3</h3>
                  <p class="text-secondary small text-uppercase mb-0 stat-card-title" style="font-size: 0.75rem; font-weight: 600;">Đang Giao</p>
                </div>
                <i class="fas fa-truck text-warning stat-icon" style="font-size: 2rem; opacity: 0.2;"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm" style="border-top: 3px solid #dc3545 !important;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h3 class="mb-1 fw-bold stat-card-value">1</h3>
                  <p class="text-secondary small text-uppercase mb-0 stat-card-title" style="font-size: 0.75rem; font-weight: 600;">Thất Bại</p>
                </div>
                <i class="fas fa-exclamation-circle text-danger stat-icon" style="font-size: 2rem; opacity: 0.2;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="card shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Mã Đơn</th>
                <th>Khách Hàng</th>
                <th class="d-none d-md-table-cell">Địa Chỉ Giao</th>
                <th>Giá Tiền</th>
                <th>Trạng Thái</th>
                <th class="d-none d-lg-table-cell">Ngày Tạo</th>
                <th>Thao Tác</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="fw-bold">#ORD001</td>
                <td>Nguyễn Văn A</td>
                <td class="d-none d-md-table-cell">123 Nguyễn Huệ, Q.1</td>
                <td class="fw-bold text-primary">85.000đ</td>
                <td><span class="badge bg-success">Đã Giao</span></td>
                <td class="d-none d-lg-table-cell">24/10/2025</td>
                <td>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success" title="Gọi điện">
                      <i class="fas fa-phone"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="fw-bold">#ORD002</td>
                <td>Trần Thị B</td>
                <td class="d-none d-md-table-cell">456 Lê Lợi, Q.4</td>
                <td class="fw-bold text-primary">120.000đ</td>
                <td><span class="badge bg-warning text-dark">Đang Giao</span></td>
                <td class="d-none d-lg-table-cell">24/10/2025</td>
                <td>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success" title="Gọi điện">
                      <i class="fas fa-phone"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="fw-bold">#ORD003</td>
                <td>Lê Văn C</td>
                <td class="d-none d-md-table-cell">789 Trần Hưng Đạo, Q.5</td>
                <td class="fw-bold text-primary">95.000đ</td>
                <td><span class="badge bg-success">Đã Giao</span></td>
                <td class="d-none d-lg-table-cell">23/10/2025</td>
                <td>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success" title="Gọi điện">
                      <i class="fas fa-phone"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="fw-bold">#ORD004</td>
                <td>Phạm Thị D</td>
                <td class="d-none d-md-table-cell">321 Hai Bà Trưng, Q.3</td>
                <td class="fw-bold text-primary">150.000đ</td>
                <td><span class="badge bg-danger">Thất Bại</span></td>
                <td class="d-none d-lg-table-cell">23/10/2025</td>
                <td>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success" title="Gọi điện">
                      <i class="fas fa-phone"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center p-3 border-top bg-light gap-2">
          <small class="text-secondary">Hiển thị 1–4 của 47 đơn hàng</small>
          <div>
            <button class="btn btn-sm btn-outline-secondary" disabled>
              <i class="fas fa-chevron-left"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary ms-2">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
@endsection