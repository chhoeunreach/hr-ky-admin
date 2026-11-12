<?php

namespace App\Http\Middleware;

use App\Services\Authorization\MobilePermissions;
use Closure;
use Illuminate\Http\Request;

class MobileFunctionPermission
{
    public function handle(Request $request, Closure $next): mixed
    {
        $action = class_basename($request->route()->getController()).'@'.$request->route()->getActionMethod();
        $rules = config('mobile_permissions.functions', []);
        // New mobile endpoints must explicitly declare a permission or infrastructure exemption.
        if (!array_key_exists($action, $rules)) {
            return response()->json(['message' => 'Mobile function permission is not configured.'], 403);
        }
        $rule = $rules[$action];
        if ($rule !== null && !app(MobilePermissions::class)->allowsFunction($rule)) {
            return response()->json(['message' => 'You do not have permission to use this function.'], 403);
        }
        return $next($request);
    }
}
