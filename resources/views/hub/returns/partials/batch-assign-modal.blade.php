<!-- Modal Phân Công Hàng Loạt - Fixed -->
<div class="modal fade" id="batchAssignModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="left: 100px">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog"></i> Phân Công Tài Xế Hoàn Hàng Hàng Loạt
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Thống kê đơn đã chọn -->
                <div class=" mb-4">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="small text-muted">Đã chọn</div>
                            <div class="h4 mb-0" id="totalSelected">0</div>
                            <small>đơn hoàn</small>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Đã phân</div>
                            <div class="h4 mb-0 text-success" id="totalAssigned">0</div>
                            <small>đơn</small>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Tổng phí hoàn</div>
                            <div class="h4 mb-0 text-danger" id="totalFee">0đ</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Tổng COD</div>
                            <div class="h4 mb-0 text-warning" id="totalCod">0đ</div>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadDrivers()">
                        <i class="fas fa-sync"></i> Tải tài xế
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="autoAssign()">
                        <i class="fas fa-magic"></i> Gợi ý phân công
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllAssignments()">
                        <i class="fas fa-eraser"></i> Xóa tất cả
                    </button>
                </div>

                <!-- Danh sách đơn hoàn -->
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th width="80">Đơn hàng</th>
                                <th width="150">Sender</th>
                                <th>Địa chỉ</th>
                                <th width="100">Phí/COD</th>
                                <th width="250">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>Chọn tài xế</span>
                                        <select id="bulkDriverSelect" class="form-select form-select-sm" style="width: auto;">
                                            <option value="">-- Áp dụng cho tất cả --</option>
                                        </select>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="returnsList">
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Đang tải dữ liệu...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Note chung -->
                <div class="mt-3">
                    <label class="form-label fw-bold">Ghi chú chung (tùy chọn)</label>
                    <textarea id="batchNote" class="form-control" rows="2" 
                              placeholder="Ghi chú áp dụng cho tất cả đơn hoàn..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="button" class="btn btn-primary" id="submitBatchBtn" onclick="submitBatchAssign()">
                    <i class="fas fa-check"></i> Xác nhận phân công (<span id="confirmCount">0</span> đơn)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ===== GLOBAL VARIABLES =====
let selectedReturns = [];
let availableDrivers = [];
let assignments = {}; // { return_id: driver_id }

// ===== KHỞI TẠO MODAL =====
const batchModalElement = document.getElementById('batchAssignModal');
if (batchModalElement) {
    batchModalElement.addEventListener('show.bs.modal', function() {
        console.log('Modal opening...');
        initBatchAssign();
    });
}

function initBatchAssign() {
    console.log('Init batch assign...');
    
    // Reset
    assignments = {};
    
    // Lấy danh sách đơn đã chọn
    const checkboxes = document.querySelectorAll('.return-checkbox:checked');
    selectedReturns = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    console.log('Selected returns:', selectedReturns);
    
    if (selectedReturns.length === 0) {
        alert('Vui lòng chọn ít nhất một đơn để phân công');
        const modalInstance = bootstrap.Modal.getInstance(batchModalElement);
        if (modalInstance) {
            modalInstance.hide();
        }
        return;
    }

    // Load dữ liệu
    loadSelectedReturns();
    loadDrivers();
}

// ===== LOAD DANH SÁCH ĐƠN ĐÃ CHỌN =====
async function loadSelectedReturns() {
    console.log('Loading selected returns...');
    
    try {
        const response = await fetch('/hub/returns/selected-info', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ return_ids: selectedReturns })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Returns data:', data);
        
        // Cập nhật thống kê
        document.getElementById('totalSelected').textContent = data.returns.length;
        document.getElementById('totalFee').textContent = data.total_fee + 'đ';
        document.getElementById('totalCod').textContent = data.total_cod + 'đ';

        // Render danh sách
        renderReturnsList(data.returns);

    } catch (error) {
        console.error('Error loading returns:', error);
        showError('Không thể tải thông tin đơn hoàn: ' + error.message);
    }
}

