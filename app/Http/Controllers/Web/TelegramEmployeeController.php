<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Services\TelegramService;
use App\Support\TelegramBotSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'branch_id' => $request->input('branch_id'),
            'department_id' => $request->input('department_id'),
            'linked' => $request->input('linked'),
            'active_employee' => $request->input('active_employee'),
        ];

        $employeeQuery = $this->buildEmployeeQuery($filters);

        $statsQuery = $this->buildEmployeeQuery($filters);

        $perPage = 25;
        $page = max(1, (int) $request->input('page', 1));

        $employees = $employeeQuery
            ->orderByRaw('CASE WHEN users.employee_code IS NULL OR users.employee_code = "" THEN 1 ELSE 0 END')
            ->orderBy('employee_code')
            ->orderBy('name')
            ->paginate($perPage);

        $branches = Branch::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'dept_name')->orderBy('dept_name')->get();
        $botSettings = TelegramBotSettings::all();
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'linked' => (clone $statsQuery)->whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '')->count(),
            'unlinked' => (clone $statsQuery)->where(function ($query) {
                $query->whereNull('telegram_chat_id')->orWhere('telegram_chat_id', '');
            })->count(),
        ];
        $activeEmployeeId = (int) $filters['active_employee'];

        if (! $activeEmployeeId || ! $employees->contains('id', $activeEmployeeId)) {
            $activeEmployeeId = (int) optional($employees->first())->id;
        }

        if ($request->expectsJson()) {
            $employeeData = $employees->map(function ($employee) {
                $avatar = $employee->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');
                $initial = mb_substr(trim($employee->name ?: $employee->username ?: 'U'), 0, 1);
                $preview = $employee->telegram_chat_id ? ($employee->telegram_username ? '@' . $employee->telegram_username : 'Chat ID ' . $employee->telegram_chat_id) : 'Waiting for Telegram link';
                $headerStatus = trim(implode(' · ', array_filter([$employee->phone, $employee->employee_code ?: $employee->username, $employee->branch?->name, $employee->telegram_chat_id ? 'connected via Telegram' : 'not connected'])));
                $connectUrl = \App\Support\TelegramBotSettings::connectUrl($employee);
                $username = $employee->telegram_username ? '@' . $employee->telegram_username : 'Not saved';

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'avatar' => $avatar,
                    'has_avatar' => (bool) $employee->avatar,
                    'initial' => $initial,
                    'preview' => $preview,
                    'header_status' => $headerStatus,
                    'linked_at' => $employee->telegram_linked_at ? optional($employee->telegram_linked_at)->format('M d') : 'New',
                    'has_chat' => (bool) $employee->telegram_chat_id,
                    'employee_code' => $employee->employee_code ?: $employee->username ?: 'No employee code',
                    'phone' => $employee->phone ?: 'N/A',
                    'branch_name' => $employee->branch?->name ?: 'N/A',
                    'department_name' => $employee->department?->dept_name ?: 'N/A',
                    'telegram_chat_id' => $employee->telegram_chat_id,
                    'telegram_username' => $username,
                    'telegram_linked_at_full' => $employee->telegram_linked_at ? optional($employee->telegram_linked_at)->format('Y-m-d H:i') : null,
                    'connect_url' => $connectUrl,
                    'connect_url_validity' => \App\Support\TelegramBotSettings::connectLinkValidityMinutes(),
                    'employee_code_for_link' => $employee->employee_code ?: $employee->username ?: 'EMPLOYEE_CODE',
                ];
            });

            return response()->json([
                'employees' => $employeeData,
                'has_more' => $employees->hasMorePages(),
                'next_page' => $employees->currentPage() + 1,
                'active_employee_id' => $activeEmployeeId,
            ]);
        }

        return view('admin.telegramEmployee.index', compact(
            'employees',
            'branches',
            'departments',
            'filters',
            'botSettings',
            'stats',
            'activeEmployeeId'
        ));
    }

    public function detail(Request $request, string $type): JsonResponse
    {
        $filters = [
            'search' => $request->input('search'),
            'branch_id' => $request->input('branch_id'),
            'department_id' => $request->input('department_id'),
        ];

        $employeeQuery = $this->buildEmployeeQuery($filters);

        if ($type === 'linked') {
            $employeeQuery->whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '');
        } elseif ($type === 'unlinked') {
            $employeeQuery->where(function ($query) {
                $query->whereNull('telegram_chat_id')->orWhere('telegram_chat_id', '');
            });
        }

        $employees = $employeeQuery
            ->orderByRaw('CASE WHEN users.employee_code IS NULL OR users.employee_code = "" THEN 1 ELSE 0 END')
            ->orderBy('employee_code')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function ($employee) {
                $avatar = $employee->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');
                $initial = mb_substr(trim($employee->name ?: $employee->username ?: 'U'), 0, 1);
                $hasChat = ! empty($employee->telegram_chat_id);

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'avatar' => $avatar,
                    'has_avatar' => (bool) $employee->avatar,
                    'initial' => $initial,
                    'has_chat' => $hasChat,
                    'employee_code' => $employee->employee_code ?: 'N/A',
                    'phone' => $employee->phone ?: 'N/A',
                    'branch' => $employee->branch?->name ?: 'N/A',
                    'department' => $employee->department?->dept_name ?: 'N/A',
                    'telegram_chat_id' => $employee->telegram_chat_id,
                    'telegram_username' => $employee->telegram_username ? '@' . $employee->telegram_username : null,
                    'linked_at' => $employee->telegram_linked_at ? optional($employee->telegram_linked_at)->format('Y-m-d H:i') : null,
                ];
            });

        $labels = [
            'all' => 'All Employees',
            'linked' => 'Linked Employees',
            'unlinked' => 'Not Linked Employees',
        ];

        return response()->json([
            'type' => $type,
            'label' => $labels[$type] ?? 'Employees',
            'employees' => $employees,
            'count' => $employees->count(),
        ]);
    }

    private function buildEmployeeQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return User::with(['branch:id,name', 'department:id,dept_name'])
            ->where('status', 'verified')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('english_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('telegram_chat_id', 'like', "%{$search}%")
                        ->orWhere('telegram_username', 'like', "%{$search}%");
                });
            })
            ->when($filters['branch_id'] ?? null, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['department_id'] ?? null, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when(($filters['linked'] ?? null) === 'yes', fn ($query) => $query->whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', ''))
            ->when(($filters['linked'] ?? null) === 'no', fn ($query) => $query->where(function ($query) {
                $query->whereNull('telegram_chat_id')->orWhere('telegram_chat_id', '');
            }));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $data = $request->validate([
            'telegram_chat_id' => ['nullable', 'string', 'max:255'],
            'telegram_username' => ['nullable', 'string', 'max:255'],
        ]);

        $data['telegram_chat_id'] = trim((string) ($data['telegram_chat_id'] ?? ''));
        $data['telegram_username'] = ltrim(trim((string) ($data['telegram_username'] ?? '')), '@');
        $data['telegram_linked_at'] = $data['telegram_chat_id'] !== '' ? ($employee->telegram_linked_at ?: now()) : null;

        $employee->update($data);

        return back()->with('success', 'Employee Telegram link updated.');
    }

    public function unlink(User $employee): RedirectResponse
    {
        $employee->update([
            'telegram_chat_id' => null,
            'telegram_username' => null,
            'telegram_linked_at' => null,
        ]);

        return back()->with('success', $employee->name . ' has been unlinked from Telegram.');
    }

    public function send(Request $request, User $employee, TelegramService $telegramService): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['nullable', 'required_without:attachment', 'string', 'max:4096'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $message = trim((string) ($data['message'] ?? ''));
        $chatId = trim((string) $employee->telegram_chat_id);

        if ($chatId === '') {
            return back()->with('danger', 'Telegram message failed. This employee has no Telegram chat ID.');
        }

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $path = (string) $attachment->getRealPath();
            $mimeType = (string) $attachment->getMimeType();

            $ok = str_starts_with($mimeType, 'image/')
                ? $telegramService->sendPhoto($chatId, $path, $message ?: null) !== null
                : $telegramService->sendDocument($chatId, $path, $attachment->getClientOriginalName(), $message ?: null);
        } else {
            $ok = $telegramService->sendToEmployee($employee, $message);
        }

        return back()->with($ok ? 'success' : 'danger', $ok
            ? 'Telegram message sent to employee.'
            : ($telegramService->lastError() ?: 'Telegram message failed. Check employee chat ID, bot token, and server logs.')
        );
    }

    public function broadcast(Request $request, TelegramService $telegramService): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4096'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $employees = User::query()
            ->where('status', 'verified')
            ->where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->when($data['branch_id'] ?? null, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($data['department_id'] ?? null, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->get();

        $result = $telegramService->sendToEmployees($employees, $data['message']);

        return back()->with('success', "Telegram broadcast complete. Sent: {$result['sent']}, Failed: {$result['failed']}, Skipped: {$result['skipped']}.");
    }

    public function syncStarts(TelegramService $telegramService): JsonResponse|RedirectResponse
    {
        $updates = $telegramService->getUpdates();

        if ((! is_array($updates) || ($updates['ok'] ?? false) !== true) && str_contains(strtolower((string) $telegramService->lastError()), 'webhook')) {
            $telegramService->deleteWebhook(false);
            $updates = $telegramService->getUpdates();
        }

        if (! is_array($updates) || ($updates['ok'] ?? false) !== true) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to sync Telegram starts. ' . ($telegramService->lastError() ?: 'Check bot token and Telegram webhook settings.'),
                ], 422);
            }

            return back()->with(
                'danger',
                'Unable to sync Telegram starts. ' . ($telegramService->lastError() ?: 'Check bot token and Telegram webhook settings.')
            );
        }

        $linked = 0;
        $ignored = 0;
        $lastUpdateId = null;

        foreach (($updates['result'] ?? []) as $update) {
            $lastUpdateId = max((int) ($lastUpdateId ?? 0), (int) ($update['update_id'] ?? 0));
            $message = (array) ($update['message'] ?? []);
            $text = trim((string) ($message['text'] ?? ''));
            $chatId = isset($message['chat']['id']) ? (string) $message['chat']['id'] : '';

            if ($text === '' || $chatId === '') {
                $ignored++;
                continue;
            }

            $employee = $this->employeeFromTelegramText($text);

            if (! $employee) {
                $ignored++;
                continue;
            }

            $employee->update([
                'telegram_chat_id' => $chatId,
                'telegram_username' => trim((string) data_get($message, 'from.username')) ?: $employee->telegram_username,
                'telegram_linked_at' => now(),
            ]);

            $telegramService->sendMessage($chatId, 'Telegram linked successfully for ' . $employee->name . '.');
            $linked++;
        }

        if ($lastUpdateId !== null) {
            $telegramService->getUpdates($lastUpdateId + 1, 1, 0);
        }

        if ($linked === 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'linked' => 0,
                    'ignored' => $ignored,
                    'message' => "No employee connect starts found. Scan the QR code, press Start in Telegram, then sync again. Ignored: {$ignored}.",
                ]);
            }

            return back()->with('warning', "No employee connect starts found. Scan the QR code, press Start in Telegram, then click Sync Telegram Starts again. Ignored: {$ignored}.");
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'linked' => $linked,
                'ignored' => $ignored,
                'message' => "Telegram employee sync complete. Linked: {$linked}, Ignored: {$ignored}.",
            ]);
        }

        return back()->with('success', "Telegram employee sync complete. Linked: {$linked}, Ignored: {$ignored}.");
    }

    private function employeeFromTelegramText(string $text): ?User
    {
        $parts = preg_split('/\s+/', trim($text), 2);
        $command = strtolower(strtok($parts[0] ?? '', '@') ?: '');
        $payload = trim((string) ($parts[1] ?? ''));

        if ($command === '/start' && $payload !== '') {
            return TelegramBotSettings::employeeFromConnectPayload($payload);
        }

        if ($command === '/link' && $payload !== '') {
            return User::query()
                ->where('status', 'verified')
                ->where(function ($query) use ($payload) {
                    $query->where('employee_code', $payload)
                        ->orWhere('username', $payload);
                })
                ->first();
        }

        if ($command !== '/start' && Str::startsWith($text, 'e')) {
            return TelegramBotSettings::employeeFromConnectPayload($text);
        }

        return null;
    }
}
