<?php
namespace App\Models\Customer\Dashboard\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'name', 'quantity', 'weight', 'value',
        'length', 'width', 'height', 'specials'
    ];

    protected $casts = [
        'specials' => 'array',
    ];

    /**
     * ✅ Bản đồ dịch từ tiếng Anh -> Tiếng Việt cho specials
     */
    private static $specialsTranslation = [
        'high_value' => 'Giá trị cao',
        'oversized' => 'Quá khổ',
        'fragile' => 'Dễ vỡ',
        'liquid' => 'Chất lỏng',
        'bulk' => 'Nguyên khối',
        'battery' => 'Từ tính, Pin',
        'certificate' => 'Hóa đơn, Giấy chứng nhận',
    ];

    /**
     * ✅ Mutator: Tự động dịch specials khi lưu (dữ liệu mới)
     */
    protected function setSpecialsAttribute($value)
    {
        if (is_array($value)) {
            // Dịch từ Anh -> Việt khi lưu
            $translated = array_map(function ($item) {
                return self::$specialsTranslation[$item] ?? $item;
            }, $value);
            $this->attributes['specials'] = json_encode($translated, JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['specials'] = $value;
        }
    }

    /**
     * ✅ Accessor: Dịch specials từ Anh -> Việt khi lấy (bao gồm dữ liệu cũ)
     */
    protected function getSpecialsAttribute($value)
        {
            if ($value) {
                // Lấy specials từ database (dạng tiếng Anh)
                $data = json_decode($value, true) ?? [];
                
                // ✅ CHỈ dịch khi HIỂN THỊ trên view
                // Database vẫn giữ dạng tiếng Anh để backend tính phí
                return $data;
            }
            return [];
        }

    /**
     * ✅ Helper: Lấy specials dưới dạng string (hiển thị)
     */
       public function getSpecialsDisplayAttribute()
    {
        $specials = $this->specials ?? [];
        
        $translated = array_map(function ($item) {
            return self::$specialsTranslation[$item] ?? $item;
        }, $specials);
        
        return implode(', ', $translated);
    }

    /**
     * ✅ Helper: Kiểm tra xem có một đặc tính nào không (dùng tên Việt)
     */
    public function hasSpecial($specialKey)
    {
         return in_array($specialKey, $this->specials ?? []);
    }
}