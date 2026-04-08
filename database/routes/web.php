<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Web\AppSettingController;
use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AssetTypeController;
use App\Http\Controllers\Web\AttachmentController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AwardController;
use App\Http\Controllers\Web\AwardTypeController;
use App\Http\Controllers\Web\BonusController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\ComplaintController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DataExportController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\EmployeeLogOutRequestController;
use App\Http\Controllers\Web\EmployeeSalaryController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\FeatureController;
use App\Http\Controllers\Web\FiscalYearController;
use App\Http\Controllers\Web\GeneralSettingController;
use App\Http\Controllers\Web\HolidayController;
use App\Http\Controllers\Web\LeaveApprovalController;
use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\LeaveTypeController;
use App\Http\Controllers\Web\NFCController;
use App\Http\Controllers\Web\NoticeController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\OfficeTimeController;
use App\Http\Controllers\Web\OverTimeSettingController;
use App\Http\Controllers\Web\PaymentCurrencyController;
use App\Http\Controllers\Web\PaymentMethodController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\PrivacyPolicyController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\PromotionController;
use App\Http\Controllers\Web\QrCodeController;
use App\Http\Controllers\Web\ResignationController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\RouterController;
use App\Http\Controllers\Web\AdvanceSalaryController;
use App\Http\Controllers\Web\SalaryComponentController;
use App\Http\Controllers\Web\SalaryGroupController;
use App\Http\Controllers\Web\SalaryHistoryController;
use App\Http\Controllers\Web\SalaryTDSController;
use App\Http\Controllers\Web\SSFController;
use App\Http\Controllers\Web\StaticPageContentController;
use App\Http\Controllers\Web\SupportController;
use App\Http\Controllers\Web\TadaAttachmentController;
use App\Http\Controllers\Web\TadaController;
use App\Http\Controllers\Web\TaskChecklistController;
use App\Http\Controllers\Web\TaskCommentController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\TaxReportController;
use App\Http\Controllers\Web\TeamMeetingController;
use App\Http\Controllers\Web\TerminationController;
use App\Http\Controllers\Web\TerminationTypeController;
use App\Http\Controllers\Web\ThemeController;
use App\Http\Controllers\Web\TimeLeaveController;
use App\Http\Controllers\Web\TrainerController;
use App\Http\Controllers\Web\TrainingController;
use App\Http\Controllers\Web\TrainingTypeController;
use App\Http\Controllers\Web\TransferController;
use App\Http\Controllers\Web\UnderTimeSettingController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WarningController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes([
    'register' => false,
    'login' => false,
    'logout' => false
]);

Route::get('/', function () {
    return redirect()->route('admin.login');
});

