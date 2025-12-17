@extends('hub.layouts.app')
@section('title', 'Quản lý tiền COD')

@section('content')
<div class="container">
    @php
        use App\Models\Customer\Dashboard\Orders\CodTransaction;
    @endphp
    
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-building text-primary"></i> Quản lý tiền COD - Hub
            </h3>
            <p class="text-muted mb-0">Driver → Hub → Sender & Driver Commission & System</p>
        </div>
        <div>
            <a href="{{ route('hub.cod.statistics') }}" class="btn btn-info">
                <i class="bi bi-graph-up"></i> Thống kê
            </a>
        </div>
    </div>

    <!-- THỐNG KÊ -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ xác nhận từ Driver
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['waiting_confirm']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ CARD MỚI: Chờ xác nhận phí từ Customer -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Chờ xác nhận phí
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending_fee_confirm']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-coin fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Chờ trả Driver
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending_driver_commission']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Chờ trả Sender
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending_sender']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-send fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG GIAO DỊCH -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-list-ul"></i> Danh sách giao dịch COD
            </h5>
            
            @if($tab === 'pending_fee_confirm')
            <button type="button" class="btn btn-success btn-sm" 
                    data-bs-toggle="modal" 
                    data-bs-target="#batchConfirmFeeModal"
                    id="batchConfirmFeeBtn">
                <i class="bi bi-check2-all"></i> Xác nhận hàng loạt
            </button>
            @endif
        </div>

        <div class="card-body">
            <!-- TABS -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'waiting_confirm' ? 'active' : '' }}" 
                       href="?tab=waiting_confirm">
                        <i class="bi bi-clock"></i> Chờ XN từ Driver
                    </a>
                </li>
                
                <!-- ✅ TAB MỚI: Chờ xác nhận phí -->
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'pending_fee_confirm' ? 'active' : '' }}" 
                       href="?tab=pending_fee_confirm">
                        <i class="bi bi-cash-coin"></i> Chờ XN phí
                        @if($stats['pending_fee_confirm'] > 0)
                        <span class="badge bg-success ms-1">
                            {{ CodTransaction::byHub(Auth::id())->where('sender_fee_status', 'transferred')->count() }}
                        </span>
                        @endif
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'pending_driver_commission' ? 'active' : '' }}" 
                       href="?tab=pending_driver_commission">
                        <i class="bi bi-truck"></i> Chờ trả Driver
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'pending_sender' ? 'active' : '' }}" 
                       href="?tab=pending_sender">
                        <i class="bi bi-send"></i> Chờ trả Sender
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'completed' ? 'active' : '' }}" 
                       href="?tab=completed">
                        <i class="bi bi-check-circle"></i> Đã hoàn tất
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'pending_system' ? 'active' : '' }}" 
                       href="?tab=pending_system">
                        <i class="bi bi-database"></i> Chờ nộp hệ thống
                    </a>
                </li>
            </ul>

            @if($transactions->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Không có giao dịch nào
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                @if(in_array($tab, ['pending_fee_confirm', 'pending_driver_commission', 'pending_system']))
                                <th><input type="checkbox" id="selectAll"></th>
                                @endif
                                <th>Mã GD</th>
                                <th>Đơn hàng</th>
                                <th>Người liên quan</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $trans)
                            <tr>
                                @if(in_array($tab, ['pending_fee_confirm', 'pending_driver_commission', 'pending_system']))
                                <td>
                                    <input type="checkbox" class="transaction-checkbox" value="{{ $trans->id }}">
                                </td>
                                @endif
                                
                                <td><strong class="text-primary">#{{ $trans->id }}</strong></td>
                                
                                <td>
                                    <a href="{{ route('hub.orders.show', $trans->order_id) }}" target="_blank">
                                        #{{ $trans->order_id }}
                                    </a>
                                </td>
                                
                                <td>
                                    @if($tab === 'pending_fee_confirm')
                                        <div>
                                            <strong>{{ $trans->sender->full_name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">Customer</small>
                                        </div>
                                    @elseif($tab === 'pending_driver_commission')
                                        <div>
                                            <strong>{{ $trans->driver->full_name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">Driver</small>
                                        </div>
                                    @else
                                        <div>
                                            <strong>{{ $trans->sender->full_name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $trans->sender->phone ?? '' }}</small>
                                        </div>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($tab === 'pending_fee_confirm')
                                        <strong class="text-success">{{ number_format($trans->sender_fee_paid) }}đ</strong><br>
                                        <small class="text-muted">Phí dịch vụ</small>
                                    @elseif($tab === 'pending_driver_commission')
                                        <strong class="text-primary">{{ number_format($trans->driver_commission) }}đ</strong><br>
                                        <small class="text-muted">Commission</small>
                                    @elseif($tab === 'pending_system')
                                        <strong class="text-danger">{{ number_format($trans->hub_system_amount) }}đ</strong><br>
                                        <small class="text-muted">Platform Fee</small>
                                    @else
                                        <strong class="text-success">{{ number_format($trans->total_collected) }}đ</strong><br>
                                        <small class="text-muted">COD: {{ number_format($trans->cod_amount) }}đ</small>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($tab === 'waiting_confirm')
                                        <span class="badge bg-warning">Chờ xác nhận</span>
                                    @elseif($tab === 'pending_fee_confirm')
                                        <span class="badge bg-success">Chờ XN phí</span>
                                    @elseif($tab === 'pending_driver_commission')
                                        <span class="badge bg-primary">Chờ trả driver</span>
                                    @elseif($tab === 'pending_sender')
                                        <span class="badge bg-info">Chờ trả sender</span>
                                    @elseif($tab === 'completed')
                                        <span class="badge bg-success">Đã hoàn tất</span>
                                    @elseif($tab === 'pending_system')
                                        <span class="badge bg-danger">Chờ nộp</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <small>{{ $trans->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                         <a href="{{ route('hub.cod.show', $trans->id) }}" 
                                           class="btn btn-outline-info"
                                           title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($tab === 'pending_fee_confirm')
                                            <button type="button" 
                                                    class="btn btn-outline-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmFeeModal{{ $trans->id }}"
                                                    title="Xác nhận phí">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectFeeModal{{ $trans->id }}"
                                                    title="Từ chối">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- ✅ MODAL XÁC NHẬN PHÍ -->
                            @if($tab === 'pending_fee_confirm')
                            <div class="modal fade" id="confirmFeeModal{{ $trans->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('hub.cod.confirm-customer-fee', $trans->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-check-circle"></i> Xác nhận nhận phí
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <p class="mb-2"><strong>Customer:</strong> {{ $trans->sender->full_name }}</p>
                                                    <p class="mb-2"><strong>Số tiền:</strong> 
                                                        <span class="text-success fs-5">{{ number_format($trans->sender_fee_paid) }}đ</span>
                                                    </p>
                                                    <p class="mb-0"><strong>Phương thức:</strong> {{ ucfirst($trans->sender_fee_payment_method ?? 'N/A') }}</p>
                                                </div>

                                                @if($trans->sender_fee_payment_proof)
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Chứng từ thanh toán:</label>
                                                    <div>
                                                        <img src="{{ asset('storage/' . $trans->sender_fee_payment_proof) }}" 
                                                             alt="Proof" 
                                                             class="img-fluid rounded border"
                                                             style="max-height: 300px;">
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Ghi chú</label>
                                                    <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú xác nhận..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-success">Xác nhận đã nhận</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- ✅ MODAL TỪ CHỐI PHÍ -->
                            <div class="modal fade" id="rejectFeeModal{{ $trans->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('hub.cod.reject-customer-fee', $trans->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">
                                                    <i class="bi bi-x-circle"></i> Từ chối thanh toán
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <p class="mb-0">Customer sẽ phải thanh toán lại sau khi bị từ chối</p>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Lý do từ chối <span class="text-danger">*</span></label>
                                                    <textarea name="reason" class="form-control" rows="3" required 
                                                              placeholder="VD: Sai số tiền, ảnh không rõ ràng..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-danger">Từ chối</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $transactions->appends(['tab' => $tab])->links() }}
            @endif
        </div>
    </div>
</div>

<!-- ✅ MODAL XÁC NHẬN HÀNG LOẠT -->
<div class="modal fade" id="batchConfirmFeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('hub.cod.batch-confirm-customer-fees') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check2-all"></i> Xác nhận phí hàng loạt
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="selectedFees"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú chung..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận đã nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelected();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelected);
    });

    function updateSelected() {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        const container = document.getElementById('selectedFees') || 
                         document.getElementById('selectedCommissions') || 
                         document.getElementById('selectedTransactions');
        
        if (container) {
            if (selected.length > 0) {
                container.innerHTML = `
                    <div class="alert alert-success">
                        <strong>Đã chọn ${selected.length} giao dịch</strong>
                        ${selected.map(id => `<input type="hidden" name="transaction_ids[]" value="${id}">`).join('')}
                    </div>
                `;
            } else {
                container.innerHTML = '<div class="alert alert-warning">Chưa chọn giao dịch nào</div>';
            }
        }
    }
});
</script>

<style>
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-danger { border-left: 4px solid #e74a3b !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
</style>
@endsection