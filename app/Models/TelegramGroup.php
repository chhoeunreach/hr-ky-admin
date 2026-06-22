<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramGroup extends Model
{
    use HasFactory;

    public const RECORDS_PER_PAGE = 10;

    public const ACTION_GENERAL = 'general';
    public const ACTION_ATTENDANCE = 'attendance';
    public const ACTION_LEAVE = 'leave';
    public const ACTION_ADVANCE_SALARY = 'advance_salary';
    public const ACTION_ADVANCE_SALARY_APPROVED = 'advance_salary_approved';
    public const ACTION_ADVANCE_SALARY_REQUEST = 'advance_salary_request';
    public const ACTION_NEW_EMPLOYEE = 'new_employee';
    public const EVENT_ATTENDANCE_CHECKIN = 'attendance_checkin';
    public const EVENT_ATTENDANCE_CHECKOUT = 'attendance_checkout';
    public const EVENT_LEAVE_REQUEST = 'leave_request';
    public const EVENT_LEAVE_APPROVED = 'leave_approved';
    public const EVENT_LEAVE_REJECTED = 'leave_rejected';
    public const EVENT_TIME_LEAVE_REQUEST = 'time_leave_request';
    public const EVENT_ADVANCE_SALARY_REQUEST = 'advance_salary_request';
    public const EVENT_ADVANCE_SALARY_APPROVED = 'advance_salary_approved';
    public const EVENT_SELL_OUT_REPORT = 'sell_out_report';
    public const EVENT_SELL_OUT_SALE = 'sell_out_sale';
    public const EVENT_SELL_OUT_UT = 'sell_out_ut';
    public const EVENT_SELL_OUT_MATERIAL = 'sell_out_material';
    public const EVENT_SELL_OUT_REPAIR = 'sell_out_repair';
    public const EVENT_SELL_OUT_PURCHASE = 'sell_out_purchase';
    public const EVENT_SELL_OUT_ICLOUD_CUS = 'sell_out_icloud_cus';
    public const EVENT_NEW_EMPLOYEE = 'new_employee';

    protected $fillable = [
        'name',
        'chat_id',
        'action_key',
        'event_keys',
        'branch_id',
        'department_id',
        'branch_name',
        'department_name',
        'send_for_all',
        'is_active',
        'description',
    ];

    protected $casts = [
        'send_for_all' => 'boolean',
        'is_active' => 'boolean',
        'event_keys' => 'array',
    ];

    public static function actionOptions(): array
    {
        return [
            self::ACTION_GENERAL => 'General',
            self::ACTION_ATTENDANCE => 'Attendance',
            self::ACTION_LEAVE => 'Leave',
            self::ACTION_ADVANCE_SALARY => 'Advance Salary',
            self::ACTION_ADVANCE_SALARY_APPROVED => 'Advance Salary Approved',
            self::ACTION_ADVANCE_SALARY_REQUEST => 'Advance Salary Request',
            self::ACTION_NEW_EMPLOYEE => 'New Employee',
            self::EVENT_SELL_OUT_REPORT => 'Sell Out Report',
        ];
    }

    public static function eventOptions(): array
    {
        return [
            self::EVENT_ATTENDANCE_CHECKIN => 'Check In',
            self::EVENT_ATTENDANCE_CHECKOUT => 'Check Out',
            self::EVENT_LEAVE_REQUEST => 'Leave Request',
            self::EVENT_LEAVE_APPROVED => 'Leave Approved',
            self::EVENT_LEAVE_REJECTED => 'Leave Rejected',
            self::EVENT_TIME_LEAVE_REQUEST => 'Time Leave Request',
            self::EVENT_ADVANCE_SALARY_REQUEST => 'Advance Salary Request',
            self::EVENT_ADVANCE_SALARY_APPROVED => 'Advance Salary Approved',
            self::EVENT_SELL_OUT_REPORT => 'Sell Out Report',
            self::EVENT_SELL_OUT_SALE => 'Sell Out - លក់',
            self::EVENT_SELL_OUT_UT => 'Sell Out - អ៊ុត',
            self::EVENT_SELL_OUT_MATERIAL => 'Sell Out - សម្ភារ',
            self::EVENT_SELL_OUT_REPAIR => 'Sell Out - ជួសជុល',
            self::EVENT_SELL_OUT_PURCHASE => 'Sell Out - ទិញចូល',
            self::EVENT_SELL_OUT_ICLOUD_CUS => 'Sell Out - iCloud Cus',
            self::EVENT_NEW_EMPLOYEE => 'After Create New Employee',
        ];
    }

    public static function sellOutEventOptions(): array
    {
        return [
            self::EVENT_SELL_OUT_SALE => 'លក់',
            self::EVENT_SELL_OUT_UT => 'អ៊ុត',
            self::EVENT_SELL_OUT_MATERIAL => 'សម្ភារ',
            self::EVENT_SELL_OUT_REPAIR => 'ជួសជុល',
            self::EVENT_SELL_OUT_PURCHASE => 'ទិញចូល',
            self::EVENT_SELL_OUT_ICLOUD_CUS => 'iCloud Cus',
        ];
    }

    public static function sellOutEventKeyForServiceType(?string $serviceType): string
    {
        $normalized = mb_strtolower(trim((string) $serviceType));
        $normalized = str_replace([' ', '-', '_'], '', $normalized);

        return match ($normalized) {
            'អ៊ុត', 'ut' => self::EVENT_SELL_OUT_UT,
            'សម្ភារ', 'material', 'materials', 'accessory', 'accessories' => self::EVENT_SELL_OUT_MATERIAL,
            'ជួសជុល', 'repair', 'fix', 'service' => self::EVENT_SELL_OUT_REPAIR,
            'ទិញចូល', 'purchase', 'buyin', 'stockin' => self::EVENT_SELL_OUT_PURCHASE,
            'icloudcus', 'icloudcustomer' => self::EVENT_SELL_OUT_ICLOUD_CUS,
            default => self::EVENT_SELL_OUT_SALE,
        };
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
