<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class LeaveRequestSavedNotificationTest extends TestCase
{
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

    private function banner(bool $modal = true): string
    {
        app()->instance('request', Request::create('/admin/leave-request/add', 'GET', $modal ? ['request_modal' => '1'] : []));
        return view('admin.employees.profile.partials.request-saved-next-step', [
            'savedRequestType' => 'leave', 'printPermission' => 'employee.leave_form.print',
            'printTab' => 'leave-letter', 'printRequestField' => 'leave_request_id',
        ])->render();
    }

    public function test_saved_leave_in_a_popup_notifies_the_parent_without_removing_the_success_banner(): void
    {
        session()->put('saved_request', ['type' => 'leave', 'id' => 42, 'employee_id' => 20]);
        $html = $this->banner();
        $this->assertStringContainsString('employee-request-saved-banner', $html);
        $this->assertStringContainsString('#42', $html);
        $this->assertStringContainsString("type: 'employee-request-form-updated', requestType: 'leave'", $html);
        $this->assertStringContainsString('window.location.origin', $html);
    }

    public function test_normal_page_saves_do_not_send_popup_messages(): void
    {
        session()->put('saved_request', ['type' => 'leave', 'id' => 42, 'employee_id' => 20]);
        $this->assertStringNotContainsString('postMessage', $this->banner(false));
    }

    public function test_unsaved_or_other_request_types_never_notify_a_leave_refresh(): void
    {
        $this->assertStringNotContainsString('postMessage', $this->banner());
        session()->put('saved_request', ['type' => 'resignation', 'id' => 42, 'employee_id' => 20]);
        $this->assertStringNotContainsString('postMessage', $this->banner());
    }
}
