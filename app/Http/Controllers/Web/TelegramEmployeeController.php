<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TelegramEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'branch_id' => $request->input('branch_id'),
            'department_id' => $request->input('department_id'),
            'linked' => $request->input('linked'),
        ];

        $employees = User::with(['branch:id,name', 'department:id,dept_name'])
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
            }))
            ->orderByRaw('CASE WHEN users.employee_code IS NULL OR users.employee_code = "" THEN 1 ELSE 0 END')
            ->orderBy('employee_code')
            ->orderBy('name')
            ->paginate(25);

        $branches = Branch::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'dept_name')->orderBy('dept_name')->get();

        return view('admin.telegramEmployee.index', compact('employees', 'branches', 'departments', 'filters'));
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
            'message' => ['required', 'string', 'max:4096'],
        ]);

        $ok = $telegramService->sendToEmployee($employee, $data['message']);

        return back()->with($ok ? 'success' : 'danger', $ok
            ? 'Telegram message sent to employee.'
            : 'Telegram message failed. Check employee chat ID, bot token, and server logs.'
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
}
