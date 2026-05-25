<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChatMediaController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => ['required', 'string', Rule::in(['image', 'voice'])],
                'file' => ['required', 'file'],
            ]);

            $validator->sometimes('file', ['mimes:jpg,jpeg,png,webp', 'max:10240'], function ($input) {
                return $input->type === 'image';
            });

            $validator->sometimes('file', ['mimes:m4a,mp3,wav,aac,webm', 'max:20480'], function ($input) {
                return $input->type === 'voice';
            });

            if ($validator->fails()) {
                return AppHelper::sendErrorResponse(
                    $validator->errors()->first(),
                    422,
                    $validator->errors()->toArray()
                );
            }

            $type = $request->input('type');
            $directory = $type === 'image' ? 'chat/images' : 'chat/voices';
            $path = $request->file('file')->store($directory, 'public');

            return AppHelper::sendSuccessResponse('Uploaded successfully', [
                'url' => Storage::disk('public')->url($path),
                'type' => $type,
                'path' => $path,
            ]);
        } catch (\Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }
}
