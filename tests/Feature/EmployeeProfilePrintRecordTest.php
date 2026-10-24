<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\EmployeeProfileController;
use App\Models\EmployeeProfileAuditLog;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeProfilePrintRecordTest extends TestCase
{
    private array $abilities = ['employee.profile.view'];

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('en');
        Schema::create('leave_requests_master', function (Blueprint $table) {
            $table->id();
            $table->integer('requested_by');
            $table->integer('leave_type_id')->nullable();
            foreach (['leave_from', 'leave_to', 'leave_requested_date', 'reasons', 'status', 'admin_remark'] as $field) {
                $table->string($field)->nullable();
            }
            $table->decimal('no_of_days', 8, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('time_leaves', function (Blueprint $table) {
            $table->id();
            $table->integer('requested_by');
            foreach (['issue_date', 'start_time', 'end_time', 'reasons', 'status', 'admin_remark'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        DB::table('leave_requests_master')->insert(['id' => 1, 'requested_by' => 20, 'leave_from' => '2026-10-25', 'leave_to' => '2026-10-26', 'reasons' => 'Family event', 'status' => 'pending']);
        DB::table('time_leaves')->insert(['id' => 2, 'requested_by' => 20, 'issue_date' => '2026-10-25', 'start_time' => '09:00', 'end_time' => '10:00', 'reasons' => 'Appointment', 'status' => 'approved']);
        Schema::create('resignations', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            foreach (['resignation_date', 'last_working_day', 'reason', 'status', 'admin_remark'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_24_000001_add_letter_details_to_resignations_table.php'))->up();
        DB::table('resignations')->insert(['id' => 3, 'employee_id' => 20, 'resignation_date' => '2026-10-24', 'last_working_day' => '2026-11-24', 'reason' => '<p>Career change</p>', 'status' => 'onReview']);
        Schema::create('employee_profile_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->string('module');
            $table->string('action');
            $table->integer('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->integer('performed_by')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
        });
        $this->actingAs((new User())->forceFill(['id' => 10, 'name' => 'HR Officer']));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));

        Route::get('/_test/resignation-employee-information', function () {
            $employee = (new User())->forceFill([
                'id' => 20, 'branch_id' => 2, 'name' => 'Employee', 'english_name' => 'Employee Latin',
                'employee_code' => 'EMP020', 'joining_date' => '2024-01-01', 'phone' => '012345678',
            ]);
            $employee->setRelation('branch', (new \App\Models\Branch())->forceFill(['name' => 'Head Office']));
            $employee->setRelation('department', (new \App\Models\Department())->forceFill(['dept_name' => 'HR']));
            $employee->setRelation('post', (new \App\Models\Post())->forceFill(['post_name' => 'Officer']));
            return app(\App\Http\Controllers\Web\ResignationController::class)->employeeInformation($employee);
        });

        // Exercise validation, authorization and persistence without unrelated profile tables.
        Route::post('/_test/profile-print', function (Request $request) {
            $employee = (new User())->forceFill(['id' => 20, 'name' => 'Employee', 'employee_code' => 'EMP020']);
            return app(EmployeeProfileController::class)->recordPrint($request, $employee);
        });
    }

    private function payload(string $type = 'leaveLetter'): array
    {
        return array_merge([
            'form_type' => $type,
            'document_text' => 'Leave Request Form — Employee EMP020',
            'fields' => [
                ['label' => 'Reason', 'value' => 'Family event', 'checked' => null],
                ['label' => 'Annual leave', 'value' => 'annual', 'checked' => true],
            ],
        ], match ($type) {
            'leaveLetter' => ['source_request_id' => 1],
            'timeLeaveLetter' => ['source_request_id' => 2],
            'resignationLetter' => ['source_request_id' => 3],
            default => [],
        });
    }

    public function test_each_form_saves_a_record_with_its_own_permission(): void
    {
        $forms = [
            'complete' => 'employee.complete_form.print',
            'overview' => 'employee.warning_overview.print',
            'warning' => 'employee.warning_form.print',
            'contract' => 'employee.contract_form.print',
            'salaryCertificate' => 'employee.salary.view',
            'leaveLetter' => 'employee.leave_form.print',
            'timeLeaveLetter' => 'employee.time_leave_form.print',
            'attendanceDailyReport' => 'employee.attendance_daily_report.print',
            'resignationLetter' => 'employee.resignation_form.print',
        ];
        foreach ($forms as $type => $permission) {
            $this->abilities = ['employee.profile.view', $permission];
            $this->postJson('/_test/profile-print', $this->payload($type))
                ->assertCreated()->assertJson(['recorded' => true]);
        }
        $this->assertSame(9, EmployeeProfileAuditLog::count());
        $log = EmployeeProfileAuditLog::where('new_values->form_type', 'leaveLetter')->firstOrFail();
        $this->assertSame(20, $log->employee_id);
        $this->assertSame(10, $log->performed_by);
        $this->assertSame('print_requested', $log->action);
        $this->assertSame('Leave Request Form', $log->new_values['document_title']);
        $this->assertSame('HR Officer', $log->new_values['performed_by_name']);
        $this->assertSame($this->payload()['fields'], $log->new_values['fields']);
        $this->assertNotNull($log->created_at);
        $this->assertSame('leave', $log->new_values['source_request_type']);
        $this->assertSame(1, $log->new_values['source_request_id']);
        $this->assertSame('Family event', $log->new_values['source_request_snapshot']['reasons']);
        $this->assertSame('A4', $log->new_values['paper_size']);
    }

    public function test_profile_view_alone_cannot_create_a_print_record(): void
    {
        $this->postJson('/_test/profile-print', $this->payload())->assertForbidden();
        $this->assertSame(0, EmployeeProfileAuditLog::count());
    }

    public function test_print_permission_for_another_form_is_denied(): void
    {
        $this->abilities[] = 'employee.complete_form.print';
        $this->postJson('/_test/profile-print', $this->payload())->assertForbidden();
        $this->assertSame(0, EmployeeProfileAuditLog::count());
    }

    public function test_unknown_form_and_missing_snapshot_are_rejected(): void
    {
        $this->postJson('/_test/profile-print', $this->payload('unknown'))->assertUnprocessable();
        $this->postJson('/_test/profile-print', ['form_type' => 'leaveLetter'])->assertUnprocessable();
        $this->assertSame(0, EmployeeProfileAuditLog::count());
    }

    public function test_repeated_prints_produce_separate_history_records(): void
    {
        $this->abilities[] = 'employee.leave_form.print';
        $this->postJson('/_test/profile-print', $this->payload())->assertCreated();
        $this->postJson('/_test/profile-print', $this->payload())->assertCreated();
        $this->assertSame(2, EmployeeProfileAuditLog::count());
    }

    public function test_failed_persistence_does_not_report_a_saved_record(): void
    {
        $this->abilities[] = 'employee.leave_form.print';
        Schema::drop('employee_profile_audit_logs');
        $this->postJson('/_test/profile-print', $this->payload())->assertStatus(500);
    }

    public function test_leave_print_requires_a_saved_request_for_the_same_employee_and_type(): void
    {
        $this->abilities[] = 'employee.leave_form.print';
        $this->abilities[] = 'employee.time_leave_form.print';
        $this->abilities[] = 'employee.resignation_form.print';
        foreach (['leaveLetter', 'timeLeaveLetter', 'resignationLetter'] as $type) {
            $payload = $this->payload($type);
            unset($payload['source_request_id']);
            $this->postJson('/_test/profile-print', $payload)->assertUnprocessable();
            $this->postJson('/_test/profile-print', array_replace($this->payload($type), ['source_request_id' => 999]))->assertUnprocessable();
        }
        DB::table('leave_requests_master')->where('id', 1)->update(['requested_by' => 30]);
        DB::table('time_leaves')->where('id', 2)->update(['requested_by' => 30]);
        $this->postJson('/_test/profile-print', $this->payload())->assertUnprocessable();
        $this->postJson('/_test/profile-print', $this->payload('timeLeaveLetter'))->assertUnprocessable();
        DB::table('resignations')->where('id', 3)->update(['employee_id' => 30]);
        $this->postJson('/_test/profile-print', $this->payload('resignationLetter'))->assertUnprocessable();
        $this->assertSame(0, EmployeeProfileAuditLog::count());
    }

    public function test_time_leave_print_records_source_snapshot_and_returns_record_number(): void
    {
        $this->abilities[] = 'employee.time_leave_form.print';
        $response = $this->postJson('/_test/profile-print', $this->payload('timeLeaveLetter'))->assertCreated();
        $log = EmployeeProfileAuditLog::firstOrFail();
        $response->assertJsonPath('print_record_id', $log->id);
        $this->assertSame('time_leave', $log->new_values['source_request_type']);
        $this->assertSame(2, $log->new_values['source_request_id']);
        $this->assertSame('09:00', $log->new_values['source_request_snapshot']['start_time']);
        $this->assertSame('approved', $log->new_values['source_request_snapshot']['status']);
    }

    public function test_employee_information_requires_resignation_permission_and_respects_branch(): void
    {
        $this->getJson('/_test/resignation-employee-information')->assertForbidden();
        $this->abilities[] = 'create_resignation';
        $this->getJson('/_test/resignation-employee-information')->assertOk()
            ->assertJsonPath('data.employee_code', 'EMP020')
            ->assertJsonPath('data.latin_name', 'Employee Latin')
            ->assertJsonPath('data.department', 'HR')->assertJsonPath('data.position', 'Officer');
        auth()->user()->branch_id = 3;
        $this->getJson('/_test/resignation-employee-information')->assertNotFound();
    }

    public function test_resignation_form_renders_errors_and_preserves_input_in_both_languages(): void
    {
        $this->abilities[] = 'create_resignation';
        request()->setLaravelSession(app('session.store'));
        session()->flashInput(['reason' => 'Personal reasons', 'status' => 'onReview', 'resignation_date' => '2026-10-24']);
        $errors = new \Illuminate\Support\ViewErrorBag();
        $errors->put('default', new \Illuminate\Support\MessageBag(['last_working_day' => 'Choose a later final working day.']));
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->view('admin.resignation.common.form', [
                'profileEmployee' => null, 'resignationDetail' => null, 'companyDetail' => null,
                'isBsEnabled' => false, 'resignationStatus' => \App\Enum\ResignationStatusEnum::cases(), 'errors' => $errors,
            ])->assertSee('Personal reasons')->assertSee('Choose a later final working day.')
                ->assertSee('value="onReview" selected', false)->assertSee('id="resignation_date"', false)
                ->assertSee('id="last_working_day"', false)->assertSee(__('index.resignation_save_request'))
                ->assertSee(__('index.resignation_place_of_signing'))->assertDontSee('index.');
        }
    }

    public function test_complete_resignation_information_is_saved_rendered_and_validated(): void
    {
        $this->abilities[] = 'create_resignation';
        $this->abilities[] = 'employee.resignation_form.print';
        $employee = (new User())->forceFill(['id' => 20, 'name' => 'Employee', 'employee_code' => 'EMP020']);
        foreach (['branch', 'department', 'post'] as $relation) {
            $employee->setRelation($relation, null);
        }
        $details = [
            'khmer_name' => 'Saved employee name', 'latin_name' => 'Saved Latin name',
            'gender' => 'female', 'employee_code' => 'EMP020', 'phone' => '012345678',
            'joining_date' => '2024-01-01', 'position' => 'Officer',
            'department' => 'HR', 'branch' => 'Head Office',
            'returned_property' => 'Laptop and ID card returned', 'made_at' => 'Phnom Penh',
            'letter_date' => '2026-10-24',
        ];
        $resignation = \App\Models\Resignation::firstOrFail();
        $resignation->update(['letter_details' => $details]);
        $this->assertSame($details, $resignation->fresh()->letter_details);
        $rules = array_filter((new \App\Requests\Resignation\ResignationRequest())->rules(),
            fn ($key) => str_starts_with($key, 'letter_details'), ARRAY_FILTER_USE_KEY);
        $this->assertTrue(\Illuminate\Support\Facades\Validator::make(['letter_details' => $details], $rules)->passes());
        $invalid = array_replace($details, ['joining_date' => 'invalid', 'letter_date' => 'invalid', 'made_at' => str_repeat('x', 256)]);
        $validator = \Illuminate\Support\Facades\Validator::make(['letter_details' => $invalid], $rules);
        $this->assertTrue($validator->fails());
        $this->assertCount(3, $validator->errors());
        $this->view('admin.resignation.common.scripts', ['profileEmployee' => $employee])
            ->assertSee('employee-information', false)->assertSee('data-letter-employee-field', false);
        $this->view('admin.resignation.common.form', [
            'profileEmployee' => $employee, 'resignationDetail' => null, 'companyDetail' => null,
            'errors' => new \Illuminate\Support\ViewErrorBag(),
            'isBsEnabled' => false, 'resignationStatus' => \App\Enum\ResignationStatusEnum::cases(),
        ])->assertSee('letter_details[returned_property]', false)->assertSee('letter_details[made_at]', false)
            ->assertSee('letter_details[letter_date]', false)->assertSee('value="EMP020"', false);
        $this->view('admin.employees.profile.partials.resignation-letter', [
            'employee' => $employee, 'resignationPrintRequests' => collect([$resignation]),
            'selectedResignationPrintRequest' => $resignation,
            'canPrintResignationForm' => true, 'canExportResignationForm' => true,
        ])->assertSee('Saved employee name')->assertSee('Laptop and ID card returned')->assertSee('Phnom Penh')
            ->assertSee('value="2024-01-01" readonly', false)->assertSee('value="2026-10-24" readonly', false);
        $this->postJson('/_test/profile-print', $this->payload('resignationLetter'))->assertCreated();
        $this->assertSame($details, EmployeeProfileAuditLog::firstOrFail()->new_values['source_request_snapshot']['letter_details']);
    }

    public function test_resignation_print_records_its_saved_source(): void
    {
        $this->abilities[] = 'employee.resignation_form.print';
        $response = $this->postJson('/_test/profile-print', $this->payload('resignationLetter'))->assertCreated();
        $log = EmployeeProfileAuditLog::firstOrFail();
        $response->assertJsonPath('print_record_id', $log->id);
        $this->assertSame('resignation', $log->new_values['source_request_type']);
        $this->assertSame(3, $log->new_values['source_request_id']);
        $this->assertSame('2026-11-24', $log->new_values['source_request_snapshot']['last_working_day']);
        $this->assertSame('<p>Career change</p>', $log->new_values['source_request_snapshot']['reason']);
    }

    public function test_resignation_preview_requires_selection_and_uses_saved_details(): void
    {
        $this->abilities[] = 'create_resignation';
        $employee = (new User())->forceFill(['id' => 20, 'name' => 'Employee', 'employee_code' => 'EMP020']);
        foreach (['branch', 'department', 'post'] as $relation) {
            $employee->setRelation($relation, null);
        }
        $resignation = \App\Models\Resignation::firstOrFail();
        $vars = [
            'employee' => $employee, 'resignationPrintRequests' => collect([$resignation]),
            'selectedResignationPrintRequest' => $resignation,
            'canPrintResignationForm' => true, 'canExportResignationForm' => true,
        ];
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->view('admin.employees.profile.partials.resignation-letter', $vars)
                ->assertSee('data-source-request-id="3"', false)->assertSee('Career change')
                ->assertDontSee('&lt;p&gt;', false)->assertSee('value="2026-11-24" readonly', false)
                ->assertSee('resignation_request_id')->assertSee('return_to_profile=1', false)->assertDontSee('index.');
            $this->view('admin.employees.profile.partials.resignation-letter', array_replace($vars, ['selectedResignationPrintRequest' => null]))
                ->assertSee('data-save-linked-print disabled', false)
                ->assertSee(__('index.request_preview_empty_title'))->assertDontSee('data-source-request-id', false);
        }
    }

    public function test_saved_request_forms_render_linked_details_in_english_and_khmer(): void
    {
        $employee = (new User())->forceFill(['id' => 20, 'name' => 'Employee', 'employee_code' => 'EMP020']);
        foreach (['branch', 'department', 'post', 'supervisor'] as $relation) {
            $employee->setRelation($relation, null);
        }
        $leave = \App\Models\LeaveRequestMaster::firstOrFail();
        $leave->setRelation('leaveType', (new \App\Models\LeaveType())->forceFill(['name' => 'Annual leave']));
        $time = \App\Models\TimeLeave::firstOrFail();
        $vars = [
            'employee' => $employee, 'leavePrintRequests' => collect([$leave]), 'timeLeavePrintRequests' => collect([$time]),
            'selectedLeavePrintRequest' => $leave, 'selectedTimeLeavePrintRequest' => $time,
            'canPrintLeaveForm' => true, 'canExportLeaveForm' => false,
            'canPrintTimeLeaveForm' => true, 'canExportTimeLeaveForm' => false,
        ];
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->view('admin.employees.profile.partials.leave-letter', $vars)
                ->assertSee('data-source-request-id="1"', false)->assertSee('Family event')
                ->assertSee('Annual leave')->assertSee(__('index.save_and_print'))->assertDontSee('index.');
            $this->view('admin.employees.profile.partials.time-leave-letter', $vars)
                ->assertSee('data-source-request-id="2"', false)->assertSee('Appointment')
                ->assertSee('value="09:00" readonly', false)->assertSee(__('index.save_and_print'))->assertDontSee('index.');
            $this->view('admin.employees.profile.partials.leave-letter', array_replace($vars, ['selectedLeavePrintRequest' => null]))
                ->assertSee('data-save-linked-print disabled', false)->assertSee(__('index.linked_request_print_steps'))
                ->assertSee(__('index.request_preview_empty_title'))->assertDontSee('data-source-request-id', false);
        }
    }

    public function test_after_saving_a_request_printing_is_an_optional_next_action(): void
    {
        $this->abilities[] = 'employee.leave_form.print';
        session()->put('saved_request', ['type' => 'leave', 'id' => 1, 'employee_id' => 20]);
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->view('admin.employees.profile.partials.request-saved-next-step', [
                'savedRequestType' => 'leave', 'printPermission' => 'employee.leave_form.print',
                'printTab' => 'leave-letter', 'printRequestField' => 'leave_request_id',
            ])->assertSee(__('index.request_saved_heading'))->assertSee(__('index.request_saved_next_help'))
                ->assertSee(__('index.preview_saved_request'))->assertSee('leave_request_id=1', false)
                ->assertDontSee('window.print', false);
        }
    }

    public function test_request_list_print_link_opens_the_correct_saved_request(): void
    {
        $this->abilities[] = 'employee.leave_form.print';
        $this->view('admin.employees.profile.partials.request-print-link', [
            'requestRecord' => \App\Models\LeaveRequestMaster::firstOrFail(),
            'printPermission' => 'employee.leave_form.print', 'printTab' => 'leave-letter', 'printRequestField' => 'leave_request_id',
        ])->assertSee('leave_request_id=1', false)->assertSee('tab=leave-letter', false);
    }
}
