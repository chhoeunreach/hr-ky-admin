<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Services\TelegramService;
use App\Support\TelegramBotSettings;
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

        $employeeQuery = User::with(['branch:id,name', 'department:id,dept_name'])
            ->where('status', 'verified')
            ->when($filters['search'], function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('english_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('telegram_chat_id', 'like', "%{$search}%")
                        ->orWhere('telegram_username', 'like', "%{$search}%");
                });
            })
            ->when($filters['branch_id'], fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['department_id'], fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($filters['linked'] === 'yes', fn ($query) => $query->whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', ''))
            ->when($filters['linked'] === 'no', fn ($query) => $query->where(function ($query) {
                $query->whereNull('telegram_chat_id')->orWhere('telegram_chat_id', '');
            }));

        $statsQuery = clone $employeeQuery;

        $employees = $employeeQuery
            ->orderByRaw('CASE WHEN users.employee_code IS NULL OR users.employee_code = "" THEN 1 ELSE 0 END')
            ->orderBy('employee_code')
            ->orderBy('name')
            ->paginate(25);

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

    public function syncStarts(TelegramService $telegramService): RedirectResponse
    {
        $updates = $telegramService->getUpdates();

        if ((! is_array($updates) || ($updates['ok'] ?? false) !== true) && str_contains(strtolower((string) $telegramService->lastError()), 'webhook')) {
            $telegramService->deleteWebhook(false);
            $updates = $telegramService->getUpdates();
        }

        if (! is_array($updates) || ($updates['ok'] ?? false) !== true) {
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
            return back()->with('warning', "No employee connect starts found. Scan the QR code, press Start in Telegram, then click Sync Telegram Starts again. Ignored: {$ignored}.");
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
