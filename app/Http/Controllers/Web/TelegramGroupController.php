<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\TelegramGroup;
use App\Models\TelegramNotificationRule;
use App\Repositories\TelegramGroupRepository;
use App\Requests\Telegram\TelegramGroupRequest;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TelegramGroupController extends Controller
{
    use CustomAuthorizesRequests;

    private string $view = 'admin.telegramGroup.';

    public function __construct(private TelegramGroupRepository $telegramGroupRepo)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('list_telegram_group');

        try {
            $filterParameters = [
                'search' => $request->search ?? null,
                'name' => $request->name ?? null,
                'action_key' => $request->action_key ?? null,
                'is_active' => $request->is_active ?? null,
                'per_page' => $request->per_page ?? TelegramGroup::RECORDS_PER_PAGE,
            ];
            $telegramGroups = $this->telegramGroupRepo->getPaginated($filterParameters);
            $actionOptions = TelegramGroup::actionOptions();
            $totalChannels = TelegramGroup::count();
            $totalRules = Schema::hasTable('telegram_notification_rules') ? TelegramNotificationRule::count() : 0;

            return view($this->view . 'index', compact(
                'telegramGroups',
                'filterParameters',
                'actionOptions',
                'totalChannels',
                'totalRules'
            ));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_telegram_group');

        $branches = Branch::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'dept_name', 'branch_id')->orderBy('dept_name')->get();
        $actionOptions = TelegramGroup::actionOptions();
        $eventOptions = TelegramGroup::eventOptions();
        $categorizedEvents = TelegramGroup::categorizedEventOptions();
        $chatOptions = $this->chatOptions();

        return view($this->view . 'create', compact('branches', 'departments', 'actionOptions', 'eventOptions', 'categorizedEvents', 'chatOptions'));
    }

    public function store(TelegramGroupRequest $request)
    {
        $this->authorize('create_telegram_group');

        try {
            DB::beginTransaction();
            $this->telegramGroupRepo->store($this->prepareTelegramGroupData($request->validated()));
            DB::commit();

            if ($request->input('telegram_group_modal') === '1') {
                session()->flash('success', 'Telegram group created successfully.');
                return view($this->view . 'common.modal-saved', ['message' => 'Telegram group created successfully.']);
            }

            return redirect()
                ->route('admin.telegram-groups.index')
                ->with('success', 'Telegram group created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function edit(int $id)
    {
        $this->authorize('edit_telegram_group');

        try {
            $telegramGroup = $this->telegramGroupRepo->find($id);
            if (! $telegramGroup) {
                throw new Exception('Telegram group not found.', 404);
            }

            $branches = Branch::select('id', 'name')->orderBy('name')->get();
            $departments = Department::select('id', 'dept_name', 'branch_id')->orderBy('dept_name')->get();
            $actionOptions = TelegramGroup::actionOptions();
            $eventOptions = TelegramGroup::eventOptions();
            $categorizedEvents = TelegramGroup::categorizedEventOptions();
            $chatOptions = $this->chatOptions();

            return view($this->view . 'edit', compact('telegramGroup', 'branches', 'departments', 'actionOptions', 'eventOptions', 'categorizedEvents', 'chatOptions'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(TelegramGroupRequest $request, int $id)
    {
        $this->authorize('edit_telegram_group');

        try {
            $telegramGroup = $this->telegramGroupRepo->find($id);
            if (! $telegramGroup) {
                throw new Exception('Telegram group not found.', 404);
            }

            DB::beginTransaction();
            $this->telegramGroupRepo->update($telegramGroup, $this->prepareTelegramGroupData($request->validated()));
            DB::commit();

            if ($request->input('telegram_group_modal') === '1') {
                session()->flash('success', 'Telegram group updated successfully.');
                return view('admin.telegramGroup.common.modal-saved', ['message' => 'Telegram group updated successfully.']);
            }

            return redirect()
                ->route('admin.telegram-groups.index')
                ->with('success', 'Telegram group updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function toggleStatus(int $id)
    {
        $this->authorize('toggle_telegram_group_status');

        try {
            DB::beginTransaction();
            $this->telegramGroupRepo->toggleStatus($id);
            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => __('message.status_changed') ?: 'Status changed successfully.',
                ]);
            }

            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $exception->getMessage(),
                ], 400);
            }

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function test(int $id, TelegramService $telegramService)
    {
        $this->authorize('test_telegram_group');

        try {
            $telegramGroup = $this->telegramGroupRepo->find($id);
            if (! $telegramGroup) {
                throw new Exception('Telegram group not found.', 404);
            }

            $ok = true;
            $errors = [];
            $chatIds = $telegramGroup->chat_ids ?: [$telegramGroup->chat_id];
            $topicId = $telegramGroup->topic_id ? (int) $telegramGroup->topic_id : null;

            $testMessage = "🔔 <b>សារសាកល្បងពីប្រព័ន្ធ HR Admin (Telegram Test)</b>\n" .
                "🏢 ឈ្មោះក្រុម: <b>{$telegramGroup->name}</b>\n" .
                ($topicId ? "📌 Topic/Thread ID: <code>{$topicId}</code>\n" : '') .
                "⏱️ ពេលវេលា: " . now()->format('d/m/Y H:i:s') . "\n" .
                "✅ ដំណើរការតភ្ជាប់ជោគជ័យ (Connected successfully)";

            foreach (array_filter($chatIds) as $chatId) {
                $sent = $telegramService->sendMessage(
                    $chatId,
                    $testMessage,
                    'HTML',
                    $topicId
                );

                if (! $sent) {
                    $errors[] = $telegramService->lastError() ?: 'Failed to send to chat ID ' . $chatId . '.';
                }

                $ok = $sent && $ok;
            }

            $telegramGroup->update([
                'last_tested_at' => now(),
                'last_test_status' => $ok ? 'success' : 'failed',
            ]);

            if (! $ok) {
                $message = 'Telegram test failed. ' . implode(' ', array_unique($errors));

                if (str_contains($message, 'chat not found')) {
                    $message .= ' Add this bot to the Telegram group/channel, then send /chatid there and update this Telegram Group chat ID.';
                }

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'status' => false,
                        'message' => $message,
                    ], 400);
                }

                return redirect()->back()->with(
                    'danger',
                    $message
                );
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Telegram test sent successfully.',
                ]);
            }

            return redirect()->back()->with('success', 'Telegram test sent successfully.');
        } catch (Exception $exception) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $exception->getMessage(),
                ], 400);
            }

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function sendTestMessage(Request $request, TelegramService $telegramService)
    {
        $this->authorize('test_telegram_group');

        $validated = $request->validate([
            'chat_ids' => ['nullable', 'array'],
            'chat_ids.*' => ['nullable', 'string', 'max:100'],
            'chat_id' => ['nullable', 'string', 'max:100'],
            'topic_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:180'],
            'id' => ['nullable', 'integer'],
        ]);

        try {
            $group = null;
            $chatIds = [];
            if (!empty($validated['id'])) {
                $group = $this->telegramGroupRepo->find((int) $validated['id']);
                if ($group) {
                    $chatIds = $group->chat_ids ?: [$group->chat_id];
                }
            }

            if (empty($chatIds)) {
                $rawList = !empty($validated['chat_ids']) ? $validated['chat_ids'] : [];
                if (!empty($validated['chat_id'])) {
                    $rawList[] = $validated['chat_id'];
                }
                $chatIds = array_filter(array_map('trim', $rawList));
            }

            if (empty($chatIds)) {
                throw new Exception('សូមបញ្ចូល Chat ID ជាមុនសិន មុននឹងធ្វើតេស្ត។ (Please specify at least one Chat ID).');
            }

            $topicId = !empty($validated['topic_id']) ? (int) $validated['topic_id'] : ($group?->topic_id ?? null);
            $groupName = $validated['name'] ?? ($group?->name ?? 'HR KY Admin');
            $ok = true;
            $errors = [];

            $testMessage = "🔔 <b>សារសាកល្បងពីប្រព័ន្ធ HR Admin (Telegram Test Message)</b>\n" .
                "🏢 ឈ្មោះក្រុម: <b>{$groupName}</b>\n" .
                ($topicId ? "📌 Topic/Thread ID: <code>{$topicId}</code>\n" : '') .
                "⏱️ ពេលវេលា: " . now()->format('d/m/Y H:i:s') . "\n" .
                "✅ ប្រព័ន្ធតេឡេក្រាមបានតភ្ជាប់ជោគជ័យ ១០០% (Connected successfully)";

            foreach (array_unique($chatIds) as $chatId) {
                $sent = $telegramService->sendMessage($chatId, $testMessage, 'HTML', $topicId);
                if (!$sent) {
                    $err = $telegramService->lastError() ?: "Failed to send to chat ID {$chatId}.";
                    $errors[] = $err;
                    $ok = false;
                }
            }

            if ($group) {
                $group->update([
                    'last_tested_at' => now(),
                    'last_test_status' => $ok ? 'success' : 'failed',
                ]);
            }

            if (!$ok) {
                $errorMessage = 'ការផ្ញើសារសាកល្បងមិនជោគជ័យ: ' . implode(' ', array_unique($errors));
                if (str_contains($errorMessage, 'chat not found')) {
                    $errorMessage .= ' សូមប្រាកដថាបានទាញ Bot ចូលក្នុង Group/Channel និងបានផ្តល់សិទ្ធិ Admin ឬផ្ញើ /start ទៅកាន់ Bot។';
                }

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['status' => false, 'message' => $errorMessage], 422);
                }

                return redirect()->back()->with('danger', $errorMessage);
            }

            $successMessage = 'សារសាកល្បងត្រូវបានផ្ញើទៅកាន់ Telegram Group ដោយជោគជ័យ!';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => true, 'message' => $successMessage]);
            }

            return redirect()->back()->with('success', $successMessage);
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        $this->authorize('delete_telegram_group');

        try {
            $telegramGroup = $this->telegramGroupRepo->find($id);
            if (! $telegramGroup) {
                throw new Exception('Telegram group not found.', 404);
            }

            DB::beginTransaction();
            $this->telegramGroupRepo->delete($telegramGroup);

            // Clean up deleted channel ID from notification rules
            if (Schema::hasTable('telegram_notification_rules')) {
                $rules = TelegramNotificationRule::query()->get();
                foreach ($rules as $rule) {
                    if (is_array($rule->channel_ids) && in_array($id, $rule->channel_ids, true)) {
                        $remaining = array_values(array_filter($rule->channel_ids, fn ($chId) => (int) $chId !== $id));
                        $rule->update(['channel_ids' => $remaining]);
                    }
                }
            }

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Telegram group deleted successfully.',
                ]);
            }

            return redirect()->back()->with('success', 'Telegram group deleted successfully.');
        } catch (Exception $exception) {
            DB::rollBack();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => $exception->getMessage(),
                ], 400);
            }

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function prepareTelegramGroupData(array $data): array
    {
        if (empty($data['action_keys']) && ! empty($data['event_keys'])) {
            $derivedActions = [];
            foreach ($data['event_keys'] as $eventKey) {
                $parent = TelegramGroup::parentActionForEvent($eventKey);
                if ($parent) {
                    $derivedActions[] = $parent;
                }
            }
            $data['action_keys'] = array_values(array_unique($derivedActions));
        }

        if (empty($data['action_keys'])) {
            $data['action_keys'] = [TelegramGroup::ACTION_GENERAL];
        }

        if (empty($data['action_key'])) {
            $data['action_key'] = $data['action_keys'][0] ?? TelegramGroup::ACTION_GENERAL;
        }

        if (! empty($data['branch_ids'])) {
            $branchNames = Branch::whereIn('id', $data['branch_ids'])
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
            $data['branch_name'] = implode(',', $branchNames);
        } elseif (! empty($data['branch_id'])) {
            $branch = Branch::select('id', 'name')->find($data['branch_id']);
            $data['branch_name'] = $branch?->name ?? $data['branch_name'] ?? null;
        } else {
            $data['branch_name'] = null;
        }

        if (! empty($data['department_ids'])) {
            $departmentNames = Department::whereIn('id', $data['department_ids'])
                ->orderBy('dept_name')
                ->pluck('dept_name')
                ->toArray();
            $data['department_name'] = implode(',', $departmentNames);
        } elseif (! empty($data['department_id'])) {
            $department = Department::select('id', 'dept_name')->find($data['department_id']);
            $data['department_name'] = $department?->dept_name ?? $data['department_name'] ?? null;
        } else {
            $data['department_name'] = null;
        }

        return $data;
    }

    private function chatOptions(): array
    {
        return TelegramGroup::query()
            ->get(['chat_id', 'chat_ids'])
            ->flatMap(function (TelegramGroup $telegramGroup) {
                return $telegramGroup->chat_ids ?: [$telegramGroup->chat_id];
            })
            ->map(fn ($chatId) => trim((string) $chatId))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
