<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class ChatMediaController extends Controller
{
    private function mediaSuccessResponse(string $message, array $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    private function mediaErrorResponse(string $message, int $statusCode = 422, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'status' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $response['data'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    public function upload(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => ['required', 'string', Rule::in(['image', 'audio', 'voice', 'document', 'file'])],
                'file' => ['required', 'file'],
            ]);

            $validator->sometimes('file', [function (string $attribute, mixed $value, \Closure $fail) {
                if (!$value instanceof UploadedFile) {
                    $fail('The file must be a valid image upload.');
                    return;
                }

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $extension = strtolower($value->getClientOriginalExtension());
                $mimeType = strtolower((string) $value->getMimeType());

                if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
                    $fail('The file must be a file of type: jpg, jpeg, png, webp.');
                }
            }, 'max:10240'], function ($input) {
                return $input->type === 'image';
            });

            $validator->sometimes('file', [function (string $attribute, mixed $value, \Closure $fail) {
                if (!$value instanceof UploadedFile) {
                    $fail('The file must be a valid audio upload.');
                    return;
                }

                $allowedExtensions = ['m4a', 'mp3', 'wav', 'aac', 'webm', 'ogg', 'oga'];
                $allowedMimeTypes = [
                    'audio/mp4',
                    'audio/x-m4a',
                    'audio/m4a',
                    'audio/mpeg',
                    'audio/mp3',
                    'audio/wav',
                    'audio/x-wav',
                    'audio/aac',
                    'audio/aacp',
                    'audio/webm',
                    'video/webm',
                    'audio/ogg',
                    'application/ogg',
                    'application/octet-stream',
                ];
                $extension = strtolower($value->getClientOriginalExtension());
                $mimeType = strtolower((string) $value->getMimeType());

                if (!in_array($extension, $allowedExtensions, true) && !in_array($mimeType, $allowedMimeTypes, true)) {
                    $fail('The file must be a file of type: m4a, mp3, wav, aac, webm, ogg.');
                }
            }, 'max:20480'], function ($input) {
                return in_array($input->type, ['audio', 'voice'], true);
            });

            $validator->sometimes('file', [function (string $attribute, mixed $value, \Closure $fail) {
                if (!$value instanceof UploadedFile) {
                    $fail('The file must be a valid document upload.');
                    return;
                }

                if ($value->getSize() > (20 * 1024 * 1024)) {
                    $fail('The file may not be greater than 20480 kilobytes.');
                }
            }], function ($input) {
                return in_array($input->type, ['document', 'file'], true);
            });

            if ($validator->fails()) {
                return $this->mediaErrorResponse(
                    $validator->errors()->first(),
                    422,
                    $validator->errors()->toArray()
                );
            }

            $type = $request->input('type');
            $file = $request->file('file');
            $directory = match ($type) {
                'image' => 'chat/images',
                'audio', 'voice' => 'chat/voice',
                default => 'chat/files',
            };
            $responseType = match ($type) {
                'audio', 'voice' => 'audio',
                'document', 'file' => 'document',
                default => 'image',
            };

            if ($type === 'image') {
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = uniqid('chat_', true) . '.' . $extension;
                $path = $directory . '/' . $fileName;

                $image = Image::make($file->getRealPath())
                    ->orientate()
                    ->resize(1600, 1600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                Storage::disk('public')->put($path, (string) $image->encode($extension, 82));
                $width = $image->width();
                $height = $image->height();
            } else {
                $path = $file->store($directory, 'public');
                $width = null;
                $height = null;
            }

            return $this->mediaSuccessResponse('Media uploaded successfully', [
                'url' => ChatMessage::normalizeMediaUrl($path),
                'type' => $responseType,
                'path' => ChatMessage::normalizeMediaPath($path),
                'width' => $width,
                'height' => $height,
            ]);
        } catch (\Exception $exception) {
            return $this->mediaErrorResponse($exception->getMessage(), 400);
        }
    }
}
