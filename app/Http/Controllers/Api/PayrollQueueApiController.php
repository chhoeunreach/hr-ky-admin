<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollQueueTicket;
use App\Services\Payroll\PayrollQueueService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollQueueApiController extends Controller
{
    public function __construct(private PayrollQueueService $queueService) {}

    /** Join the payroll session encoded in the admin-created QR, as the signed-in employee. */
    public function join(Request $request): JsonResponse
    {
        $data = $request->validate(['qr_token' => ['required', 'string', 'max:100']]);
        $employee = $request->user('api') ?: $request->user();
        abort_unless($employee, 401);

        try {
            $result = $this->queueService->joinQueue($data['qr_token'], (int) $employee->id);
        } catch (DomainException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
        $ticket = $result['ticket'];

        return response()->json([
            'success' => true,
            'status' => $result['status'],
            'message' => $result['message'],
            'ticket' => $ticket,
            'people_ahead' => $ticket?->peopleAhead() ?? 0,
        ], $result['status'] === 'success' ? 201 : 200);
    }

    /** Poll only the current employee's own ticket. */
    public function ticket(Request $request, int $ticketId): JsonResponse
    {
        $employee = $request->user('api') ?: $request->user();
        abort_unless($employee, 401);
        $ticket = PayrollQueueTicket::with('session:id,month,title,status')
            ->whereKey($ticketId)->where('employee_id', $employee->id)->firstOrFail();
        return response()->json(['success' => true, 'ticket' => $ticket, 'people_ahead' => $ticket->peopleAhead()]);
    }
}
