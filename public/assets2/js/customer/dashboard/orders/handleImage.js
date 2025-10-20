
// ============ XỬ LÝ UPLOAD & PREVIEW ẢNH ============
let selectedImages = [];
const MAX_IMAGES = 5;
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

$('#order-images').on('change', function(e) {
    const files = Array.from(e.target.files);
    
    // Kiểm tra số lượng
    if (selectedImages.length + files.length > MAX_IMAGES) {
        alert(`⚠️ Chỉ được tải tối đa ${MAX_IMAGES} ảnh`);
        return;
    }
    
    // Validate từng file
    for (let file of files) {
        if (!file.type.startsWith('image/')) {
            alert('⚠️ Chỉ chấp nhận file ảnh');
            continue;
        }
        
        if (file.size > MAX_FILE_SIZE) {
            alert(`⚠️ File "${file.name}" vượt quá 5MB`);
            continue;
        }
        
        selectedImages.push(file);
    }
    
    renderImagePreviews();
    $(this).val(''); // Reset input để có thể chọn lại
});

function renderImagePreviews() {
    const container = $('#image-preview-container');
    container.html('');
    
    if (selectedImages.length === 0) return;
    
    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const html = `
                <div class="col-md-4 col-6">
                    <div class="image-preview-item">
                        <button type="button" class="remove-image" onclick="removeImage(${index})">×</button>
                        <img src="${e.target.result}" alt="Preview">
                        <div class="image-note">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="image_notes[]" 
                                   placeholder="Ghi chú ảnh (tùy chọn)">
                        </div>
                    </div>
                </div>
            `;
            container.append(html);
        };
        
        reader.readAsDataURL(file);
    });
    
    console.log(`📷 Đã chọn ${selectedImages.length} ảnh`);
}

function removeImage(index) {
    selectedImages.splice(index, 1);
    renderImagePreviews();
    console.log(`🗑️ Đã xóa ảnh #${index}`);
}


