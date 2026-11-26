<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\TelegramGroup;
use App\Models\TelegramNotificationRule;
use App\Services\TelegramService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TelegramNotificationRuleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('list_telegram_group');

        $query = TelegramNotificationRule::query();

        if ($request->filled('event_key')) {
            $query->where(function ($q) use ($request) {
                $q->where('event_key', $request->input('event_key'))
                    ->orWhereJsonContains('event_keys', $request->input('event_key'));
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (int) $request->input('is_active'));
        }

        if ($request->filled('keyword')) {
            $kw = trim($request->input('keyword'));
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                    ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $rules = $query->latest('id')->paginate($perPage)->withQueryString();

        $channels = TelegramGroup::orderBy('name')->get();
        $channelsById = $channels->keyBy('id');
        $eventOptions = TelegramGroup::eventOptions();
        $categorizedEvents = TelegramGroup::categorizedEventOptions();

        $totalRules = TelegramNotificationRule::count();
        $activeRules = TelegramNotificationRule::where('is_active', true)->count();
        $totalChannels = TelegramGroup::count();

        return view('admin.telegramRule.index', compact(
            'rules',
            'channels',
            'channelsById',
            'eventOptions',
            'categorizedEvents',
            'totalRules',
            'activeRules',
            'totalChannels'
        ));
    }

    public function create(): View
    {
        $this->authorize('create_telegram_group');

        $channels = TelegramGroup::where('is_active', true)->orderBy('name')->get();
        $branches = Schema::hasTable('branches') ? Branch::orderBy('name')->get() : collect();
        $departments = Schema::hasTable('departments') ? Department::orderBy('dept_name')->get() : collect();
        $categorizedEvents = TelegramGroup::categorizedEventOptions();
        $eventOptions = TelegramGroup::eventOptions();

        return view('admin.telegramRule.create', compact(
            'channels',
            'branches',
            'departments',
            'categorizedEvents',
            'eventOptions'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create_telegram_group');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'event_key' => ['required', 'string', 'max:100'],
            'event_keys' => ['nullable', 'array'],
            'branch_scope' => ['required', 'in:all,specific'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer'],
            'channel_ids' => ['required', 'array', 'min:1'],
            'channel_ids.*' => ['integer'],
            'send_location' => ['nullable', 'boolean'],
            'send_selfie' => ['nullable', 'boolean'],
            'is_silent' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['send_location'] = $request->boolean('send_location', true);
        $validated['send_selfie'] = $request->boolean('send_selfie', true);
        $validated['is_silent'] = $request->boolean('is_silent', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        TelegramNotificationRule::create($validated);

        return redirect()
            ->route('admin.telegram-rules.index')
            ->with('success', 'វិធានការបញ្ជូនសារថ្មីត្រូវបានបង្កើតដោយជោគជ័យ (Notification Rule created successfully).');
    }

    public function edit(int $id): View
    {
        $this->authorize('edit_telegram_group');

        $rule = TelegramNotificationRule::findOrFail($id);
        $channels = TelegramGroup::orderBy('name')->get();
        $branches = Schema::hasTable('branches') ? Branch::orderBy('name')->get() : collect();
        $departments = Schema::hasTable('departments') ? Department::orderBy('dept_name')->get() : collect();
        $categorizedEvents = TelegramGroup::categorizedEventOptions();
        $eventOptions = TelegramGroup::eventOptions();

        return view('admin.telegramRule.edit', compact(
            'rule',
            'channels',
            'branches',
            'departments',
            'categorizedEvents',
            'eventOptions'
        ));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorize('edit_telegram_group');

        $rule = TelegramNotificationRule::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'event_key' => ['required', 'string', 'max:100'],
            'event_keys' => ['nullable', 'array'],
            'branch_scope' => ['required', 'in:all,specific'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer'],
            'channel_ids' => ['required', 'array', 'min:1'],
            'channel_ids.*' => ['integer'],
            'send_location' => ['nullable', 'boolean'],
            'send_selfie' => ['nullable', 'boolean'],
            'is_silent' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['send_location'] = $request->boolean('send_location', true);
        $validated['send_selfie'] = $request->boolean('send_selfie', true);
        $validated['is_silent'] = $request->boolean('is_silent', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        $rule->update($validated);

        return redirect()
            ->route('admin.telegram-rules.index')
            ->with('success', 'វិធានការបញ្ជូនសារត្រូវបានកែប្រែដោយជោគជ័យ (Notification Rule updated successfully).');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->authorize('delete_telegram_group');

        $rule = TelegramNotificationRule::findOrFail($id);
        $rule->delete();

        return redirect()
            ->back()
            ->with('success', 'វិធានការបញ្ជូនសារត្រូវបានលុបចេញ (Notification Rule deleted successfully).');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $this->authorize('edit_telegram_group');

        $rule = TelegramNotificationRule::findOrFail($id);
        $rule->update([
            'is_active' => ! $rule->is_active,
        ]);

        $statusText = $rule->is_active ? 'បើកដំណើរការ (Activated)' : 'បិទដំណើរការ (Deactivated)';

        return redirect()
            ->back()
            ->with('success', "ស្ថានភាពវិធានការត្រូវបានផ្លាស់ប្តូរទៅជា {$statusText}។");
    }

    public function syncDefault(Request $request): RedirectResponse
    {
        $this->authorize('create_telegram_group');

        $count = \App\Services\Telegram\TelegramRuleSyncService::syncDefaultRules();

        if ($count > 0) {
            return redirect()
                ->route('admin.telegram-rules.index')
                ->with('success', "ទិន្នន័យដើមនៃវិធានការចំនួន {$count} ត្រូវបានបង្កើតចេញពី Chat IDs ដែលមានស្រាប់ដោយជោគជ័យ!");
        }

        return redirect()
            ->route('admin.telegram-rules.index')
            ->with('info', 'វិធានការទាំងអស់ត្រូវបានបង្កើតរួចរាល់ហើយ គ្មានទិន្នន័យថ្មីដែលត្រូវបង្កើតបន្ថែមទេ។ (All rules are already up to date).');
    }

    public function testRule(int $id, TelegramService $telegramService): RedirectResponse
    {
        $this->authorize('test_telegram_group');

        try {
            $rule = TelegramNotificationRule::findOrFail($id);
            $channels = $rule->channels();

            if ($channels->isEmpty()) {
                throw new Exception('វិធានការនេះមិនទាន់មាន Channel/Group ណាមួយត្រូវបានភ្ជាប់នៅឡើយទេ។ (No channel assigned to this rule).');
            }

            $ok = true;
            $errors = [];
            $testedChannels = [];

            $testMessage = "🔔 <b>សារតេស្តវិធានការ (Notification Rule Test)</b>\n"
                . "━━━━━━━━━━━━━━━━━━━\n"
                . "📋 <b>ឈ្មោះវិធានការ:</b> {$rule->name}\n"
                . "⚡ <b>ព្រឹត្តិការណ៍:</b> {$rule->event_label} (<code>{$rule->event_key}</code>)\n"
                . "🏢 <b>វិសាលភាពសាខា:</b> " . ($rule->branch_scope === 'all' ? 'គ្រប់សាខា (All Branches)' : 'សាខាជាក់លាក់ (Specific Branches)') . "\n"
                . "⏰ <b>ម៉ោងសាកល្បង:</b> " . now()->format('d/m/Y H:i:s') . "\n"
                . "━━━━━━━━━━━━━━━━━━━\n"
                . "✅ <i>ប្រព័ន្ធបញ្ជូនសារដំណើរការបានត្រឹមត្រូវ ១០០% (Rule Connected)</i>";

            foreach ($channels as $channel) {
                $chatIds = $channel->chat_ids ?: [$channel->chat_id];
                $topicId = $channel->topic_id ? (int) $channel->topic_id : null;

                foreach (array_filter($chatIds) as $chatId) {
                    $sent = $telegramService->sendMessage(
                        $chatId,
                        $testMessage,
                        'HTML',
                        $topicId
                    );

                    if (! $sent) {
                        $err = $telegramService->lastError() ?: "Failed to send to chat ID {$chatId}";
                        $errors[] = "[{$channel->name}]: {$err}";
                        $ok = false;
                    } else {
                        $testedChannels[] = $channel->name;
                    }
                }
            }

            $rule->update([
                'last_test_at' => now(),
                'last_test_status' => $ok ? 'success' : 'failed',
            ]);

            if (! $ok) {
                $errMsg = 'ការធ្វើតេស្តបានបរាជ័យខ្លះ៖ ' . implode(' | ', array_unique($errors));
                return redirect()->back()->with('danger', $errMsg);
            }

            $channelCount = count(array_unique($testedChannels));
            return redirect()->back()->with(
                'success',
                "ការធ្វើតេស្តវិធានការបានជោគជ័យ! សារត្រូវបានផ្ញើទៅកាន់ Channels ចំនួន {$channelCount} ដោយជោគជ័យ។"
            );
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }
}
