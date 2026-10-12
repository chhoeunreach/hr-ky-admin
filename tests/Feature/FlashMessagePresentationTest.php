<?php

namespace Tests\Feature;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FlashMessagePresentationTest extends TestCase
{
    public function test_flash_messages_escape_content_and_offer_accessible_dismiss_controls(): void
    {
        session()->put([
            'success' => 'Employee details saved.',
            'danger' => '<script>alert("error")</script>',
            'info' => 'No matching employees found.',
            'warning' => 'Check the selected period.',
            'warning_important' => true,
        ]);
        $errors = new ViewErrorBag();
        $errors->put('default', new MessageBag(['name' => ['Please enter an employee name.']]));

        $html = view('admin.section.flash_message', compact('errors'))->render();

        $this->assertSame(5, substr_count($html, 'app-flash-message-content'));
        $this->assertSame(4, substr_count($html, 'data-bs-dismiss="alert"'));
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('id="message"', $html);
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('alert-important', $html);
        $this->assertStringContainsString('Please enter an employee name.', $html);
    }

    public function test_shared_dialog_labels_are_available_in_english_and_khmer(): void
    {
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $html = view('admin.section.dialog_messages')->render();

            $this->assertStringContainsString('window.AppDialogMessages', $html);
            $this->assertStringContainsString('assets/js/app-dialogs.js', $html);
            $this->assertStringNotContainsString('index.dialog_', $html);
        }
    }
}
