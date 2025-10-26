@extends('driver.layouts.app')
@section('title', 'Quản lý tiền COD')

@section('content')
<div class="container-fluid py-4">
    <!-- SUMMARY CARDS -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-white mb-0">Cần trả về Admin</h6>
                    <h3 class="text-white mb-0">{{ number_format($totalPending) }} đ</h3>
                    <small>{{ $pending->count() }} giao dịch</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-white mb-0">Chờ Admin xác nhận</h6>
                    <h3 class="text-white mb-0">{{ number_format($totalTransferred) }} đ</h3>
                    <small>{{ $transferred->count() }} giao dịch</small>
                </div>
            </div>
        </div>
    </div>

    <!-- PENDING PAYMENTS -->
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h5><i class="bi bi-exclamation-circle text-warning"></i> Cần chuyển tiền cho Admin</h5>
        </div>
        <div class="card-body">
            @if($pending->isEmpty())
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Bạn không có tiền COD cần trả
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Đơn hàng</th>
                                <th>Ngày giao</th>
                                <th>Tiền COD</th>
                                <th>Phí ship</th>
                                <th>Tổng cần trả</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pending as $trans)
                            <tr>
                                <td>
                                    <a href="{{ route('driver.orders.show', $trans->order_id) }}">
                                        <strong>#{{ $trans->order_id }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <small>{{ $trans->order->updated_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($trans->cod_amount) }}đ</strong>
                                </td>
                                <td>
                                    {{ number_format($trans->shipping_fee) }}đ
                                </td>
                                <td>
                                    <strong class="text-danger">{{ number_format($trans->total_collected) }}đ</strong>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#transferModal{{ $trans->id }}">
                                        <i class="bi bi-send"></i> Chuyển tiền
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL CHUYỂN TIỀN -->
                            <div class="modal fade" id="transferModal{{ $trans->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('driver.cod.transfer', $trans->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Xác nhận chuyển tiền COD</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <strong>Đơn hàng:</strong> #{{ $trans->order_id }}<br>
                                                    <strong>Tổng tiền:</strong> {{ number_format($trans->total_collected) }}đ
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Phương thức chuyển <span class="text-danger">*</span></label>
                                                    <select name="method" class="form-select" required>
                                                        <option value="">-- Chọn phương thức --</option>
                                                        <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                                                        <option value="wallet">Ví điện tử (Momo, ZaloPay...)</option>
                                                        <option value="cash">Nộp tiền mặt tại văn phòng</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Ảnh chứng từ <span class="text-muted">(bắt buộc nếu chuyển khoản)</span></label>
                                                    <input type="file" name="proof" class="form-control" accept="image/*">
                                                    <small class="text-muted">Chụp ảnh biên lai chuyển khoản</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Ghi chú</label>
                                                    <textarea name="note" class="form-control" rows="3" placeholder="VD: Đã chuyển lúc 14:30, mã GD 123456..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="bi bi-check-circle"></i> Xác nhận đã chuyển
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- TRANSFERRED (WAITING CONFIRM) -->
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h5><i class="bi bi-clock text-info"></i> Chờ Admin xác nhận</h5>
        </div>
        <div class="card-body">
            @if($transferred->isEmpty())
                <p class="text-muted">Không có giao dịch nào</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Đơn hàng</th>
                                <th>Số tiền</th>
                                <th>Thời gian chuyển</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transferred as $trans)
                            <tr>
                                <td><strong>#{{ $trans->order_id }}</strong></td>
                                <td>{{ number_format($trans->total_collected) }}đ</td>
                                <td>{{ $trans->shipper_transfer_time?->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($trans->shipper_transfer_method === 'bank_transfer')
                                        Chuyển khoản
                                    @elseif($trans->shipper_transfer_method === 'wallet')
                                        Ví điện tử
                                    @else
                                        Tiền mặt
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning">Chờ xác nhận</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- CONFIRMED -->
    <div class="card">
        <div class="card-header pb-0">
            <h5><i class="bi bi-check-circle text-success"></i> Đã xác nhận</h5>
        </div>
        <div class="card-body">
            @if($confirmed->isEmpty())
                <p class="text-muted">Không có giao dịch nào</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Đơn hàng</th>
                                <th>Số tiền</th>
                                <th>Admin xác nhận</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($confirmed as $trans)
                            <tr>
                                <td><strong>#{{ $trans->order_id }}</strong></td>
                                <td>{{ number_format($trans->total_collected) }}đ</td>
                                <td>{{ $trans->admin_confirm_time?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-success">Đã xác nhận</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection