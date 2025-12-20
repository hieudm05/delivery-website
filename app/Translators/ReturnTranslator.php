<?php

namespace App\Translators;

/**
 * ✅ TRANSLATOR CHO ORDER RETURN
 * Dùng để việt hoá tất cả các enum, constant của hoàn hàng
 */
class ReturnTranslator
{
    /**
     * ✅ LY DO HOAN HANG (reason_type)
     */
    public static function getReasonLabel($reasonType, $withIcon = true)
    {
        $labels = [
            'auto_failed' => [
                'label' => 'Tự động (>3 lần thất bại)',
                'icon' => '<i class="fas fa-robot text-danger"></i>',
                'color' => 'danger'
            ],
            'hub_decision' => [
                'label' => 'Hub quyết định',
                'icon' => '<i class="fas fa-building text-primary"></i>',
                'color' => 'primary'
            ],
            'customer_request' => [
                'label' => 'Khách hàng yêu cầu',
                'icon' => '<i class="fas fa-user text-info"></i>',
                'color' => 'info'
            ],
            'wrong_info' => [
                'label' => 'Thông tin sai',
                'icon' => '<i class="fas fa-exclamation-triangle text-warning"></i>',
                'color' => 'warning'
            ],
            'other' => [
                'label' => 'Lý do khác',
                'icon' => '<i class="fas fa-ellipsis-h text-secondary"></i>',
                'color' => 'secondary'
            ],
        ];

        $data = $labels[$reasonType] ?? [
            'label' => 'Không xác định',
            'icon' => '<i class="fas fa-question-circle"></i>',
            'color' => 'secondary'
        ];

        if ($withIcon) {
            return $data['icon'] . ' ' . $data['label'];
        }

        return $data['label'];
    }

    /**
     * ✅ LAY CHI BADGE CHO LY DO
     */
    public static function getReasonBadge($reasonType)
    {
        $label = self::getReasonLabel($reasonType, false);
        $color = self::getReasonColor($reasonType);

        return "<span class=\"badge bg-{$color}\">{$label}</span>";
    }

    /**
     * ✅ LAY MAU CHO LY DO
     */
    public static function getReasonColor($reasonType)
    {
        $colors = [
            'auto_failed' => 'danger',
            'hub_decision' => 'primary',
            'customer_request' => 'info',
            'wrong_info' => 'warning',
            'other' => 'secondary',
        ];

        return $colors[$reasonType] ?? 'secondary';
    }

    /**
     * ✅ TINH TRANG HANG (package_condition)
     */
    public static function getConditionLabel($condition, $withIcon = true)
    {
        $labels = [
            'good' => [
                'label' => 'Nguyên vẹn',
                'icon' => '<i class="fas fa-check-circle text-success"></i>',
                'color' => 'success'
            ],
            'damaged' => [
                'label' => 'Hư hỏng',
                'icon' => '<i class="fas fa-exclamation-circle text-danger"></i>',
                'color' => 'danger'
            ],
            'opened' => [
                'label' => 'Đã mở',
                'icon' => '<i class="fas fa-box-open text-warning"></i>',
                'color' => 'warning'
            ],
            'missing' => [
                'label' => 'Thiếu sót',
                'icon' => '<i class="fas fa-times-circle text-dark"></i>',
                'color' => 'dark'
            ],
        ];

        $data = $labels[$condition] ?? [
            'label' => 'Không xác định',
            'icon' => '<i class="fas fa-question-circle"></i>',
            'color' => 'secondary'
        ];

        if ($withIcon) {
            return $data['icon'] . ' ' . $data['label'];
        }

        return $data['label'];
    }

    /**
     * ✅ LAY CHI BADGE CHO TINH TRANG
     */
    public static function getConditionBadge($condition)
    {
        $label = self::getConditionLabel($condition, false);
        $color = self::getConditionColor($condition);

        return "<span class=\"badge bg-{$color}\">" . self::getConditionLabel($condition) . "</span>";
    }

    /**
     * ✅ LAY MAU CHO TINH TRANG
     */
    public static function getConditionColor($condition)
    {
        $colors = [
            'good' => 'success',
            'damaged' => 'danger',
            'opened' => 'warning',
            'missing' => 'dark',
        ];

        return $colors[$condition] ?? 'secondary';
    }

    /**
     * ✅ TRANG THAI HOAN (status)
     */
    public static function getStatusLabel($status, $withIcon = true)
    {
        $labels = [
            'pending' => [
                'label' => 'Chờ hoàn về',
                'icon' => '<i class="fas fa-hourglass-half text-warning"></i>',
                'color' => 'warning',
                'badge' => 'warning'
            ],
            'assigned' => [
                'label' => 'Đã phân công',
                'icon' => '<i class="fas fa-user-check text-info"></i>',
                'color' => 'info',
                'badge' => 'info'
            ],
            'returning' => [
                'label' => 'Đang hoàn về',
                'icon' => '<i class="fas fa-shipping-fast text-primary"></i>',
                'color' => 'primary',
                'badge' => 'primary'
            ],
            'completed' => [
                'label' => 'Hoàn thành',
                'icon' => '<i class="fas fa-check-circle text-success"></i>',
                'color' => 'success',
                'badge' => 'success'
            ],
            'cancelled' => [
                'label' => 'Đã hủy',
                'icon' => '<i class="fas fa-times-circle text-danger"></i>',
                'color' => 'danger',
                'badge' => 'danger'
            ],
        ];

        $data = $labels[$status] ?? [
            'label' => 'Không xác định',
            'icon' => '<i class="fas fa-question-circle"></i>',
            'color' => 'secondary',
            'badge' => 'secondary'
        ];

        if ($withIcon) {
            return $data['icon'] . ' ' . $data['label'];
        }

        return $data['label'];
    }

    /**
     * ✅ LAY BADGE CHO TRANG THAI
     */
    public static function getStatusBadge($status)
    {
        $label = self::getStatusLabel($status, false);
        $badgeColor = self::getStatusBadgeColor($status);

        return "<span class=\"badge bg-{$badgeColor}\">{$label}</span>";
    }

    /**
     * ✅ LAY MAU BADGE CHO TRANG THAI
     */
    public static function getStatusBadgeColor($status)
    {
        $colors = [
            'pending' => 'warning',
            'assigned' => 'info',
            'returning' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * ✅ QUAN HE NGUOI NHAN (received_by_relation)
     */
    public static function getRelationLabel($relation)
    {
        $labels = [
            'self' => 'Chính sender',
            'family' => 'Người thân',
            'staff' => 'Nhân viên',
            'other' => 'Khác',
        ];

        return $labels[$relation] ?? 'Không xác định';
    }

    /**
     * ✅ TRANG THAI CHI TIET (Detailed status info)
     */
    public static function getDetailedStatus($status)
    {
        $details = [
            'pending' => [
                'text' => 'Chờ phân công tài xế',
                'description' => 'Hub đang tìm tài xế phù hợp để hoàn hàng',
                'color' => 'warning'
            ],
            'assigned' => [
                'text' => 'Tài xế đã nhận việc',
                'description' => 'Tài xế đã được phân công, chờ bắt đầu hoàn hàng',
                'color' => 'info'
            ],
            'returning' => [
                'text' => 'Đang hoàn hàng',
                'description' => 'Tài xế đang trên đường đến sender để hoàn hàng',
                'color' => 'primary'
            ],
            'completed' => [
                'text' => 'Hoàn thành',
                'description' => 'Hàng đã hoàn thành cho sender',
                'color' => 'success'
            ],
            'cancelled' => [
                'text' => 'Đã hủy',
                'description' => 'Hoàn hàng đã bị hủy',
                'color' => 'danger'
            ],
        ];

        return $details[$status] ?? [
            'text' => 'Không xác định',
            'description' => 'Trạng thái không rõ ràng',
            'color' => 'secondary'
        ];
    }

    /**
     * ✅ ARRAY CUC LAY - DUNG CHO SELECT, DROPDOWN
     */
    public static function getReasonTypeOptions()
    {
        return [
            'auto_failed' => 'Tự động (>3 lần thất bại)',
            'hub_decision' => 'Hub quyết định',
            'customer_request' => 'Khách hàng yêu cầu',
            'wrong_info' => 'Thông tin sai',
            'other' => 'Lý do khác',
        ];
    }

    public static function getConditionOptions()
    {
        return [
            'good' => '✅ Nguyên vẹn',
            'damaged' => '⚠️ Hư hỏng',
            'opened' => '📦 Đã mở',
            'missing' => '❌ Thiếu sót',
        ];
    }

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Chờ hoàn về',
            'assigned' => 'Đã phân công',
            'returning' => 'Đang hoàn về',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    }

    public static function getRelationOptions()
    {
        return [
            'self' => 'Chính sender',
            'family' => 'Người thân',
            'staff' => 'Nhân viên',
            'other' => 'Khác',
        ];
    }
}