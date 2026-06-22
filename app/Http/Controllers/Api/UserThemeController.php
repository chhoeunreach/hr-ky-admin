<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Requests\User\Api\UserThemeRequest;
use Illuminate\Http\JsonResponse;

class UserThemeController extends Controller
{
    public function show(): JsonResponse
    {
        $themeMode = auth()->user()?->app_theme_mode ?: User::DEFAULT_THEME_MODE;

        return AppHelper::sendSuccessResponse(__('index.data_found'), [
            'theme_mode' => $themeMode,
        ]);
    }

    public function update(UserThemeRequest $request): JsonResponse
    {
        $user = auth()->user();
        $themeMode = $request->validated()['theme_mode'];

        $user->app_theme_mode = $themeMode;
        $user->save();

        return AppHelper::sendSuccessResponse('Theme updated successfully', [
            'theme_mode' => $themeMode,
        ]);
    }
}
