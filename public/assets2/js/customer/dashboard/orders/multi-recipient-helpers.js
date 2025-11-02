/**
 * MULTI-RECIPIENT ORDER FORM HELPERS
 * Đặt tại: assets2/js/customer/dashboard/orders/multi-recipient-helpers.js
 */

// ============ CURRENCY FORMATTER ============
const formatCurrency = (amount) => {
    return (amount || 0).toLocaleString('vi-VN') + ' đ';
};

const parseCurrency = (text) => {
    return parseInt(text.replace(/[^\d]/g, '')) || 0;
};

// ============ VALIDATION HELPERS ============
const validatePhone = (phone) => {
    const pattern = /^(0|\+84)[0-9]{9,10}$/;
    return pattern.test(phone.trim());
};

const validateEmail = (email) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email.trim());
};

// ============ ADDRESS HELPERS ============
const findProvinceByCode = (code, data) => {
    return data.find(p => p.code === parseInt(code));
};

const findDistrictByCode = (provinceCode, districtCode, data) => {
    const province = findProvinceByCode(provinceCode, data);
    return province?.districts.find(d => d.code === parseInt(districtCode));
};

const findWardByCode = (provinceCode, districtCode, wardCode, data) => {
    const district = findDistrictByCode(provinceCode, districtCode, data);
    return district?.wards.find(w => w.code === parseInt(wardCode));
};

// ============ PRODUCT HELPERS ============
const calculateProductWeight = (products) => {
    return products.reduce((total, p) => {
        return total + (p.weight || 0) * (p.quantity || 1);
    }, 0);
};

const calculateProductValue = (products) => {
    return products.reduce((total, p) => {
        return total + (p.value || 0) * (p.quantity || 1);
    }, 0);
};

const hasSpecialProperty = (products, property) => {
    return products.some(p => {
        return p.specials && p.specials.includes(property);
    });
};

// ============ SERVICE HELPERS ============
const getServiceLabel = (serviceCode) => {
    const labels = {
        'fast': 'Giao nhanh',
        'insurance': 'Bảo hiểm',
        'cod': 'Thu hộ COD',
        'package': 'Đóng gói cẩn thận',
        'priority': 'Ưu tiên giao'
    };
    return labels[serviceCode] || serviceCode;
};

const getServiceFee = (serviceCode, baseAmount, productValue) => {
    const fees = {
        'fast': baseAmount * 0.15,
        'insurance': productValue * 0.01,
        'cod': 1000 + (baseAmount * 0.01),
        'package': 5000,
        'priority': 10000
    };
    return fees[serviceCode] || 0;
};