// ===== RENDER DANH SÁCH ĐƠN =====
function renderReturnsList(returns) {
    console.log('Rendering returns list...');
    
    const tbody = document.getElementById('returnsList');
    
    if (returns.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    Không có đơn hoàn nào
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = returns.map(ret => `
        <tr data-return-id="${ret.id}">
            <td>
                <strong class="text-primary">#${ret.order_id}</strong>
                ${ret.failed_attempts >= 3 ? `<span class="badge bg-danger ms-1">${ret.failed_attempts}x</span>` : ''}
            </td>
            <td>
                <div class="fw-bold small">${ret.sender_name}</div>
                <small class="text-muted">${ret.sender_phone}</small>
            </td>
            <td>
                <small class="text-muted" style="display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                    ${ret.sender_address}
                </small>
            </td>
            <td>
                <div class="small">
                    <div class="text-danger">Phí: ${ret.return_fee}đ</div>
                    ${parseInt(ret.cod_amount) > 0 ? `<div class="text-warning">COD: ${ret.cod_amount}đ</div>` : ''}
                </div>
            </td>
            <td>
                <select class="form-select form-select-sm driver-select" 
                        data-return-id="${ret.id}"
                        onchange="updateAssignment(${ret.id}, this.value)">
                    <option value="">-- Chọn tài xế --</option>
                </select>
            </td>
        </tr>
    `).join('');

    console.log('Returns rendered');
}

// ===== LOAD DANH SÁCH TÀI XẾ =====
async function loadDrivers() {
    console.log('Loading drivers...');
    
    try {
        const response = await fetch('/hub/returns/batch-available-drivers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Drivers data:', data);
        
        availableDrivers = data.drivers;
        
        // Update driver selects
        updateDriverSelects();
        updateBulkDriverSelect();

    } catch (error) {
        console.error('Error loading drivers:', error);
        showError('Không thể tải danh sách tài xế: ' + error.message);
    }
}

// ===== CẬP NHẬT DRIVER SELECTS =====
function updateDriverSelects() {
    console.log('Updating driver selects...');
    
    const selects = document.querySelectorAll('.driver-select');
    
    selects.forEach(select => {
        const currentValue = select.value;
        
        select.innerHTML = '<option value="">-- Chọn tài xế --</option>' + 
            availableDrivers.map(driver => `
                <option value="${driver.id}">
                    ${driver.name} 
                    ${driver.active_returns > 0 ? `(Đang hoàn: ${driver.active_returns})` : ''}
                </option>
            `).join('');
        
        // Restore value if existed
        if (currentValue) {
            select.value = currentValue;
        }
    });
}

// ===== CẬP NHẬT BULK DRIVER SELECT =====
function updateBulkDriverSelect() {
    console.log('Updating bulk driver select...');
    
    const bulkSelect = document.getElementById('bulkDriverSelect');
    
    bulkSelect.innerHTML = '<option value="">-- Áp dụng cho tất cả --</option>' + 
        availableDrivers.map(driver => `
            <option value="${driver.id}">
                ${driver.name} (${driver.phone})
                ${driver.active_returns > 0 ? ` - Đang hoàn: ${driver.active_returns}` : ''}
            </option>
        `).join('');

    // Event: Apply to all
    bulkSelect.onchange = function() {
        if (this.value) {
            applyDriverToAll(this.value);
            this.value = ''; // Reset
        }
    };
}

// ===== ÁP DỤNG TÀI XẾ CHO TẤT CẢ =====
function applyDriverToAll(driverId) {
    console.log('Applying driver to all:', driverId);
    
    const selects = document.querySelectorAll('.driver-select');
    
    selects.forEach(select => {
        select.value = driverId;
        const returnId = parseInt(select.dataset.returnId);
        assignments[returnId] = driverId;
    });

    updateAssignmentCount();
}

// ===== CẬP NHẬT ASSIGNMENT =====
function updateAssignment(returnId, driverId) {
    console.log('Update assignment:', returnId, driverId);
    
    if (driverId) {
        assignments[returnId] = parseInt(driverId);
    } else {
        delete assignments[returnId];
    }
    
    updateAssignmentCount();
}

// ===== CẬP NHẬT SỐ LƯỢNG ĐÃ PHÂN =====
function updateAssignmentCount() {
    const count = Object.keys(assignments).length;
    console.log('Assignment count:', count, assignments);
    
    document.getElementById('totalAssigned').textContent = count;
    document.getElementById('confirmCount').textContent = count;
}

// ===== GỢI Ý PHÂN CÔNG TỰ ĐỘNG =====
async function autoAssign() {
    console.log('Auto assigning...');
    
    if (availableDrivers.length === 0) {
        showError('Chưa có tài xế nào khả dụng');
        return;
    }

    try {
        const response = await fetch('/hub/returns/suggest-assignments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ return_ids: selectedReturns })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Suggestions:', data);
        
        // Apply suggestions
        data.suggestions.forEach(suggestion => {
            const select = document.querySelector(`.driver-select[data-return-id="${suggestion.return_id}"]`);
            if (select) {
                select.value = suggestion.driver_id;
                assignments[suggestion.return_id] = suggestion.driver_id;
            }
        });

        updateAssignmentCount();
        showSuccess('Đã áp dụng gợi ý phân công thông minh');

    } catch (error) {
        console.error('Error auto assigning:', error);
        showError('Không thể tự động phân công: ' + error.message);
    }
}

// ===== XÓA TẤT CẢ PHÂN CÔNG =====
function clearAllAssignments() {
    console.log('Clearing all assignments...');
    
    const selects = document.querySelectorAll('.driver-select');
    selects.forEach(select => select.value = '');
    assignments = {};
    updateAssignmentCount();
}

// ===== SUBMIT PHÂN CÔNG =====
async function submitBatchAssign() {
    console.log('Submitting batch assign...', assignments);
    
    // Validate
    if (Object.keys(assignments).length === 0) {
        showError('Vui lòng chọn tài xế cho ít nhất một đơn');
        return;
    }

    // Confirm
    const confirmed = confirm(`Xác nhận phân công ${Object.keys(assignments).length} đơn hoàn?`);
    if (!confirmed) return;

    // Disable button
    const submitBtn = document.getElementById('submitBatchBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    // Prepare data
    const assignmentsArray = Object.entries(assignments).map(([return_id, driver_id]) => ({
        return_id: parseInt(return_id),
        driver_id: parseInt(driver_id)
    }));

    const note = document.getElementById('batchNote').value.trim();

    console.log('Sending data:', {
        assignments: assignmentsArray,
        note: note || null
    });

    try {
        const response = await fetch('/hub/returns/batch-assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                assignments: assignmentsArray,
                note: note || null
            })
        });

        console.log('Response status:', response.status);

        if (response.ok) {
            showSuccess('Phân công thành công! Đang tải lại trang...');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            const errorData = await response.json();
            console.error('Error response:', errorData);
            showError(errorData.message || 'Có lỗi xảy ra khi phân công');
            
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Xác nhận phân công (<span id="confirmCount">' + Object.keys(assignments).length + '</span> đơn)';
        }

    } catch (error) {
        console.error('Error submitting batch assign:', error);
        showError('Không thể thực hiện phân công: ' + error.message);
        
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Xác nhận phân công (<span id="confirmCount">' + Object.keys(assignments).length + '</span> đơn)';
    }
}

// ===== HELPER FUNCTIONS =====
function showSuccess(message) {
    console.log('Success:', message);
    alert('✅ ' + message);
}

function showError(message) {
    console.error('Error:', message);
    alert('❌ ' + message);
}
</script>

<style>
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8f9fa;
}
</style>