/** app privacy policy route */
Route::get('privacy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['web']
], function () {
    Route::get('login', [AdminAuthController::class, 'showAdminLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.process');

    Route::group(['middleware' => ['admin.auth','permission']], function () {

        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/showQR', [DashboardController::class, 'showQR'])->name('showQR');

        /** User route */
        Route::resource('users', UserController::class);
        Route::get('users/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('users/delete/{id}', [UserController::class, 'delete'])->name('users.delete');
        Route::get('users/change-workspace/{id}', [UserController::class, 'changeWorkSpace'])->name('users.change-workspace');
        Route::get('users/get-company-employee/{branchId}', [UserController::class, 'getAllCompanyEmployeeDetail'])->name('users.getAllCompanyUsers');
        Route::post('users/change-password/{userId}', [UserController::class, 'changePassword'])->name('users.change-password');
        Route::get('users/force-logout/{userId}', [UserController::class, 'forceLogOutEmployee'])->name('users.force-logout');
        Route::get('users/get-all-employees/{departmentId}', [UserController::class, 'getAllEmployeeByDepartmentId'])->name('users.getAllUsersByDepartmentId');
        Route::post('users/fetch-employees-by-department', [UserController::class, 'fetchEmployeesByDepartment'])->name('users.fetchByDepartment');

        /** company route */
        Route::resource('company', CompanyController::class);

        /** branch route */
        Route::resource('branch', BranchController::class);
        Route::get('branch/toggle-status/{id}', [BranchController::class, 'toggleStatus'])->name('branch.toggle-status');
        Route::get('branch/delete/{id}', [BranchController::class, 'delete'])->name('branch.delete');


        /** Department route */
        Route::resource('departments', DepartmentController::class);
        Route::get('departments/toggle-status/{id}', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');
        Route::get('departments/delete/{id}', [DepartmentController::class, 'delete'])->name('departments.delete');
        Route::get('departments/get-All-Departments/{branchId}', [DepartmentController::class, 'getAllDepartmentsByBranchId'])->name('departments.getAllDepartmentsByBranchId');


        /** post route */
        Route::resource('posts', PostController::class);
        Route::get('posts/toggle-status/{id}', [PostController::class, 'toggleStatus'])->name('posts.toggle-status');
        Route::get('posts/delete/{id}', [PostController::class, 'delete'])->name('posts.delete');
        Route::get('posts/get-All-posts/{deptId}', [PostController::class, 'getAllPostsByBranchId'])->name('posts.getAllPostsByBranchId');

        /** roles & permissions route */
        Route::resource('roles', RoleController::class);
        Route::get('roles/toggle-status/{id}', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
        Route::get('roles/delete/{id}', [RoleController::class, 'delete'])->name('roles.delete');
        Route::get('roles/permissions/{roleId}', [RoleController::class, 'createPermission'])->name('roles.permission');
        Route::put('roles/assign-permissions/{roleId}', [RoleController::class, 'assignPermissionToRole'])->name('role.assign-permissions');

        /** office_time route */
        Route::resource('office-times', OfficeTimeController::class);
        Route::get('office-times/toggle-status/{id}', [OfficeTimeController::class, 'toggleStatus'])->name('office-times.toggle-status');
        Route::get('office-times/delete/{id}', [OfficeTimeController::class, 'delete'])->name('office-times.delete');

        /** branch_router route */
        Route::resource('routers', RouterController::class);
        Route::get('routers/toggle-status/{id}', [RouterController::class, 'toggleStatus'])->name('routers.toggle-status');
        Route::get('routers/delete/{id}', [RouterController::class, 'delete'])->name('routers.delete');

        /** holiday route */
        Route::get('holidays/import-csv', [HolidayController::class, 'holidayImport'])->name('holidays.import-csv.show');
        Route::post('holidays/import-csv', [HolidayController::class, 'importHolidays'])->name('holidays.import-csv.store');
        Route::resource('holidays', HolidayController::class);
        Route::get('holidays/toggle-status/{id}', [HolidayController::class, 'toggleStatus'])->name('holidays.toggle-status');
        Route::get('holidays/delete/{id}', [HolidayController::class, 'delete'])->name('holidays.delete');

        /** app settings */
        Route::get('app-settings/index', [AppSettingController::class, 'index'])->name('app-settings.index');
        Route::get('app-settings/toggle-status/{id}', [AppSettingController::class, 'toggleStatus'])->name('app-settings.toggle-status');

        /** General settings */
        Route::resource('general-settings', GeneralSettingController::class);
        Route::get('general-settings/delete/{id}', [GeneralSettingController::class, 'delete'])->name('general-settings.delete');

        /** Leave route */
        Route::resource('leaves', LeaveTypeController::class);
        Route::get('leaves/toggle-status/{id}', [LeaveTypeController::class, 'toggleStatus'])->name('leaves.toggle-status');
        Route::get('leaves/toggle-early-exit/{id}', [LeaveTypeController::class, 'toggleEarlyExit'])->name('leaves.toggle-early-exit');
        Route::get('leaves/delete/{id}', [LeaveTypeController::class, 'delete'])->name('leaves.delete');
        Route::get('leaves/get-leave-types/{earlyExitStatus}', [LeaveTypeController::class, 'getLeaveTypesBasedOnEarlyExitStatus']);

        /** Company Content Management route */
        Route::resource('static-page-contents', StaticPageContentController::class);
        Route::get('static-page-contents/toggle-status/{id}', [StaticPageContentController::class, 'toggleStatus'])->name('static-page-contents.toggle-status');
        Route::get('static-page-contents/delete/{id}', [StaticPageContentController::class, 'delete'])->name('static-page-contents.delete');

        /** Notification route */
        Route::get('notifications/get-nav-notification', [NotificationController::class, 'getNotificationForNavBar'])->name('nav-notifications');
        Route::resource('notifications', NotificationController::class);
        Route::get('notifications/toggle-status/{id}', [NotificationController::class, 'toggleStatus'])->name('notifications.toggle-status');
        Route::get('notifications/delete/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
        Route::get('notifications/send-notification/{id}', [NotificationController::class, 'sendNotificationToAllCompanyUser'])->name('notifications.send-notification');

        /** Attendance route */
        Route::put('employees/night-attendance/{id}', [AttendanceController::class, 'updateNightAttendance'])->name('night_attendances.update');

        Route::resource('attendances', AttendanceController::class);
        Route::get('employees/attendance/check-in/{companyId}/{userId}', [AttendanceController::class, 'checkInEmployee'])->name('employees.check-in');
        Route::get('employees/attendance/check-out/{companyId}/{userId}', [AttendanceController::class, 'checkOutEmployee'])->name('employees.check-out');
        Route::get('employees/attendance/delete/{id}', [AttendanceController::class, 'delete'])->name('attendance.delete');
        Route::get('employees/attendance/change-status/{id}', [AttendanceController::class, 'changeAttendanceStatus'])->name('attendances.change-status');
        Route::get('employees/attendance/{type}', [AttendanceController::class, 'dashboardAttendance'])->name('dashboard.takeAttendance');

        /** Leave route */
        Route::get('employees/leave-request', [LeaveController::class, 'index'])->name('leave-request.index');
        Route::post('leave-request/store', [LeaveController::class, 'storeLeaveRequest'])->name('employee-leave-request.store');
        Route::get('employees/leave-request/show/{leaveId}', [LeaveController::class, 'show'])->name('leave-request.show');
        Route::put('employees/leave-request/status-update/{leaveRequestId}', [LeaveController::class, 'updateLeaveRequestStatus'])->name('leave-request.update-status');
        Route::get('leave-request/create', [LeaveController::class, 'createLeaveRequest'])->name('leave-request.create');
        Route::get('employees/leave-request/add', [LeaveController::class, 'addLeaveRequest'])->name('leave-request.add');
        Route::post('employees/leave-request/add', [LeaveController::class, 'saveLeaveRequest'])->name('leave-request.save');

        /** Time Leave Route */
        Route::get('employees/time-leave-request', [TimeLeaveController::class, 'index'])->name('time-leave-request.index');
        Route::put('employees/time-leave-request/status-update/{leaveRequestId}', [TimeLeaveController::class, 'updateLeaveRequestStatus'])->name('time-leave-request.update-status');
        Route::get('employees/time-leave-request/show/{leaveId}', [TimeLeaveController::class, 'show'])->name('time-leave-request.show');
        Route::get('employees/time-leave-request/create', [TimeLeaveController::class, 'createLeaveRequest'])->name('time-leave-request.create');
        Route::post('employees/time-leave-request/store', [TimeLeaveController::class, 'storeLeaveRequest'])->name('time-leave-request.store');


        /**logout request Routes */
        Route::get('employee/logout-requests', [EmployeeLogOutRequestController::class, 'getAllCompanyEmployeeLogOutRequest'])->name('logout-requests.index');
        Route::get('employee/logout-requests/toggle-status/{employeeId}', [EmployeeLogOutRequestController::class, 'acceptLogoutRequest'])->name('logout-requests.accept');

        /** Notice route */
        Route::resource('notices', NoticeController::class);
        Route::get('notices/toggle-status/{id}', [NoticeController::class, 'toggleStatus'])->name('notices.toggle-status');
        Route::get('notices/delete/{id}', [NoticeController::class, 'delete'])->name('notices.delete');
        Route::get('notices/send-notice/{id}', [NoticeController::class, 'sendNotice'])->name('notices.send-notice');

        /** Team Meeting route */
        Route::resource('team-meetings', TeamMeetingController::class);
        Route::get('team-meetings/delete/{id}', [TeamMeetingController::class, 'delete'])->name('team-meetings.delete');
        Route::get('team-meetings/remove-image/{id}', [TeamMeetingController::class, 'removeImage'])->name('team-meetings.remove-image');

        /** Clients route */
        Route::post('clients/ajax/store', [ClientController::class, 'ajaxClientStore'])->name('clients.ajax-store');
        Route::resource('clients', ClientController::class);
        Route::get('clients/delete/{id}', [ClientController::class, 'delete'])->name('clients.delete');
        Route::get('clients/toggle-status/{id}', [ClientController::class, 'toggleIsActiveStatus'])->name('clients.toggle-status');

        /** Project Management route */
        Route::resource('projects', ProjectController::class);
        Route::get('projects/delete/{id}', [ProjectController::class, 'delete'])->name('projects.delete');
        Route::get('projects/toggle-status/{id}', [ProjectController::class, 'toggleStatus'])->name('projects.toggle-status');
        Route::get('projects/get-assigned-members/{projectId}', [ProjectController::class, 'getProjectAssignedMembersByProjectId'])->name('projects.get-assigned-members');
        Route::get('projects/get-employees-to-add/{addEmployeeType}/{projectId}', [ProjectController::class, 'getEmployeesToAddTpProject'])->name('projects.add-employee');
        Route::post('projects/update-leaders', [ProjectController::class, 'updateLeaderToProject'])->name('projects.update-leader-data');
        Route::post('projects/update-members', [ProjectController::class, 'updateMemberToProject'])->name('projects.update-member-data');

        /** Project & Task Attachment route */
        Route::get('projects/attachment/create/{projectId}', [AttachmentController::class, 'createProjectAttachment'])->name('project-attachment.create');
        Route::post('projects/attachment/store', [AttachmentController::class, 'storeProjectAttachment'])->name('project-attachment.store');
        Route::get('tasks/attachment/create/{taskId}', [AttachmentController::class, 'createTaskAttachment'])->name('task-attachment.create');
        Route::post('tasks/attachment/store', [AttachmentController::class, 'storeTaskAttachment'])->name('task-attachment.store');
        Route::get('attachment/delete/{id}', [AttachmentController::class, 'deleteAttachmentById'])->name('attachment.delete');


        /** Task Management route */
        Route::resource('tasks', TaskController::class);
        Route::get('projects/task/create/{projectId}', [TaskController::class, 'createTaskFromProjectPage'])->name('project-task.create');
        Route::get('tasks/delete/{id}', [TaskController::class, 'delete'])->name('tasks.delete');
        Route::get('tasks/toggle-status/{id}', [TaskController::class, 'toggleStatus'])->name('tasks.toggle-status');
        Route::get('tasks/get-all-tasks/{projectId}', [TaskController::class, 'getAllTaskByProjectId'])->name('users.getAllTaskByProjectId');

        /** Task Checklist route */
        Route::post('task-checklists/save', [TaskChecklistController::class, 'store'])->name('task-checklists.store');
        Route::get('task-checklists/edit/{id}', [TaskChecklistController::class, 'edit'])->name('task-checklists.edit');
        Route::put('task-checklists/update/{id}', [TaskChecklistController::class, 'update'])->name('task-checklists.update');
        Route::get('task-checklists/delete/{id}', [TaskChecklistController::class, 'delete'])->name('task-checklists.delete');
        Route::get('task-checklists/toggle-status/{id}', [TaskChecklistController::class, 'toggleIsCompletedStatus'])->name('task-checklists.toggle-status');

        /** Task Comments  route */
        Route::post('task-comment/store', [TaskCommentController::class, 'saveCommentDetail'])->name('task-comment.store');
        Route::get('task-comment/delete/{commentId}', [TaskCommentController::class, 'deleteComment'])->name('comment.delete');
        Route::get('task-comment/reply/delete/{replyId}', [TaskCommentController::class, 'deleteReply'])->name('reply.delete');


        /** Support route */
        Route::get('supports/get-all-query',[SupportController::class,'getAllQueriesPaginated'])->name('supports.index');
        Route::get('supports/change-seen-status/{queryId}', [SupportController::class, 'changeIsSeenStatus'])->name('supports.changeSeenStatus');
        Route::put('supports/update-status/{id}', [SupportController::class, 'changeQueryStatus'])->name('supports.updateStatus');
        Route::get('supports/delete/{id}', [SupportController::class, 'delete'])->name('supports.delete');

        /** Tada route */
        Route::put('tadas/update-status/{id}', [TadaController::class, 'changeTadaStatus'])->name('tadas.update-status');
        Route::resource('tadas', TadaController::class);
        Route::get('tadas/delete/{id}', [TadaController::class, 'delete'])->name('tadas.delete');
        Route::get('tadas/toggle-active-status/{id}', [TadaController::class, 'toggleTadaIsActive'])->name('tadas.toggle-status');

        /** Tada Attachment route */
        Route::get('tadas/attachment/create/{tadaId}', [TadaAttachmentController::class, 'create'])->name('tadas.attachment.create');
        Route::post('tadas/attachment/store', [TadaAttachmentController::class, 'store'])->name('tadas.attachment.store');
        Route::get('tadas/attachment/delete/{id}', [TadaAttachmentController::class, 'delete'])->name('tadas.attachment-delete');

        /** Export data route */
        Route::get('leave-types-export', [DataExportController::class, 'exportLeaveType'])->name('leave-type-export');
        Route::get('leave-requests-export', [DataExportController::class, 'exportEmployeeLeaveRequestLists'])->name('leave-request-export');
        Route::get('employee-detail-export', [DataExportController::class, 'exportEmployeeDetail'])->name('employee-lists-export');
        Route::get('attendance-detail-export', [DataExportController::class, 'exportAttendanceDetail'])->name('attendance-lists-export');

        /** Asset Management route */
        Route::resource('asset-types', AssetTypeController::class,[
            'except' => ['destroy']
        ]);
        Route::get('asset-types/delete/{id}', [AssetTypeController::class, 'delete'])->name('asset-types.delete');
        Route::get('asset-types/toggle-status/{id}', [AssetTypeController::class, 'toggleIsActiveStatus'])->name('asset-types.toggle-status');

        Route::resource('assets', AssetController::class,[
            'except' => ['destroy']
        ]);
        Route::get('assets/delete/{id}', [AssetController::class, 'delete'])->name('assets.delete');
        Route::get('assets/toggle-status/{id}', [AssetController::class, 'changeAvailabilityStatus'])->name('assets.change-Availability-status');

        /** Salary Component route */
        Route::resource('salary-components', SalaryComponentController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('salary-components/delete/{id}', [SalaryComponentController::class, 'delete'])->name('salary-components.delete');
        Route::get('salary-components/change-status/{id}', [SalaryComponentController::class, 'toggleSalaryComponentStatus'])->name('salary-components.toggle-status');

        /** Payment Methods route */
        Route::resource('payment-methods', PaymentMethodController::class,[
            'except' => ['destroy','show','edit']
        ]);
        Route::get('payment-methods/delete/{id}', [PaymentMethodController::class, 'deletePaymentMethod'])->name('payment-methods.delete');
        Route::get('payment-methods/change-status/{id}', [PaymentMethodController::class, 'togglePaymentMethodStatus'])->name('payment-methods.toggle-status');

        /** Payment Currency route */
        Route::get('payment-currency', [PaymentCurrencyController::class, 'index'])->name('payment-currency.index');
        Route::post('payment-currency', [PaymentCurrencyController::class, 'updateOrSetPaymentCurrency'])->name('payment-currency.save');

        /** Salary TDS route */
        Route::resource('salary-tds', SalaryTDSController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('salary-tds/delete/{id}', [SalaryTDSController::class, 'deleteSalaryTDS'])->name('salary-tds.delete');
        Route::get('salary-tds/change-status/{id}', [SalaryTDSController::class, 'toggleSalaryTDSStatus'])->name('salary-tds.toggle-status');

        /** Salary Group route */
        Route::resource('salary-groups', SalaryGroupController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('salary-groups/delete/{id}', [SalaryGroupController::class, 'deleteSalaryGroup'])->name('salary-groups.delete');
        Route::get('salary-groups/change-status/{id}', [SalaryGroupController::class, 'toggleSalaryGroupStatus'])->name('salary-groups.toggle-status');

        /** Employee Salary route */
        Route::resource('employee-salaries', EmployeeSalaryController::class,[
            'except' =>['destroy','create','edit','update','store','show']
        ]);
        Route::get('employee-salaries/update-cycle/{employeeId}/{cycle}', [EmployeeSalaryController::class, 'changeSalaryCycle'])->name('employee-salaries.update-salary-cycle');
        Route::post('employee-salaries/payroll-create', [EmployeeSalaryController::class, 'payrollCreate'])->name('employee-salaries.payroll-create');
        Route::get('employee-salaries/payroll', [EmployeeSalaryController::class, 'payroll'])->name('employee-salary.payroll');
        Route::get('employee-salaries/payroll/{payslipId}', [EmployeeSalaryController::class, 'viewPayroll'])->name('employee-salary.payroll-detail');
        Route::get('employee-salaries/payroll/{payslipId}/print', [EmployeeSalaryController::class, 'printPayslip'])->name('employee-salary.payroll-print');
        Route::get('employee-salaries/payroll/{payslipId}/edit', [EmployeeSalaryController::class, 'editPayroll'])->name('employee-salary.payroll-edit');
        Route::put('employee-salaries/payroll/{payslipId}/update', [EmployeeSalaryController::class, 'updatePayroll'])->name('employee-salary.payroll-update');
        Route::delete('employee-salaries/payroll/{payslipId}/delete', [EmployeeSalaryController::class, 'deletePayroll'])->name('employee-salary.payroll-delete');

        Route::get('employee-salaries/salary/create/{employeeId}', [EmployeeSalaryController::class, 'createSalary'])->name('employee-salaries.add');
        Route::post('employee-salaries/salary/{employeeId}', [EmployeeSalaryController::class, 'saveSalary'])->name('employee-salaries.store-salary');
        Route::get('employee-salaries/salary/edit/{employeeId}', [EmployeeSalaryController::class, 'editSalary'])->name('employee-salaries.edit-salary');
        Route::put('employee-salaries/salary/{employeeId}', [EmployeeSalaryController::class, 'updateSalary'])->name('employee-salaries.update-salary');
        Route::get('employee-salaries/salary/{employeeId}', [EmployeeSalaryController::class, 'deleteSalary'])->name('employee-salaries.delete-salary');

        Route::put('employee-salaries/{payslipId}/make-payment', [EmployeeSalaryController::class, 'makePayment'])->name('employee-salaries.make_payment');

        /** get weeks list */
        Route::get('employee-salaries/getWeeks/{year}', [EmployeeSalaryController::class, 'getWeeks'])->name('employee-salaries.get-weeks');


        /** Employee Salary History route */
        Route::get('employee-salaries/salary-update/{accountId}', [SalaryHistoryController::class, 'create'])->name('employee-salaries.increase-salary');
        Route::post('employee-salaries/salary-history/store', [SalaryHistoryController::class, 'store'])->name('employee-salaries.updated-salary-store');
        Route::get('employee-salaries/salary-increment-history/{employeeId}', [SalaryHistoryController::class, 'getEmployeeAllSalaryHistory'])->name('employee-salaries.salary-revise-history.show');

        Route::get('advance-salaries/setting/', [AdvanceSalaryController::class, 'setting'])->name('advance-salaries.setting');
        Route::post('advance-salaries/setting/{id}', [AdvanceSalaryController::class, 'updateSetting'])->name('advance-salaries.setting.store');

        /** Advance Salary route */
        Route::resource('advance-salaries', AdvanceSalaryController::class,[
            'except' => ['destroy','store','edit']
        ]);
        Route::get('advance-salaries/delete/{id}', [AdvanceSalaryController::class, 'delete'])->name('advance-salaries.delete');

        /** Tax report */

        Route::get('employee-salaries/tax-report', [TaxReportController::class, 'index'])->name('payroll.tax-report.index');
        Route::get('employee-salaries/tax-report/{id}/detail', [TaxReportController::class, 'taxReport'])->name('payroll.tax-report.detail');
        Route::get('employee-salaries/tax-report/{id}/print', [TaxReportController::class, 'printTaxReport'])->name('payroll.tax-report.print');
        Route::get('employee-salaries/tax-report/{id}/edit', [TaxReportController::class, 'editTaxReport'])->name('payroll.tax-report.edit');
        Route::put('employee-salaries/tax-report/{id}', [TaxReportController::class, 'updateTaxReport'])->name('payroll.tax-report.update');


        /** Payroll OverTime Setting route */

        Route::resource('overtime', OverTimeSettingController::class,[
        'except' => ['destroy']
        ]);
        Route::get('overtime/delete/{id}', [OverTimeSettingController::class, 'delete'])->name('overtime.delete');
        Route::get('overtime/change-status/{id}', [OverTimeSettingController::class, 'toggleOTStatus'])->name('overtime.toggle-status');


        /** Payroll UnderTime Setting route */
        Route::resource('under-time', UnderTimeSettingController::class,[
            'except' => ['destroy']
        ]);
        Route::get('under-time/delete/{id}', [UnderTimeSettingController::class, 'delete'])->name('under-time.delete');
        Route::get('under-time/change-status/{id}', [UnderTimeSettingController::class, 'toggleUTStatus'])->name('under-time.toggle-status');


        Route::resource('qr', QrCodeController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('qr/delete/{id}', [QrCodeController::class, 'delete'])->name('qr.destroy');
        Route::get('qr/print/{id}', [QrCodeController::class, 'print'])->name('qr.print');

        Route::get('/nfc', [NFCController::class, 'index'])->name('nfc.index');
        Route::get('/nfc/delete/{id}', [NFCController::class, 'delete'])->name('nfc.destroy');

        /** app settings */
        Route::get('feature/index', [FeatureController::class, 'index'])->name('feature.index');
        Route::get('feature/toggle-status/{id}', [FeatureController::class, 'toggleStatus'])->name('feature.toggle-status');

        /** delete employee leave type */
        Route::get('employees/leave_type/delete/{id}', [UserController::class, 'deleteEmployeeLeaveType'])->name('employee_leave_type.delete');


        /** Award Management route */
        Route::resource('award-types', AwardTypeController::class,[
            'except' => ['destroy']
        ]);
        Route::get('award-types/delete/{id}', [AwardTypeController::class, 'delete'])->name('award-types.delete');
        Route::get('award-types/toggle-status/{id}', [AwardTypeController::class, 'toggleStatus'])->name('award-types.toggle-status');

        Route::resource('awards', AwardController::class,[
            'except' => ['destroy']
        ]);
        Route::get('awards/delete/{id}', [AwardController::class, 'delete'])->name('awards.delete');

        /** language route */
        Route::get('language/change', [LanguageController::class, 'change'])->name('language.change');

        /** Bonus route */
        Route::resource('bonus', BonusController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('bonus/delete/{id}', [BonusController::class, 'delete'])->name('bonus.delete');
        Route::get('bonus/change-status/{id}', [BonusController::class, 'toggleBonusStatus'])->name('bonus.toggle-status');

        /** Bonus route */
        Route::resource('fiscal_year', FiscalYearController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('fiscal_year/delete/{id}', [FiscalYearController::class, 'delete'])->name('fiscal_year.delete');

        /** Bonus route */
        Route::resource('ssf', SSFController::class,[
            'except' => ['destroy','show']
        ]);

        /** Attendance Logs */
        Route::get('attendance/logs', [AttendanceController::class, 'logs'])->name('attendance.log');

        /** calculate tax */
        Route::get('calculate_tax',[EmployeeSalaryController::class, 'calculateTax'])->name('get-tax');

        /** theme change */
        Route::get('change-theme', [ThemeController::class, 'changeTheme'])->name('change-theme');

        /** leave approval route */
        Route::get('leave-approval/get-users-by-role', [LeaveApprovalController::class, 'getEmployeesByRole'])->name('leave-approval.fetchByRole');
        Route::resource('leave-approval', LeaveApprovalController::class,[
            'except' => ['destroy']
        ]);
        Route::get('leave-approval/delete/{id}', [LeaveApprovalController::class, 'delete'])->name('leave-approval.delete');
        Route::get('leave-approval/change-status/{id}', [LeaveApprovalController::class, 'toggleStatus'])->name('leave-approval.toggle-status');

        /** get leave request approvals */
        Route::get('leave-request/get-approvers/{id}', [LeaveController::class, 'getLeaveRequestApproval'])->name('leave-request.approval-details');

        /** Attendance Export */
        Route::get('attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

        /** Event route */
        Route::resource('event', EventController::class);
        Route::get('event/delete/{id}', [EventController::class, 'delete'])->name('event.delete');
        Route::get('event/remove-image/{id}', [EventController::class, 'removeImage'])->name('event.remove-image');


        /** Training Management */
        Route::resource('training-types', TrainingTypeController::class,
            ['except' => ['destroy','create','edit']]);
        Route::get('training-types/delete/{id}', [TrainingTypeController::class, 'delete'])->name('training-types.delete');
        Route::get('training-types/toggle-status/{id}', [TrainingTypeController::class, 'toggleStatus'])->name('training-types.toggle-status');

        /** Trainer */
        Route::resource('trainers', TrainerController::class,[
            'except' => ['destroy']
        ]);
        Route::get('trainers/delete/{id}', [TrainerController::class, 'delete'])->name('trainers.delete');
        Route::get('trainers/toggle-status/{id}', [TrainerController::class, 'toggleStatus'])->name('trainers.toggle-status');
        Route::get('trainers/get-all-trainers/{type}', [TrainerController::class, 'getAllTrainersByType'])->name('trainers.getAllTrainersByType');

        /** Trainer */
        Route::resource('training', TrainingController::class,[
            'except' => ['destroy']
        ]);
        Route::get('training/delete/{id}', [TrainingController::class, 'delete'])->name('training.delete');
        Route::get('training/toggle-status/{id}', [TrainingController::class, 'toggleStatus'])->name('training.toggle-status');



        /** termination Management */
        /** termination Type */
        Route::resource('termination-types', TerminationTypeController::class,
            ['except' => ['destroy','create','edit']]);
        Route::get('termination-types/delete/{id}', [TerminationTypeController::class, 'delete'])->name('termination-types.delete');
        Route::get('termination-types/toggle-status/{id}', [TerminationTypeController::class, 'toggleStatus'])->name('termination-types.toggle-status');


        /** termination */
        Route::resource('termination', TerminationController::class,[
            'except' => ['destroy']
        ]);
        Route::get('termination/delete/{id}', [TerminationController::class, 'delete'])->name('termination.delete');
        Route::put('termination/status-update/{terminationId}', [TerminationController::class, 'updateTerminationStatus'])->name('termination.update-status');

        /** resignation */
        Route::resource('resignation', ResignationController::class,[
            'except' => ['destroy']
        ]);
        Route::get('resignation/delete/{id}', [ResignationController::class, 'delete'])->name('resignation.delete');
        Route::put('resignation/status-update/{resignationId}', [ResignationController::class, 'updateResignationStatus'])->name('resignation.update-status');


        /** user export */
        Route::get('/export-users', [UserController::class, 'export'])->name('users.export');

        /** Warning */
        Route::resource('warning', WarningController::class,[
            'except' => ['destroy']
        ]);
        Route::get('warning/delete/{id}', [WarningController::class, 'delete'])->name('warning.delete');

        /** Complaint */
        Route::resource('complaint', ComplaintController::class,[
            'except' => ['destroy']
        ]);
        Route::get('complaint/delete/{id}', [ComplaintController::class, 'delete'])->name('complaint.delete');

        /** add member to task */
        Route::get('task/get-employees-to-add/{taskId}', [TaskController::class, 'getEmployeesToAddToTask'])->name('tasks.add-employee');
        Route::post('task/update-members', [TaskController::class, 'updateTaskMember'])->name('tasks.update-member-data');


        /** Promotion */
        Route::resource('promotion', PromotionController::class,[
            'except' => ['destroy']
        ]);
        Route::get('promotion/delete/{id}', [PromotionController::class, 'delete'])->name('promotion.delete');
        Route::get('promotion/get-employees-posts/{departmentId}', [PromotionController::class, 'getEmployeeAndPostByDepartment'])->name('promotion.getUsersAndPostByDepartment');
//        Route::put('promotion/status-update/{promotionId}', [PromotionController::class, 'updatePromotionStatus'])->name('promotion.update-status');


        Route::resource('transfer', TransferController::class,[
            'except' => ['destroy']
        ]);
        Route::get('transfer/delete/{id}', [TransferController::class, 'delete'])->name('transfer.delete');
//        Route::put('transfer/status-update/{transferId}', [TransferController::class, 'updateTransferStatus'])->name('transfer.update-status');
        Route::get('transfer/get-user-transfer-department-data/{departmentId}', [TransferController::class, 'getUserTransferDepartmentData']);
        Route::get('transfer/get-user-transfer-branch-data/{branchId}', [TransferController::class, 'getUserTransferBranchData']);
        Route::get('transfer/get-user-data/{employeeId}', [TransferController::class, 'getUserTransferData']);

    });
});

Route::fallback(function() {
    return view('errors.404');
});