// ============ DATETIME HELPERS ============
const formatDatetimeDisplay = (datetime) => {
    if (!datetime) return '';
    const date = new Date(datetime);
    const options = { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleString('vi-VN', options);
};

const isValidFutureDate = (datetime) => {
    if (!datetime) return false;
    const date = new Date(datetime);
    const now = new Date();
    return date > now;
};

const getMinDeliveryTime = (pickupTime, hoursAfter = 1) => {
    if (!pickupTime) return null;
    const pickup = new Date(pickupTime);
    return new Date(pickup.getTime() + (hoursAfter * 60 * 60 * 1000));
};

// ============ NOTIFICATION HELPERS ============
const showSuccess = (message) => {
    // Nếu có thư viện toast (như Toastr), dùng nó
    if (typeof toastr !== 'undefined') {
        toastr.success(message);
    } else {
        alert('✅ ' + message);
    }
};

const showError = (message) => {
    if (typeof toastr !== 'undefined') {
        toastr.error(message);
    } else {
        alert('❌ ' + message);
    }
};

const showWarning = (message) => {
    if (typeof toastr !== 'undefined') {
        toastr.warning(message);
    } else {
        alert('⚠️ ' + message);
    }
};

const showInfo = (message) => {
    if (typeof toastr !== 'undefined') {
        toastr.info(message);
    } else {
        alert('ℹ️ ' + message);
    }
};

// ============ LOADING HELPERS ============
const showLoading = (element, text = 'Đang xử lý...') => {
    const $el = $(element);
    $el.data('original-html', $el.html());
    $el.prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm me-2"></span>${text}
    `);
};

const hideLoading = (element) => {
    const $el = $(element);
    const originalHtml = $el.data('original-html');
    if (originalHtml) {
        $el.html(originalHtml);
    }
    $el.prop('disabled', false);
};

// ============ DEBOUNCE HELPER ============
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// ============ LOCAL STORAGE HELPERS ============
const saveToLocalStorage = (key, data) => {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (e) {
        console.error('Không thể lưu vào localStorage:', e);
        return false;
    }
};

const loadFromLocalStorage = (key) => {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.error('Không thể đọc từ localStorage:', e);
        return null;
    }
};

const removeFromLocalStorage = (key) => {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (e) {
        console.error('Không thể xóa khỏi localStorage:', e);
        return false;
    }
};

// ============ DRAFT SAVE/LOAD ============
const saveDraft = (recipientsData) => {
    const draft = {
        timestamp: new Date().toISOString(),
        recipients: recipientsData
    };
    return saveToLocalStorage('order_draft', draft);
};

const loadDraft = () => {
    return loadFromLocalStorage('order_draft');
};

const clearDraft = () => {
    return removeFromLocalStorage('order_draft');
};

// ============ COPY TO CLIPBOARD ============
const copyToClipboard = (text) => {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showSuccess('Đã sao chép!');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
};

const fallbackCopyToClipboard = (text) => {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        showSuccess('Đã sao chép!');
    } catch (e) {
        showError('Không thể sao chép');
    }
    document.body.removeChild(textArea);
};

// ============ ARRAY HELPERS ============
const uniqueArray = (arr) => {
    return [...new Set(arr)];
};

const sumArray = (arr) => {
    return arr.reduce((sum, val) => sum + (parseFloat(val) || 0), 0);
};

const groupBy = (arr, key) => {
    return arr.reduce((result, item) => {
        const groupKey = item[key];
        if (!result[groupKey]) {
            result[groupKey] = [];
        }
        result[groupKey].push(item);
        return result;
    }, {});
};

// ============ STRING HELPERS ============
const truncate = (str, maxLength) => {
    if (!str) return '';
    return str.length > maxLength ? str.substring(0, maxLength) + '...' : str;
};

const slugify = (str) => {
    return str
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

// ============ CONFIRMATION DIALOG ============
const confirmAction = (message, onConfirm, onCancel) => {
    if (confirm(message)) {
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
        return true;
    } else {
        if (typeof onCancel === 'function') {
            onCancel();
        }
        return false;
    }
};

// ============ EXPORT ALL ============
window.OrderHelpers = {
    // Currency
    formatCurrency,
    parseCurrency,
    
    // Validation
    validatePhone,
    validateEmail,
    
    // Address
    findProvinceByCode,
    findDistrictByCode,
    findWardByCode,
    
    // Product
    calculateProductWeight,
    calculateProductValue,
    hasSpecialProperty,
    
    // Service
    getServiceLabel,
    getServiceFee,
    
    // Datetime
    formatDatetimeDisplay,
    isValidFutureDate,
    getMinDeliveryTime,
    
    // Notification
    showSuccess,
    showError,
    showWarning,
    showInfo,
    
    // Loading
    showLoading,
    hideLoading,
    
    // Utility
    debounce,
    
    // Storage
    saveToLocalStorage,
    loadFromLocalStorage,
    removeFromLocalStorage,
    saveDraft,
    loadDraft,
    clearDraft,
    
    // Clipboard
    copyToClipboard,
    
    // Array
    uniqueArray,
    sumArray,
    groupBy,
    
    // String
    truncate,
    slugify,
    
    // Confirmation
    confirmAction
};

console.log('✅ Order Helpers loaded');