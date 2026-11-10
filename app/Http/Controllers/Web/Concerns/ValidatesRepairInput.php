<?php

namespace App\Http\Controllers\Web\Concerns;

use Illuminate\Http\Request;

trait ValidatesRepairInput
{
    protected function validateRepairInput(Request $request, array $rules, array $messages = []): array
    {
        return $request->validate($rules, $messages, __('repair.attributes'));
    }
}
