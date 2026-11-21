<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\SystemDiagnostics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SystemDiagnosticsController extends Controller
{
    public function __invoke(Request $request, SystemDiagnostics $diagnostics): Response
    {
        $level = $request->validate(['level' => ['nullable', 'in:error,warning']])['level'] ?? null;

        return response()->view('admin.maintenance.diagnostics', [
            'checks' => $diagnostics->checks(),
            'logs' => $diagnostics->recentLogs($level),
            'level' => $level,
        ])->header('Cache-Control', 'private, no-store');
    }
}
