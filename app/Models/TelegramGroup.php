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
    public const ACTION_PAYROLL_QUEUE = 'payroll_queue';
    public const ACTION_NEW_EMPLOYEE = 'new_employee';
    public const ACTION_RESIGNATION = 'resignation';
    public const ACTION_WARNING = 'warning';
    public const ACTION_COMPLAINT = 'complaint';
    public const ACTION_AWARD = 'award';
    public const ACTION_TRAINING = 'training';
    public const ACTION_SELL_OUT = 'sell_out';
    public const ACTION_BRANCH_ROUTE = 'branch_route';
    public const ACTION_SYSTEM = 'system';

    // Attendance Events
    public const EVENT_ATTENDANCE_CHECKIN = 'attendance_checkin';
    public const EVENT_ATTENDANCE_CHECKOUT = 'attendance_checkout';
    public const EVENT_ATTENDANCE_LATE = 'attendance_late';
    public const EVENT_ATTENDANCE_SUMMARY = 'attendance_summary';

    // Branch Route Events (ការងារចេញក្រៅ)
    public const EVENT_BRANCH_ROUTE_SUBMITTED = 'branch_route_submitted';
    public const EVENT_BRANCH_ROUTE_COMPLETED = 'branch_route_completed';
    public const EVENT_BRANCH_ROUTE_CANCELLED = 'branch_route_cancelled';

    // Leave & Time Leave Events
    public const EVENT_LEAVE_REQUEST = 'leave_request';
    public const EVENT_LEAVE_APPROVED = 'leave_approved';
    public const EVENT_LEAVE_REJECTED = 'leave_rejected';
    public const EVENT_LEAVE_CANCELLED = 'leave_cancelled';
    public const EVENT_TIME_LEAVE_REQUEST = 'time_leave_request';
    public const EVENT_TIME_LEAVE_APPROVED = 'time_leave_approved';
    public const EVENT_TIME_LEAVE_REJECTED = 'time_leave_rejected';

    // Advance Salary & Payroll Queue Events
    public const EVENT_ADVANCE_SALARY_REQUEST = 'advance_salary_request';
    public const EVENT_ADVANCE_SALARY_APPROVED = 'advance_salary_approved';
    public const EVENT_ADVANCE_SALARY_REJECTED = 'advance_salary_rejected';
    public const EVENT_PAYROLL_QUEUE_CALLED = 'payroll_queue_called';
    public const EVENT_PAYROLL_QUEUE_SERVED = 'payroll_queue_served';

    // Employee & Lifecycle Events
    public const EVENT_NEW_EMPLOYEE = 'new_employee';
    public const EVENT_EMPLOYEE_STATUS_CHANGED = 'employee_status_changed';
    public const EVENT_RESIGNATION_REQUEST = 'resignation_request';
    public const EVENT_RESIGNATION_APPROVED = 'resignation_approved';
    public const EVENT_RESIGNATION_REJECTED = 'resignation_rejected';

    // Discipline & Grievance Events
    public const EVENT_WARNING_CREATED = 'warning_created';
    public const EVENT_COMPLAINT_CREATED = 'complaint_created';
    public const EVENT_COMPLAINT_STATUS_UPDATED = 'complaint_status_updated';

    // Rewards & Training Events
    public const EVENT_AWARD_GRANTED = 'award_granted';
    public const EVENT_TRAINING_ASSIGNED = 'training_assigned';

    // Sell Out Events
    public const EVENT_SELL_OUT_REPORT = 'sell_out_report';
    public const EVENT_SELL_OUT_SALE = 'sell_out_sale';
    public const EVENT_SELL_OUT_SELLSIM = 'sell_out_sellsim';
    public const EVENT_SELL_OUT_UT = 'sell_out_ut';
    public const EVENT_SELL_OUT_MATERIAL = 'sell_out_material';
    public const EVENT_SELL_OUT_REPAIR = 'sell_out_repair';
    public const EVENT_SELL_OUT_PURCHASE = 'sell_out_purchase';
    public const EVENT_SELL_OUT_ICLOUD_CUS = 'sell_out_icloud_cus';

    // System Alert Event
    public const EVENT_SYSTEM_ALERT = 'system_alert';

    protected $fillable = [
        'name',
        'chat_id',
        'chat_ids',
        'topic_id',
        'action_key',
        'action_keys',
        'event_keys',
        'branch_id',
        'branch_ids',
        'department_id',
        'department_ids',
        'branch_name',
        'department_name',
        'send_for_all',
        'send_location',
        'send_selfie',
        'is_active',
        'description',
        'last_tested_at',
        'last_test_status',
    ];

    protected $casts = [
        'topic_id' => 'integer',
        'send_for_all' => 'boolean',
        'send_location' => 'boolean',
        'send_selfie' => 'boolean',
        'is_active' => 'boolean',
        'chat_ids' => 'array',
        'action_keys' => 'array',
        'event_keys' => 'array',
        'branch_ids' => 'array',
        'department_ids' => 'array',
        'last_tested_at' => 'datetime',
    ];

    public static function actionOptions(): array
    {
        return [
            self::ACTION_GENERAL => 'General (ទូទៅ)',
            self::ACTION_ATTENDANCE => 'Attendance (វត្តមាន)',
            self::ACTION_LEAVE => 'Leave Management (ការសុំច្បាប់)',
            self::ACTION_ADVANCE_SALARY => 'Advance Salary (បុរេប្រាក់ខែ)',
            self::ACTION_PAYROLL_QUEUE => 'Payroll Queue (កាតបើកប្រាក់ខែ)',
            self::ACTION_NEW_EMPLOYEE => 'New Employee (បុគ្គលិកថ្មី)',
            self::ACTION_RESIGNATION => 'Resignation (ការលាឈប់)',
            self::ACTION_WARNING => 'Warning (លិខិតព្រមាន)',
            self::ACTION_COMPLAINT => 'Complaint (ពាក្យបណ្តឹង)',
            self::ACTION_AWARD => 'Award (ការលើកទឹកចិត្ត/រង្វាន់)',
            self::ACTION_TRAINING => 'Training (ការបណ្តុះបណ្តាល)',
            self::ACTION_SELL_OUT => 'Sell Out (របាយការណ៍លក់)',
            self::ACTION_BRANCH_ROUTE => 'Branch Route (ការងារចេញក្រៅ)',
            self::ACTION_SYSTEM => 'System Alerts (ប្រព័ន្ធ/សុវត្ថិភាព)',
        ];
    }

    public static function categorizedEventOptions(): array
    {
        return [
            'attendance' => [
                'action_key' => self::ACTION_ATTENDANCE,
                'label' => 'វត្តមាន (Attendance)',
                'icon' => 'clock',
                'events' => [
                    self::EVENT_ATTENDANCE_CHECKIN => 'Check In (ចូលធ្វើការ)',
                    self::EVENT_ATTENDANCE_CHECKOUT => 'Check Out (ចេញពីការងារ)',
                    self::EVENT_ATTENDANCE_LATE => 'Late In (ចូលយឺត)',
                    self::EVENT_ATTENDANCE_SUMMARY => 'Daily Summary (របាយការណ៍សង្ខេបប្រចាំថ្ងៃ)',
                ],
            ],
            'leave' => [
                'action_key' => self::ACTION_LEAVE,
                'label' => 'ការសុំច្បាប់ (Leave Management)',
                'icon' => 'calendar',
                'events' => [
                    self::EVENT_LEAVE_REQUEST => 'Leave Request (ស្នើសុំច្បាប់)',
                    self::EVENT_LEAVE_APPROVED => 'Leave Approved (អនុម័តច្បាប់)',
                    self::EVENT_LEAVE_REJECTED => 'Leave Rejected (បដិសេធច្បាប់)',
                    self::EVENT_LEAVE_CANCELLED => 'Leave Cancelled (លុបចោលច្បាប់)',
                    self::EVENT_TIME_LEAVE_REQUEST => 'Time Leave Request (ស្នើសុំចេញក្រៅ/មកយឺត)',
                    self::EVENT_TIME_LEAVE_APPROVED => 'Time Leave Approved (អនុម័តចេញក្រៅ/មកយឺត)',
                    self::EVENT_TIME_LEAVE_REJECTED => 'Time Leave Rejected (បដិសេធចេញក្រៅ/មកយឺត)',
                ],
            ],
            'payroll' => [
                'action_key' => self::ACTION_ADVANCE_SALARY,
                'label' => 'បៀវត្ស និងហិរញ្ញវត្ថុ (Payroll & Finance)',
                'icon' => 'dollar-sign',
                'events' => [
                    self::EVENT_ADVANCE_SALARY_REQUEST => 'Advance Salary Request (ស្នើសុំបុរេប្រាក់ខែ)',
                    self::EVENT_ADVANCE_SALARY_APPROVED => 'Advance Salary Approved (អនុម័តបុរេប្រាក់ខែ)',
                    self::EVENT_ADVANCE_SALARY_REJECTED => 'Advance Salary Rejected (បដិសេធបុរេប្រាក់ខែ)',
                    self::EVENT_PAYROLL_QUEUE_CALLED => 'Call Queue Ticket (ហៅលេខកាតបើកប្រាក់ខែ)',
                    self::EVENT_PAYROLL_QUEUE_SERVED => 'Ticket Served (បើកប្រាក់ខែរួចរាល់)',
                ],
            ],
            'employee' => [
                'action_key' => self::ACTION_NEW_EMPLOYEE,
                'label' => 'បុគ្គលិក និងរដ្ឋបាល (Employee Lifecycle)',
                'icon' => 'users',
                'events' => [
                    self::EVENT_NEW_EMPLOYEE => 'New Employee (បង្កើតបុគ្គលិកថ្មី)',
                    self::EVENT_EMPLOYEE_STATUS_CHANGED => 'Employee Status Changed (ប្តូរស្ថានភាពបុគ្គលិក)',
                    self::EVENT_RESIGNATION_REQUEST => 'Resignation Request (ស្នើសុំលាឈប់)',
                    self::EVENT_RESIGNATION_APPROVED => 'Resignation Approved (អនុម័តការលាឈប់)',
                    self::EVENT_RESIGNATION_REJECTED => 'Resignation Rejected (បដិសេធការលាឈប់)',
                ],
            ],
            'discipline' => [
                'action_key' => self::ACTION_WARNING,
                'label' => 'វិន័យ និងពាក្យបណ្តឹង (Discipline & Grievance)',
                'icon' => 'alert-triangle',
                'events' => [
                    self::EVENT_WARNING_CREATED => 'Warning Issued (ចេញលិខិតព្រមាន)',
                    self::EVENT_COMPLAINT_CREATED => 'Complaint Submitted (ដាក់ពាក្យបណ្តឹងថ្មី)',
                    self::EVENT_COMPLAINT_STATUS_UPDATED => 'Complaint Updated (បច្ចុប្បន្នភាពពាក្យបណ្តឹង)',
                ],
            ],
            'sell_out' => [
                'action_key' => self::ACTION_SELL_OUT,
                'label' => 'របាយការណ៍ និងការលក់ (Operations & Sell Out)',
                'icon' => 'shopping-cart',
                'events' => [
                    self::EVENT_SELL_OUT_REPORT => 'Sell Out Report (របាយការណ៍ Sell Out)',
                    self::EVENT_SELL_OUT_SALE => 'Sell Out - លក់',
                    self::EVENT_SELL_OUT_SELLSIM => 'Sell Out - លក់ស៊ីម',
                    self::EVENT_SELL_OUT_UT => 'Sell Out - អ៊ុត',
                    self::EVENT_SELL_OUT_MATERIAL => 'Sell Out - សម្ភារ',
                    self::EVENT_SELL_OUT_REPAIR => 'Sell Out - ជួសជុល',
                    self::EVENT_SELL_OUT_PURCHASE => 'Sell Out - ទិញចូល',
                    self::EVENT_SELL_OUT_ICLOUD_CUS => 'Sell Out - iCloud Cus',
                ],
            ],
            'branch_route' => [
                'action_key' => self::ACTION_BRANCH_ROUTE,
                'label' => 'ការងារចេញក្រៅ (Branch Route / Operations)',
                'icon' => 'map-pin',
                'events' => [
                    self::EVENT_BRANCH_ROUTE_SUBMITTED => 'Branch Route Started (ចាប់ផ្តើមចេញដំណើរ)',
                    self::EVENT_BRANCH_ROUTE_COMPLETED => 'Branch Route Completed (បញ្ចប់បេសកកម្ម)',
                    self::EVENT_BRANCH_ROUTE_CANCELLED => 'Branch Route Cancelled (បោះបង់បេសកកម្ម)',
                ],
            ],
            'growth' => [
                'action_key' => self::ACTION_AWARD,
                'label' => 'ការលើកទឹកចិត្ត និងការបណ្តុះបណ្តាល (Rewards & Training)',
                'icon' => 'award',
                'events' => [
                    self::EVENT_AWARD_GRANTED => 'Award Granted (ផ្តល់រង្វាន់/ការសរសើរ)',
                    self::EVENT_TRAINING_ASSIGNED => 'Training Assigned (ចាត់តាំងការបណ្តុះបណ្តាល)',
                ],
            ],
            'system' => [
                'action_key' => self::ACTION_SYSTEM,
                'label' => 'ប្រព័ន្ធ និងសុវត្ថិភាព (System & Security)',
                'icon' => 'shield',
                'events' => [
                    self::EVENT_SYSTEM_ALERT => 'System Alert (ការជូនដំណឹងប្រព័ន្ធ)',
                ],
            ],
        ];
    }

    public static function eventOptions(): array
    {
        $flattened = [];
        foreach (self::categorizedEventOptions() as $category) {
            foreach ($category['events'] as $eventKey => $eventLabel) {
                $flattened[$eventKey] = $eventLabel;
            }
        }

        return $flattened;
    }

    public static function parentActionForEvent(string $eventKey): ?string
    {
        return match ($eventKey) {
            self::EVENT_ATTENDANCE_CHECKIN,
            self::EVENT_ATTENDANCE_CHECKOUT,
            self::EVENT_ATTENDANCE_LATE,
            self::EVENT_ATTENDANCE_SUMMARY => self::ACTION_ATTENDANCE,

            self::EVENT_LEAVE_REQUEST,
            self::EVENT_LEAVE_APPROVED,
            self::EVENT_LEAVE_REJECTED,
            self::EVENT_LEAVE_CANCELLED,
            self::EVENT_TIME_LEAVE_REQUEST,
            self::EVENT_TIME_LEAVE_APPROVED,
            self::EVENT_TIME_LEAVE_REJECTED => self::ACTION_LEAVE,

            self::EVENT_ADVANCE_SALARY_REQUEST,
            self::EVENT_ADVANCE_SALARY_APPROVED,
            self::EVENT_ADVANCE_SALARY_REJECTED => self::ACTION_ADVANCE_SALARY,

            self::EVENT_PAYROLL_QUEUE_CALLED,
            self::EVENT_PAYROLL_QUEUE_SERVED => self::ACTION_PAYROLL_QUEUE,

            self::EVENT_NEW_EMPLOYEE,
            self::EVENT_EMPLOYEE_STATUS_CHANGED => self::ACTION_NEW_EMPLOYEE,

            self::EVENT_RESIGNATION_REQUEST,
            self::EVENT_RESIGNATION_APPROVED,
            self::EVENT_RESIGNATION_REJECTED => self::ACTION_RESIGNATION,

            self::EVENT_WARNING_CREATED => self::ACTION_WARNING,

            self::EVENT_COMPLAINT_CREATED,
            self::EVENT_COMPLAINT_STATUS_UPDATED => self::ACTION_COMPLAINT,

            self::EVENT_AWARD_GRANTED => self::ACTION_AWARD,

            self::EVENT_TRAINING_ASSIGNED => self::ACTION_TRAINING,

            self::EVENT_SELL_OUT_REPORT,
            self::EVENT_SELL_OUT_SALE,
            self::EVENT_SELL_OUT_SELLSIM,
            self::EVENT_SELL_OUT_UT,
            self::EVENT_SELL_OUT_MATERIAL,
            self::EVENT_SELL_OUT_REPAIR,
            self::EVENT_SELL_OUT_PURCHASE,
            self::EVENT_SELL_OUT_ICLOUD_CUS => self::ACTION_SELL_OUT,

            self::EVENT_BRANCH_ROUTE_SUBMITTED,
            self::EVENT_BRANCH_ROUTE_COMPLETED,
            self::EVENT_BRANCH_ROUTE_CANCELLED => self::ACTION_BRANCH_ROUTE,

            self::EVENT_SYSTEM_ALERT => self::ACTION_SYSTEM,

            default => null,
        };
    }

    public static function sellOutEventOptions(): array
    {
        return [
            self::EVENT_SELL_OUT_SALE => 'លក់',
            self::EVENT_SELL_OUT_SELLSIM => 'លក់ស៊ីម',
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
            'លក់ស៊ីម', 'sellsim', 'simsale' => self::EVENT_SELL_OUT_SELLSIM,
            'អ៊ុត', 'ut' => self::EVENT_SELL_OUT_UT,
            'សម្ភារ', 'material', 'materials', 'accessory', 'accessories' => self::EVENT_SELL_OUT_MATERIAL,
            'ជួសជុល', 'repair', 'fix', 'service' => self::EVENT_SELL_OUT_REPAIR,
            'ទិញចូល', 'purchase', 'buyin', 'stockin' => self::EVENT_SELL_OUT_PURCHASE,
            'icloudcus', 'icloudcustomer' => self::EVENT_SELL_OUT_ICLOUD_CUS,
            default => self::EVENT_SELL_OUT_SALE,
        };
    }

    /**
     * Generate a Telegram deep link or join link for a given chat ID and optional topic.
     *
     * Supported formats:
     * - Supergroups / Channels starting with -100: https://t.me/c/<id_without_-100> (and with topic: /<topic_id>)
     * - Public handles (@username): https://t.me/<username>
     * - Direct web/invite links (https://t.me/...): return as is
     */
    public function getTelegramUrl(?string $chatId = null): ?string
    {
        $id = trim((string) ($chatId ?? $this->chat_id));
        if ($id === '') {
            return null;
        }

        if (str_starts_with($id, 'http://') || str_starts_with($id, 'https://')) {
            return $id;
        }

        if (str_starts_with($id, '@')) {
            $handle = ltrim($id, '@');
            if (!empty($this->topic_id)) {
                return "https://t.me/{$handle}/{$this->topic_id}";
            }
            return "https://t.me/{$handle}";
        }

        if (preg_match('/^-100(\d+)$/', $id, $matches)) {
            $stripped = $matches[1];
            if (!empty($this->topic_id)) {
                return "https://t.me/c/{$stripped}/{$this->topic_id}";
            }
            return "https://t.me/c/{$stripped}";
        }

        return null;
    }

    /**
     * Generate a Telegram deep link specifically for a topic thread.
     */
    public function getTelegramTopicUrl(?string $chatId = null, ?int $topicId = null): ?string
    {
        $tid = $topicId ?? $this->topic_id;
        if (empty($tid)) {
            return null;
        }

        $id = trim((string) ($chatId ?? $this->chat_id));
        if (preg_match('/^-100(\d+)$/', $id, $matches)) {
            return "https://t.me/c/{$matches[1]}/{$tid}";
        }

        if (str_starts_with($id, '@')) {
            return "https://t.me/" . ltrim($id, '@') . "/{$tid}";
        }

        return null;
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
