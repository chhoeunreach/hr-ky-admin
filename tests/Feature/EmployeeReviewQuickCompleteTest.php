<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\EmployeeProfileController;
use App\Models\PerformanceReview;
use App\Models\EmployeeSalaryHistory;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeReviewQuickCompleteTest extends TestCase
{
    private bool $allowed = true;

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
        });
        DB::table('users')->insert(['id' => 20]);
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('evaluator_id')->nullable();
            foreach (['review_type', 'period_start', 'period_end', 'review_date', 'status', 'grade',
                'strengths', 'areas_for_improvement', 'manager_comment', 'employee_comment',
                'final_recommendation', 'next_review_date'] as $field) {
                $table->string($field)->nullable();
            }
            $table->decimal('total_score', 8, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('performance_review_items', function (Blueprint $table) {
            $table->id();
            $table->integer('performance_review_id');
            $table->string('criteria');
            $table->string('description')->nullable();
            $table->string('comment')->nullable();
            foreach (['max_score', 'score', 'weight', 'sort_order'] as $field) {
                $table->decimal($field, 8, 2);
            }
            $table->timestamps();
        });
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
        $this->actingAs((new User())->forceFill(['id' => 10]));
        Gate::before(fn ($user, $ability) => $this->allowed && in_array($ability, ['employee.performance.create', 'employee.salary.history.manage'], true));
        Route::post('/_test/quick-review', function (Request $request) {
            return app(EmployeeProfileController::class)->storeReview($request, (new User())->forceFill(['id' => 20]));
        })->middleware(\Illuminate\Session\Middleware\StartSession::class);
        Route::get('/_test/full-review', function () {
            return view('admin.employees.profile.partials.evaluation', [
                'employee' => (new User())->forceFill(['id' => 20]),
                'reviews' => PerformanceReview::where('employee_id', 20)->get(),
                'defaultItems' => [[__('index.review_criteria_work_quality'), __('index.review_standard_work_quality'), 100]],
            ]);
        })->middleware([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);
        Schema::create('employee_salary_history', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            foreach (['old_base_salary', 'new_base_salary', 'increase_amount', 'increase_percentage', 'allowance_before', 'allowance_after'] as $field) {
                $table->decimal($field, 15, 2)->nullable();
            }
            foreach (['effective_date', 'approval_status', 'reason', 'note'] as $field) {
                $table->string($field)->nullable();
            }
            foreach (['requested_by', 'reviewed_by', 'approved_by'] as $field) {
                $table->integer($field)->nullable();
            }
            $table->timestamps();
        });
        $migration = require database_path('migrations/2026_10_23_000001_link_salary_history_to_performance_reviews.php');
        $migration->up();
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->decimal('current_base_salary', 15, 2);
        });
        DB::table('employee_profiles')->insert(['employee_id' => 20, 'current_base_salary' => 500]);
        Route::post('/_test/salary-request', function (Request $request) {
            return app(EmployeeProfileController::class)->storeSalary($request, (new User())->forceFill(['id' => 20]));
        });
        Route::get('/_test/salary-form', function () {
            \Illuminate\Support\Facades\Cache::put('payment_currency_symbol', '$');
            return view('admin.employees.profile.partials.salary', [
                'employee' => (new User())->forceFill(['id' => 20])->setRelation('employeeSalary', null),
                'profile' => new EmployeeProfile(['current_base_salary' => 500]),
                'reviews' => PerformanceReview::all(),
                'salaryHistory' => EmployeeSalaryHistory::with('performanceReview')->get(),
                'canViewSalary' => true,
            ]);
        });
    }

    private function payload(): array
    {
        return [
            'quick_complete' => 1, 'review_type' => 'quarterly', 'status' => 'completed',
            'period_start' => '2026-05-07', 'period_end' => '2026-08-07',
            'review_date' => '2026-07-01', 'criteria' => ['Overall performance'],
            'max_score' => [100], 'score' => [85], 'manager_comment' => 'Review finished early.',
        ];
    }

    public function test_quick_complete_saves_score_audit_and_handles_repeated_submission(): void
    {
        $this->post('/_test/quick-review', $this->payload())->assertRedirect();
        $this->post('/_test/quick-review', $this->payload())->assertRedirect();
        $this->assertSame(1, PerformanceReview::count());
        $review = PerformanceReview::firstOrFail();
        $this->assertSame('completed', $review->status);
        $this->assertEquals(85, $review->total_score);
        $this->assertSame(10, $review->evaluator_id);
        $this->assertSame(1, $review->items()->count());
        $this->assertSame(1, DB::table('employee_profile_audit_logs')->count());
        $this->assertTrue($review->completesMilestone(Carbon::parse('2026-08-07')));
        $this->assertFalse($review->completesMilestone(Carbon::parse('2027-08-07')));
    }

    public function test_quick_review_modal_returns_to_the_employee_form_after_saving(): void
    {
        $this->from('/admin/employees/profile-directory')->post('/_test/quick-review', $this->payload() + ['employee_modal' => '1'])
            ->assertRedirect(route('admin.employees.profile.show', ['employee' => 20, 'tab' => 'evaluation', 'employee_modal' => '1']));
        $this->assertSame(1, PerformanceReview::count());
    }

    public function test_invalid_modal_review_reopens_the_form_without_leaving_the_directory(): void
    {
        $this->from('/admin/employees/profile-directory')->post('/_test/quick-review', array_replace($this->payload(), ['score' => [101], 'employee_modal' => '1']))
            ->assertRedirect(route('admin.employees.profile.show', ['employee' => 20, 'tab' => 'evaluation', 'review_create' => 1, 'employee_modal' => '1']))
            ->assertSessionHasErrors('score.0');
        $this->assertSame(0, PerformanceReview::count());
    }

    public function test_permission_is_required(): void
    {
        $this->allowed = false;
        $this->postJson('/_test/quick-review', $this->payload())->assertForbidden();
        $this->assertSame(0, PerformanceReview::count());
    }

    public function test_full_review_from_directory_defaults_to_completed_and_keeps_milestone(): void
    {
        $response = $this->get('/_test/full-review?review_create=1&review_type=six_month'
            .'&period_start=2026-02-07&period_end=2026-08-07&review_date=2026-08-07');
        $response->assertOk();
        $response->assertSee('value="six_month" selected', false);
        $response->assertSee('value="completed" selected', false);
        $response->assertSee('name="period_start" value="2026-02-07"', false);
        $response->assertSee('name="period_end" value="2026-08-07"', false);
        $response->assertSee('name="review_date" value="2026-08-07"', false);

        $payload = array_replace($this->payload(), [
            'review_type' => 'six_month', 'period_start' => '2026-02-07',
            'criteria' => ['Work quality', 'Teamwork'],
            'max_score' => [60, 40], 'score' => [50, 35],
        ]);
        unset($payload['quick_complete']);
        $this->post('/_test/quick-review', $payload)->assertRedirect();
        $review = PerformanceReview::firstOrFail();
        $this->assertSame('completed', $review->status);
        $this->assertSame(2, $review->items()->count());
        $this->assertEquals(85, $review->total_score);
        $this->assertTrue($review->completesMilestone(Carbon::parse('2026-08-07')));
    }

    public function test_regular_full_review_still_defaults_to_draft(): void
    {
        $this->get('/_test/full-review')->assertOk()->assertSee('value="draft" selected', false);
    }

    public function test_custom_criteria_and_scores_are_restored_after_validation_failure(): void
    {
        $this->withSession(['_old_input' => [
            'review_form' => 1, 'criteria' => ['Custom criterion', 'Another criterion'],
            'description' => ['Custom standard', 'Second standard'],
            'max_score' => [75, 25], 'score' => [60, 20], 'comment' => ['Good work', 'Teamwork'],
        ]])->get('/_test/full-review')->assertOk()
            ->assertSee('data-reopen="1"', false)
            ->assertSee('name="criteria[]" value="Custom criterion"', false)
            ->assertSee('name="criteria[]" value="Another criterion"', false)
            ->assertSee('name="description[]"', false)->assertSee('Custom standard')
            ->assertSee('name="max_score[]" value="75"', false)
            ->assertSee('name="score[]" value="60"', false)
            ->assertSee('name="comment[]" value="Good work"', false);
    }

    public function test_full_review_saves_custom_rows_and_requires_at_least_one_criterion(): void
    {
        $payload = array_replace($this->payload(), [
            'criteria' => ['Custom quality', 'Custom teamwork', 'Initiative'],
            'description' => ['Quality target', 'Team target', 'Initiative target'],
            'max_score' => [60, 25, 15], 'score' => [50, 20, 10],
            'comment' => ['Quality comment', 'Team comment', 'Initiative comment'],
        ]);
        unset($payload['quick_complete']);
        $this->post('/_test/quick-review', $payload)->assertRedirect();
        $review = PerformanceReview::firstOrFail();
        $this->assertEquals(80, $review->total_score);
        $this->assertSame(['Custom quality', 'Custom teamwork', 'Initiative'], $review->items->pluck('criteria')->all());
        $this->assertSame('Team comment', $review->items[1]->comment);
        $this->postJson('/_test/quick-review', array_replace($payload, ['criteria' => []]))->assertUnprocessable();
        $this->assertSame(1, PerformanceReview::count());
    }

    private function salaryPayload(): array
    {
        $this->post('/_test/quick-review', $this->payload())->assertRedirect();
        return [
            'performance_review_id' => PerformanceReview::firstOrFail()->id,
            'effective_date' => '2026-11-01', 'old_base_salary' => 500, 'new_base_salary' => 550,
            'increase_amount' => 999, 'increase_percentage' => 999, 'approval_status' => 'pending',
            'reason' => 'Performance review increase',
        ];
    }

    public function test_completed_review_can_request_salary_increase_without_changing_current_salary(): void
    {
        $payload = $this->salaryPayload();
        $this->get('/_test/full-review')->assertOk()->assertSee('salary_review='.$payload['performance_review_id'], false);
        $this->get('/_test/salary-form?salary_review='.$payload['performance_review_id'])
            ->assertOk()->assertSee('value="pending" selected', false)
            ->assertSee('name="old_base_salary" value="500"', false);
        $this->post('/_test/salary-request', $payload)->assertRedirect(route('admin.employees.profile.show', ['employee' => 20, 'tab' => 'salary']));
        $record = EmployeeSalaryHistory::firstOrFail();
        $this->assertSame($payload['performance_review_id'], $record->performanceReview->id);
        $this->assertSame('pending', $record->approval_status);
        $this->assertEquals(50, $record->increase_amount);
        $this->assertEquals(10, $record->increase_percentage);
        $this->assertEquals(500, DB::table('employee_profiles')->value('current_base_salary'));
        $this->assertDatabaseHas('employee_profile_audit_logs', ['module' => 'salary', 'action' => 'create', 'record_id' => $record->id]);
    }

    public function test_linked_increase_requires_completed_review_for_the_same_employee(): void
    {
        $payload = $this->salaryPayload();
        $review = PerformanceReview::firstOrFail();
        $review->update(['status' => 'draft']);
        $this->postJson('/_test/salary-request', $payload)->assertUnprocessable();
        DB::table('users')->insert(['id' => 30]);
        $review->update(['status' => 'completed', 'employee_id' => 30]);
        $this->postJson('/_test/salary-request', $payload)->assertUnprocessable();
        $this->assertSame(0, EmployeeSalaryHistory::count());
    }

    public function test_linked_increase_requires_permission_and_cannot_bypass_pending_approval(): void
    {
        $payload = $this->salaryPayload();
        $this->postJson('/_test/salary-request', array_replace($payload, ['approval_status' => 'approved']))
            ->assertUnprocessable();
        $this->postJson('/_test/salary-request', array_replace($payload, ['new_base_salary' => 450]))
            ->assertUnprocessable();
        $this->postJson('/_test/salary-request', array_replace($payload, ['effective_date' => null]))
            ->assertUnprocessable();
        $this->allowed = false;
        $this->postJson('/_test/salary-request', $payload)->assertForbidden();
        $this->assertSame(0, EmployeeSalaryHistory::count());
        $this->assertEquals(500, DB::table('employee_profiles')->value('current_base_salary'));
    }

    public function test_review_and_salary_forms_and_validation_are_translated_in_english_and_khmer(): void
    {
        $payload = $this->salaryPayload();
        foreach ([
            'en' => ['Performance Reviews', 'Full review', 'Request salary increase', 'Work Quality - Graphic Design'],
            'km' => ['ការវាយតម្លៃការងារ', 'ការវាយតម្លៃពេញលេញ', 'ស្នើសុំដំឡើងប្រាក់ខែ', 'គុណភាពការងារ - រចនាក្រាហ្វិក'],
        ] as $locale => [$heading, $fullReview, $salaryAction, $criteria]) {
            app()->setLocale($locale);
            foreach (['monthly', 'quarterly', 'six_month', 'annual', 'probation', 'special',
                'draft', 'submitted', 'employee_acknowledged', 'manager_approved', 'hr_approved',
                'completed', 'rejected', 'pending', 'approved', 'cancelled',
                'review_three_months', 'review_six_months', 'review_twelve_months', 'review_yearly'] as $key) {
                $this->assertTrue(\Illuminate\Support\Facades\Lang::hasForLocale('index.'.$key, $locale));
                if ($locale === 'km') {
                    $this->assertMatchesRegularExpression('/\p{Khmer}/u', __('index.'.$key));
                }
            }
            $this->get('/_test/full-review?review_create=1')->assertOk()
                ->assertSee($heading)->assertSee($salaryAction)->assertSee($criteria)
                ->assertSee(__('index.add_review_criterion'))->assertSee(__('index.remove_review_criterion'))
                ->assertSee(__('index.quarterly'))->assertSee(__('index.manager_approved'))
                ->assertDontSee('index.');
            $this->view('admin.employees.profile.partials.quick-complete-review')
                ->assertSee($fullReview)->assertSee(__('index.quick_complete_review_help'))
                ->assertSee(__('index.mark_review_completed'))->assertDontSee('index.');
            $this->get('/_test/salary-form?salary_review='.$payload['performance_review_id'])
                ->assertOk()->assertSee(__('index.linked_salary_increase_help'))
                ->assertSee(__('index.salary_increase_must_be_greater'))
                ->assertSee(__('index.pending'))->assertDontSee('index.');
            $this->postJson('/_test/salary-request', array_replace($payload, ['new_base_salary' => 450]))
                ->assertUnprocessable()->assertJsonPath('message', __('index.salary_increase_must_be_greater'));
            $this->postJson('/_test/quick-review', array_replace($this->payload(), ['score' => [-1]]))
                ->assertUnprocessable()->assertJsonPath('message', __('validation.min.numeric', ['attribute' => __('index.score'), 'min' => 0]));
            $this->post('/_test/quick-review', $this->payload())->assertRedirect()
                ->assertSessionHas('success', __('index.performance_review_completed'));
        }
    }

    public function test_missing_score_or_invalid_period_cannot_complete(): void
    {
        $this->postJson('/_test/quick-review', array_replace($this->payload(), ['score' => []]))->assertUnprocessable();
        $this->postJson('/_test/quick-review', array_replace($this->payload(), ['period_end' => '2026-01-01']))->assertUnprocessable();
        $this->postJson('/_test/quick-review', array_replace($this->payload(), ['score' => [101]]))->assertUnprocessable();
        $this->assertSame(0, PerformanceReview::count());
    }

    public function test_only_completed_reviews_count_as_done(): void
    {
        foreach (['draft', 'submitted', 'rejected', 'manager_approved'] as $status) {
            $review = new PerformanceReview(['status' => $status, 'period_end' => '2026-08-07']);
            $this->assertFalse($review->completesMilestone(Carbon::parse('2026-08-07')));
        }
        $legacy = new PerformanceReview(['status' => 'completed', 'review_date' => '2026-08-01']);
        $this->assertTrue($legacy->completesMilestone(Carbon::parse('2026-08-07')));
    }
}
