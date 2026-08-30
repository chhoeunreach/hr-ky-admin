<?php

namespace App\Models;

use App\Helpers\AttendanceHelper;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    const AVATAR_UPLOAD_PATH = 'uploads/user/avatar/';
    const RECORDS_PER_PAGE = 20;
    const GENDER = ['male', 'female', 'others'];
    const STATUS = ['pending', 'verified', 'rejected', 'suspended'];
    const EMPLOYMENT_TYPE = ['contract', 'permanent', 'temporary'];
    const USER_TYPE = ['field', 'nonField'];
    const DEVICE_TYPE = ['android', 'ios', 'web'];
    const ANDROID = 'android';
    const IOS = 'ios';
    const WEB = 'web';
    const ONLINE = 1;
    const OFFLINE = 0;
    const FIELD = 0;
    const OFFICE = 1;
    const DEMO_USERS_USERNAME = [];
    const DEFAULT_THEME_MODE = 'light';
    const THEME_MODES = [
        'light',
        'dark',
        'ky_enterprise',
        'neo_glass',
        'premium_gradient',
        'amoled',
        'sunset',
        'ocean_blue',
        'forest_green',
        'royal_purple',
        'copper_brown',
        'rose_pink',
    ];

    const MARITAL_STATUS = [
        'single',
        'married'
    ];



    const LOGOUT_STATUS = [
        'pending' => 1,
        'approve' => 0
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'english_name',
        'email',
        'username',
        'password',
        'address',
        'dob',
        'gender',
        'marital_status',
        'phone',
        'status',
        'is_active',
        'avatar',
        'leave_allocated',
        'employment_type',
        'user_type',
        'joining_date',
        'workspace_type',
        'remarks',
        'uuid',
        'fcm_token',
        'device_type',
        'logout_status',
        'company_id',
        'online_status',
        'branch_id',
        'department_id',
        'post_id',
        'role_id',
        'supervisor_id',
        'office_time_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'employee_code',
        'allow_holiday_check_in',
        'app_theme_mode',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'app_theme_mode' => 'string',
    ];

    public static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
        });

        static::deleting(function ($model) {
            $model->deleted_by = auth()->user()->id ?? null;
            $model->save();
        });

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }

    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id')->select('name', 'id', 'slug');
    }

    public function officeTime(): BelongsTo
    {
        return $this->belongsTo(OfficeTime::class, 'office_time_id', 'id')
            ->select('id', 'opening_time', 'closing_time', 'shift', 'shift_type', 'is_late_check_in', 'checkin_after', 'is_early_check_out', 'checkout_before');
    }

    public function employeeAttendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }

    public function employeeTodayAttendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id')
            ->where('attendance_date', Carbon::now()->format('Y-m-d'))
            ->orderBy('attendances.created_at','desc');
    }

    public function employeeWeeklyAttendance(): HasMany
    {
        $currentDate = Carbon::now();
        $weekStartDate = AttendanceHelper::getStartOfWeekDate($currentDate);
        $weekEndDate = AttendanceHelper::getEndOfWeekDate($currentDate);
        return $this->hasMany(Attendance::class, 'user_id', 'id')
            ->where('attendance_status', 1)
            ->whereBetween('attendance_date', [$weekStartDate, $weekEndDate])
            ->orderBy('attendance_date', 'ASC');
    }

    public function accountDetail(): HasOne
    {
        return $this->hasOne(EmployeeAccount::class, 'user_id', 'id');
    }

    public function chatConversation(): HasOne
    {
        return $this->hasOne(ChatConversation::class, 'user_id', 'id')
            ->whereNull('admin_id');
    }

    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'user_id', 'id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function scopeNotAdmin($query)
    {
        return $query->whereHas('role', function ($query) {
            $query->where('slug', '!=', 'admin');
        });
    }
    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'employee_id', 'id');
    }

    public function attendanceLog()
    {
        return $this->hasOne(AttendanceLog::class, 'employee_id','id');
    }

    public function faceProfile(): HasOne
    {
        return $this->hasOne(FaceProfile::class, 'user_id', 'id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'id');
    }
    public function employeeSalary()
    {
        return $this->hasOne(EmployeeSalary::class, 'employee_id', 'id');
    }

    public function employee360Profile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class, 'employee_id', 'id');
    }

    public function employeeContract(): HasOne
    {
        return $this->hasOne(EmployeeContract::class, 'employee_id', 'id');
    }

    public function employmentHistory(): HasMany
    {
        return $this->hasMany(EmployeeEmploymentHistory::class, 'employee_id', 'id');
    }

    public function employeeSalaryHistory(): HasMany
    {
        return $this->hasMany(EmployeeSalaryHistory::class, 'employee_id', 'id');
    }

    public function employeePerformanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class, 'employee_id', 'id');
    }

    public function employeeInterviews(): HasMany
    {
        return $this->hasMany(EmployeeInterview::class, 'employee_id', 'id');
    }

    public function jobResponsibilities(): HasMany
    {
        return $this->hasMany(EmployeeJobResponsibility::class, 'employee_id', 'id');
    }

    public function employeeKpis(): HasMany
    {
        return $this->hasMany(EmployeeKpi::class, 'employee_id', 'id');
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class, 'employee_id', 'id');
    }

    public function employeeTrainingHistory(): HasMany
    {
        return $this->hasMany(EmployeeTrainingHistory::class, 'employee_id', 'id');
    }

    public function employeeRewards360(): HasMany
    {
        return $this->hasMany(EmployeeReward::class, 'employee_id', 'id');
    }

    public function disciplinaryRecords(): HasMany
    {
        return $this->hasMany(EmployeeDisciplinaryRecord::class, 'employee_id', 'id');
    }

    public function employeeGoals(): HasMany
    {
        return $this->hasMany(EmployeeGoal::class, 'employee_id', 'id');
    }

    public function improvementPlans(): HasMany
    {
        return $this->hasMany(EmployeeImprovementPlan::class, 'employee_id', 'id');
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id', 'id');
    }

    public function employeeProfileAuditLogs(): HasMany
    {
        return $this->hasMany(EmployeeProfileAuditLog::class, 'employee_id', 'id');
    }

    public function hasAdminIdentity(): bool
    {
        $roleName = Str::lower((string) $this->role?->name);
        $roleSlug = Str::lower((string) $this->role?->slug);
        $postName = Str::lower((string) $this->post?->post_name);
        $departmentName = Str::lower((string) $this->department?->dept_name);
        $userType = Str::lower((string) $this->user_type);

        return in_array($roleSlug, ['admin', 'administrator', 'super-admin', 'super_admin'], true)
            || in_array($roleName, ['admin', 'administrator', 'super admin'], true)
            || $userType === 'admin'
            || $postName === 'admin'
            || $postName === 'administrator'
            || $departmentName === 'admin'
            || $departmentName === 'administration';
    }

    public function mobileDirectoryRole(): string
    {
        if ($this->hasAdminIdentity()) {
            return 'admin';
        }

        $role = Str::lower(trim((string) ($this->role?->slug ?: $this->role?->name)));

        return $role !== '' && !Str::contains($role, 'admin') ? $role : 'employee';
    }

    public function mobileDirectoryUserType(): string
    {
        if ($this->hasAdminIdentity()) {
            return 'admin';
        }

        $userType = Str::lower(trim((string) $this->user_type));

        return $userType !== '' && !Str::contains($userType, 'admin') ? $userType : 'employee';
    }



}
