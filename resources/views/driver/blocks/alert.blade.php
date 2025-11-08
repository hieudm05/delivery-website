
@if(session('success'))
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    Swal.fire({
        title: "{{ session('alert_title') ?? 'Thành công!' }}",
        html: "{!! session('success') !!}",
        icon: "success",
        confirmButtonText: 'OK',
        confirmButtonColor: '#28a745',
        timer: 5000,
        timerProgressBar: true,
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif

{{-- ERROR ALERT --}}
@if(session('error'))
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    Swal.fire({
        title: "{{ session('alert_title') ?? 'Lỗi!' }}",
        html: "{!! session('error') !!}",
        icon: "error",
        confirmButtonText: 'Đóng',
        confirmButtonColor: '#dc3545',
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif

{{-- WARNING ALERT --}}
@if(session('warning'))
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    Swal.fire({
        title: "{{ session('alert_title') ?? 'Cảnh báo!' }}",
        html: "{!! session('warning') !!}",
        icon: "warning",
        confirmButtonText: 'Đã hiểu',
        confirmButtonColor: '#ffc107',
        timer: 6000,
        timerProgressBar: true,
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif

{{-- INFO ALERT --}}
@if(session('info'))
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    Swal.fire({
        title: "{{ session('alert_title') ?? 'Thông báo' }}",
        html: "{!! session('info') !!}",
        icon: "info",
        confirmButtonText: 'OK',
        confirmButtonColor: '#17a2b8',
        timer: 5000,
        timerProgressBar: true,
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif

{{-- VALIDATION ERRORS --}}
@if($errors->any())
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    let errorList = '<ul style="text-align: left; margin: 0; padding-left: 20px;">';
    @foreach($errors->all() as $error)
        errorList += '<li>{{ $error }}</li>';
    @endforeach
    errorList += '</ul>';

    Swal.fire({
        title: "Vui lòng kiểm tra lại thông tin",
        html: errorList,
        icon: "error",
        confirmButtonText: 'Đóng',
        confirmButtonColor: '#dc3545',
        width: '600px',
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif

{{-- CUSTOM ALERT TYPE (nếu có) --}}
@if(session('alert_type') && session('alert_message'))
<script>
if (window.performance && window.performance.navigation.type === 2) {
    // Nếu là back/forward, không hiển thị lại alert
} else {
    const alertTypes = {
        'success': { title: 'Thành công!', icon: 'success', color: '#28a745' },
        'error': { title: 'Lỗi!', icon: 'error', color: '#dc3545' },
        'warning': { title: 'Cảnh báo!', icon: 'warning', color: '#ffc107' },
        'info': { title: 'Thông báo', icon: 'info', color: '#17a2b8' },
        'question': { title: 'Xác nhận', icon: 'question', color: '#6c757d' },
    };

    const type = "{{ session('alert_type') }}";
    const config = alertTypes[type] || alertTypes['info'];

    Swal.fire({
        title: "{{ session('alert_title') }}" || config.title,
        html: "{!! session('alert_message') !!}",
        icon: config.icon,
        confirmButtonText: 'OK',
        confirmButtonColor: config.color,
        timer: type === 'success' ? 5000 : null,
        timerProgressBar: type === 'success',
    }).then(() => {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
}
</script>
@endif

{{-- HELPER FUNCTIONS --}}
<script>
/**
 * Hàm confirm action với SweetAlert2
 * Dùng cho các nút submit form cần xác nhận
 */
function confirmAction(options = {}) {
    const defaults = {
        title: 'Bạn có chắc chắn?',
        text: 'Hành động này không thể hoàn tác!',
        icon: 'warning',
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
    };

    const config = { ...defaults, ...options };

    return new Promise((resolve, reject) => {
        Swal.fire({
            title: config.title,
            text: config.text,
            html: config.html,
            icon: config.icon,
            showCancelButton: true,
            confirmButtonColor: config.confirmButtonColor,
            cancelButtonColor: config.cancelButtonColor,
            confirmButtonText: config.confirmButtonText,
            cancelButtonText: config.cancelButtonText,
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(true);
            } else {
                reject(false);
            }
        });
    });
}

/**
 * Hàm confirm giao hàng thành công
 */
function confirmDelivery(formId) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error('Form not found:', formId);
        return;
    }

    // Kiểm tra GPS
    const lat = document.getElementById('delivery_latitude')?.value;
    const lng = document.getElementById('delivery_longitude')?.value;
    
    if (!lat || !lng) {
        Swal.fire({
            icon: 'error',
            title: 'Thiếu vị trí GPS',
            text: 'Vui lòng nhấn "Lấy vị trí hiện tại" trước khi xác nhận!',
            confirmButtonColor: '#dc3545',
        });
        return;
    }

    confirmAction({
        title: 'Xác nhận giao hàng thành công?',
        html: `
            <div style="text-align: left;">
                <p><strong>Vui lòng kiểm tra:</strong></p>
                <ul>
                    <li>Đã lấy vị trí GPS chính xác</li>
                    <li>Thông tin người nhận đúng</li>
                    <li>Đã chụp ảnh chứng từ đầy đủ</li>
                    <li>Đã thu COD (nếu có)</li>
                </ul>
            </div>
        `,
        icon: 'question',
        confirmButtonText: 'Xác nhận giao hàng',
        confirmButtonColor: '#28a745',
    }).then(() => {
        form.submit();
    }).catch(() => {
        // User cancelled
    });
}

/**
 * Hàm confirm báo cáo thất bại
 */
function confirmFailure(formId) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error('Form not found:', formId);
        return;
    }

    confirmAction({
        title: '⚠️ Xác nhận giao hàng thất bại?',
        html: `
            <div style="text-align: left;">
                <p><strong>Đơn hàng sẽ được chuyển về bưu cục</strong></p>
                <p>Vui lòng đảm bảo đã:</p>
                <ul>
                    <li>✓ Mô tả rõ lý do thất bại</li>
                    <li>✓ Chụp ảnh bằng chứng (nếu có)</li>
                    <li>✓ Lấy vị trí GPS hiện tại</li>
                </ul>
            </div>
        `,
        icon: 'warning',
        confirmButtonText: 'Xác nhận thất bại',
        confirmButtonColor: '#dc3545',
    }).then(() => {
        form.submit();
    }).catch(() => {
        // User cancelled
    });
}

/**
 * Hàm confirm bắt đầu giao hàng
 */
function confirmStartDelivery(orderId, route) {
    confirmAction({
        title: '📦 Bắt đầu giao hàng?',
        text: 'Đơn hàng #' + orderId + ' sẽ chuyển sang trạng thái "Đang giao"',
        icon: 'question',
        confirmButtonText: 'Bắt đầu giao',
        confirmButtonColor: '#17a2b8',
    }).then(() => {
        window.location.href = route;
    }).catch(() => {
        // User cancelled
    });
}

/**
 * Toast notification nhẹ (cho thông báo không quan trọng)
 */
function showToast(message, type = 'success', position = 'top-end') {
    const Toast = Swal.mixin({
        toast: true,
        position: position,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: type,
        title: message
    });
}

/**
 * Loading overlay
 */
function showLoading(message = 'Đang xử lý...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

/**
 * Confirm delete
 */
function confirmDelete(message = 'Bạn có chắc muốn xóa?', callback) {
    confirmAction({
        title: 'Xác nhận xóa',
        text: message,
        icon: 'warning',
        confirmButtonText: 'Xóa',
        confirmButtonColor: '#dc3545',
    }).then(() => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    }).catch(() => {
        // User cancelled
    });
}
</script>