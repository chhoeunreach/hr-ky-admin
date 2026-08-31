<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\TelegramGroup;
use App\Repositories\TelegramGroupRepository;
use App\Requests\Telegram\TelegramGroupRequest;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'name' => $request->name ?? null,
                'action_key' => $request->action_key ?? null,
                'is_active' => $request->is_active ?? null,
                'per_page' => $request->per_page ?? TelegramGroup::RECORDS_PER_PAGE,
            ];
            $telegramGroups = $this->telegramGroupRepo->getPaginated($filterParameters);
            $actionOptions = TelegramGroup::actionOptions();

            return view($this->view . 'index', compact('telegramGroups', 'filterParameters', 'actionOptions'));
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
        $chatOptions = $this->chatOptions();

        return view($this->view . 'create', compact('branches', 'departments', 'actionOptions', 'eventOptions', 'chatOptions'));
    }

    public function store(TelegramGroupRequest $request): RedirectResponse
    {
        $this->authorize('create_telegram_group');

        try {
            DB::beginTransaction();
            $this->telegramGroupRepo->store($this->prepareTelegramGroupData($request->validated()));
            DB::commit();

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
            $chatOptions = $this->chatOptions();

            return view($this->view . 'edit', compact('telegramGroup', 'branches', 'departments', 'actionOptions', 'eventOptions', 'chatOptions'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(TelegramGroupRequest $request, int $id): RedirectResponse
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

            return redirect()
                ->route('admin.telegram-groups.index')
                ->with('success', 'Telegram group updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $this->authorize('toggle_telegram_group_status');

        try {
            DB::beginTransaction();
            $this->telegramGroupRepo->toggleStatus($id);
            DB::commit();

            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function test(int $id, TelegramService $telegramService): RedirectResponse
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

            foreach (array_filter($chatIds) as $chatId) {
                $sent = $telegramService->sendMessage(
                    $chatId,
                    'Telegram group test from HR Admin: ' . $telegramGroup->name
                );

                if (! $sent) {
                    $errors[] = $telegramService->lastError() ?: 'Failed to send to chat ID ' . $chatId . '.';
                }

                $ok = $sent && $ok;
            }

            if (! $ok) {
                $message = 'Telegram test failed. ' . implode(' ', array_unique($errors));

                if (str_contains($message, 'chat not found')) {
                    $message .= ' Add this bot to the Telegram group/channel, then send /chatid there and update this Telegram Group chat ID.';
                }

                return redirect()->back()->with(
                    'danger',
                    $message
                );
            }

            return redirect()->back()->with('success', 'Telegram test sent successfully.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete(int $id): RedirectResponse
    {
        $this->authorize('delete_telegram_group');

        try {
            $telegramGroup = $this->telegramGroupRepo->find($id);
            if (! $telegramGroup) {
                throw new Exception('Telegram group not found.', 404);
            }

            DB::beginTransaction();
            $this->telegramGroupRepo->delete($telegramGroup);
            DB::commit();

            return redirect()->back()->with('success', 'Telegram group deleted successfully.');
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function prepareTelegramGroupData(array $data): array
    {
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
