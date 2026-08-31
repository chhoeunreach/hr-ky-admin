<?php

namespace App\Http\Controllers\Web;

use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;
use Intervention\Image\Facades\Image;

class EmployeeChatController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view_employee_chat');
        $admin = auth('admin')->user();

        $filters = $this->staffFilters($request);
        $staffList = $this->getStaffList($filters);
        $selectedStaff = $this->resolveSelectedStaff($staffList, $request->integer('employee_id'));
        $conversation = $selectedStaff ? $this->getOrCreateConversation($selectedStaff->id, $admin->id) : null;
        $messages = collect();

        if ($conversation) {
            $messages = $this->loadThreadMessages($conversation, $admin->id);
            $this->markMessagesAsReadByAdmin($messages);
        }

        $branches = \App\Models\Branch::select('id', 'name')->orderBy('name')->get();
        $departments = \App\Models\Department::select('id', 'dept_name')->orderBy('dept_name')->get();
        $botSettings = \App\Support\TelegramBotSettings::all();

        return view('admin.employee-chat', compact(
            'staffList',
            'selectedStaff',
            'conversation',
            'messages',
            'filters',
            'branches',
            'departments',
            'botSettings'
        ));
    }

    public function staff(Request $request): JsonResponse
    {
        $this->authorize('view_employee_chat');

        $filters = $this->staffFilters($request);
        $staffList = $this->getStaffList($filters);
        $selectedStaff = $staffList->firstWhere('id', $request->integer('employee_id'));

        return response()->json([
            'success' => true,
            'html' => view('admin.chat.partials.staff', compact('staffList', 'selectedStaff'))->render(),
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $this->authorize('view_employee_chat');

        $staffList = $this->getStaffList();
        $selectedStaff = $this->resolveSelectedStaff($staffList, $request->integer('employee_id'));
        $admin = auth('admin')->user();

        if (!$selectedStaff) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        $conversation = $this->getOrCreateConversation($selectedStaff->id, $admin->id);
        $messages = $this->loadThreadMessages($conversation, $admin->id);
        $this->markMessagesAsReadByAdmin($messages);

        return response()->json([
            'success' => true,
            'html' => view('admin.chat.partials.messages', compact('messages'))->render(),
        ]);
    }

    public function store(Request $request, TelegramService $telegramService): JsonResponse
    {
        $this->authorize('send_employee_chat');

        $validator = Validator::make($request->all(), [
            'employee_id' => ['required', Rule::exists('users', 'id')],
            'message' => ['nullable', 'string'],
            'message_type' => ['nullable', 'string', Rule::in([
                ChatMessage::TYPE_TEXT,
                ChatMessage::TYPE_IMAGE,
                ChatMessage::TYPE_VOICE,
                ChatMessage::TYPE_LOCATION,
            ])],
            'media_url' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'map_url' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $hasAttachment = $request->hasFile('attachment');
            $hasMessage = trim((string) $request->input('message')) !== '';
            $hasLocation = $request->filled('latitude') && $request->filled('longitude');

            if (!$hasAttachment && !$hasMessage && !$hasLocation) {
                $validator->errors()->add('message', 'Please enter a message, upload a file, or send a location.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $employee = User::query()
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->findOrFail((int) $request->input('employee_id'));

        $admin = auth('admin')->user();
        $conversation = $this->getOrCreateConversation($employee->id, $admin->id);

        $message = DB::transaction(function () use ($request, $conversation, $admin, $employee) {
            $messageType = $request->input('message_type', ChatMessage::TYPE_TEXT);
            $mediaUrl = $request->input('media_url');
            $storedMediaPath = null;
            $storedFileName = null;

            if ($request->hasFile('attachment')) {
                [$messageType, $mediaUrl, $storedMediaPath, $storedFileName] = $this->storeAttachment($request->file('attachment'));
            } elseif ($request->filled('latitude') && $request->filled('longitude')) {
                $messageType = ChatMessage::TYPE_LOCATION;
            }

            $mapUrl = $request->input('map_url');
            if ($messageType === ChatMessage::TYPE_LOCATION && $mapUrl === null) {
                $mapUrl = 'https://www.google.com/maps?q=' . $request->input('latitude') . ',' . $request->input('longitude');
            }

            $externalConversationId = $this->externalConversationId($employee->id, $admin->id);
            $message = $conversation->messages()->create([
                'sender_type' => ChatMessage::SENDER_ADMIN,
                'sender_id' => $admin->id,
                'message_type' => $messageType,
                'message' => $request->input('message'),
                'media_url' => $mediaUrl,
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'map_url' => $mapUrl,
                'meta' => [
                    'admin_id' => $admin->id,
                    'admin_username' => $admin->username,
                    'external_conversation_id' => $externalConversationId,
                    'media_path' => $storedMediaPath,
                    'file_name' => $storedFileName,
                ],
                'is_read_by_admin' => true,
                'is_read_by_user' => false,
            ]);

            $conversation->update([
                'last_message_at' => $message->created_at,
            ]);

            $notificationBody = match ($messageType) {
                ChatMessage::TYPE_IMAGE => 'Sent a photo',
                ChatMessage::TYPE_VOICE => 'Sent a voice message',
                ChatMessage::TYPE_LOCATION => 'Sent a location',
                default => (string) ($message->message ?: 'New message'),
            };

            SMPushHelper::sendPushNotification(
                $admin->name,
                $externalConversationId,
                $notificationBody,
                'chat',
                [$employee->username],
                '',
                $messageType,
                $mediaUrl ?? '',
                $message->latitude,
                $message->longitude,
                $message->map_url ?? '',
                $admin->id,
                $admin->username,
                'admin_thread',
                (string) $conversation->id
            );

            return $message;
        });

        $telegramResult = $this->mirrorMessageToTelegram($message, $employee, $telegramService);

        $messages = $this->loadThreadMessages($conversation, $admin->id);

        return response()->json([
            'success' => true,
            'html' => view('admin.chat.partials.messages', compact('messages'))->render(),
            'telegram_status' => $telegramResult['status'],
            'telegram_message' => $telegramResult['message'],
        ]);
    }

    private function staffFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search')),
            'branch_id' => $request->input('branch_id'),
            'department_id' => $request->input('department_id'),
            'linked' => $request->input('linked'),
        ];
    }

    private function getStaffList(array $filters = [])
    {
        $adminId = auth('admin')->id();
        $supportsPerAdminConversation = $this->supportsPerAdminConversation();

        $query = User::query()
            ->select(['id', 'name', 'username', 'avatar', 'phone', 'department_id', 'branch_id', 'online_status', 'telegram_chat_id', 'telegram_username', 'telegram_linked_at'])
            ->with([
                'department:id,dept_name',
                'branch:id,name',
                'chatConversations' => function ($query) use ($adminId, $supportsPerAdminConversation) {
                    if ($supportsPerAdminConversation) {
                        $query->where('admin_id', $adminId);
                    } else {
                        $query->whereNull('admin_id');
                    }
                    $query
                        ->with('latestMessage');
                },
            ])
            ->where('status', 'verified')
            ->where('is_active', 1);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('english_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('telegram_chat_id', 'like', "%{$search}%")
                    ->orWhere('telegram_username', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (($filters['linked'] ?? '') === 'yes') {
            $query->whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '');
        } elseif (($filters['linked'] ?? '') === 'no') {
            $query->where(function ($query) {
                $query->whereNull('telegram_chat_id')->orWhere('telegram_chat_id', '');
            });
        }

        return $query->orderBy('name')->get();
    }

    private function resolveSelectedStaff($staffList, ?int $employeeId): ?User
    {
        if ($staffList->isEmpty()) {
            return null;
        }

        return $staffList->firstWhere('id', $employeeId) ?? $staffList->first();
    }

    private function getOrCreateConversation(int $employeeId, int $adminId): ChatConversation
    {
        if (!$this->supportsPerAdminConversation()) {
            return ChatConversation::firstOrCreate([
                'user_id' => $employeeId,
            ]);
        }
        try {
            return ChatConversation::firstOrCreate([
                'user_id' => $employeeId,
                'admin_id' => $adminId,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return ChatConversation::firstOrCreate([
                'user_id' => $employeeId,
            ]);
        }
    }

    private function supportsPerAdminConversation(): bool
    {
        static $supportsPerAdminConversation = null;

        if ($supportsPerAdminConversation !== null) {
            return $supportsPerAdminConversation;
        }

        $supportsPerAdminConversation = Schema::hasColumn('chat_conversations', 'admin_id');

        return $supportsPerAdminConversation;
    }

    private function externalConversationId(int $employeeId, int $adminId): string
    {
        return 'employee_admin_' . $employeeId . '_' . $adminId;
    }

    private function markMessagesAsReadByAdmin($messages): void
    {
        $messageIds = collect($messages)
            ->filter(fn (ChatMessage $message) => $message->sender_type === ChatMessage::SENDER_USER && !$message->is_read_by_admin)
            ->pluck('id')
            ->all();

        if ($messageIds === []) {
            return;
        }

        ChatMessage::query()
            ->whereIn('id', $messageIds)
            ->update(['is_read_by_admin' => true]);
    }

    private function storeAttachment(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());
        $originalName = $file->getClientOriginalName();

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $imageMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (in_array($extension, $imageExtensions, true) || in_array($mimeType, $imageMimeTypes, true)) {
            $directory = 'chat/images';
            $fileName = uniqid('chat_', true) . '.' . $extension;
            $path = $directory . '/' . $fileName;

            $image = Image::make($file->getRealPath())
                ->orientate()
                ->resize(1600, 1600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

            Storage::disk('public')->put($path, (string) $image->encode($extension, 82));

            return [ChatMessage::TYPE_IMAGE, Storage::disk('public')->url($path), $path, $originalName];
        }

        if (str_starts_with($mimeType, 'audio/')) {
            $path = $file->store('chat/voice', 'public');

            return [ChatMessage::TYPE_VOICE, Storage::disk('public')->url($path), $path, $originalName];
        }

        $path = $file->store('chat/files', 'public');

        return [ChatMessage::TYPE_FILE, Storage::disk('public')->url($path), $path, $originalName];
    }

    private function mirrorMessageToTelegram(ChatMessage $message, User $employee, TelegramService $telegramService): array
    {
        $chatId = trim((string) $employee->telegram_chat_id);

        if ($chatId === '') {
            $this->markTelegramStatus($message, 'skipped', null);

            return [
                'status' => 'skipped',
                'message' => 'System chat sent. Telegram skipped because this employee is not connected.',
            ];
        }

        $ok = match ($message->message_type) {
            ChatMessage::TYPE_IMAGE => $this->sendTelegramImage($message, $telegramService, $chatId),
            ChatMessage::TYPE_FILE, ChatMessage::TYPE_VOICE => $this->sendTelegramDocument($message, $telegramService, $chatId),
            ChatMessage::TYPE_LOCATION => $this->sendTelegramLocation($message, $telegramService, $chatId),
            default => $telegramService->sendMessage($chatId, (string) ($message->message ?: 'New HR chat message')),
        };

        $error = $ok ? null : $telegramService->lastError();
        $this->markTelegramStatus($message, $ok ? 'sent' : 'failed', $error);

        return [
            'status' => $ok ? 'sent' : 'failed',
            'message' => $ok
                ? 'System chat sent and Telegram delivered.'
                : 'System chat sent, but Telegram failed. ' . ($error ?: 'Check employee chat ID and bot token.'),
        ];
    }

    private function sendTelegramImage(ChatMessage $message, TelegramService $telegramService, string $chatId): bool
    {
        $mediaPath = $message->resolvedMediaPath();

        if ($mediaPath === null) {
            return $telegramService->sendMessage($chatId, (string) ($message->message ?: 'Sent a photo'));
        }

        return $telegramService->sendPhoto(
            $chatId,
            Storage::disk('public')->path($mediaPath),
            $message->message ?: null
        ) !== null;
    }

    private function sendTelegramDocument(ChatMessage $message, TelegramService $telegramService, string $chatId): bool
    {
        $mediaPath = $message->resolvedMediaPath();

        if ($mediaPath === null) {
            return $telegramService->sendMessage($chatId, (string) ($message->message ?: 'Sent a file'));
        }

        return $telegramService->sendDocument(
            $chatId,
            Storage::disk('public')->path($mediaPath),
            $message->meta['file_name'] ?? basename($mediaPath),
            $message->message ?: null
        );
    }

    private function sendTelegramLocation(ChatMessage $message, TelegramService $telegramService, string $chatId): bool
    {
        if ($message->latitude === null || $message->longitude === null) {
            return $telegramService->sendMessage($chatId, (string) ($message->message ?: $message->map_url ?: 'Sent a location'));
        }

        $locationSent = $telegramService->sendLocation($chatId, (float) $message->latitude, (float) $message->longitude);

        if ($locationSent && trim((string) $message->message) !== '') {
            $telegramService->sendMessage($chatId, (string) $message->message);
        }

        return $locationSent;
    }

    private function markTelegramStatus(ChatMessage $message, string $status, ?string $error): void
    {
        $meta = $message->meta ?? [];
        $meta['telegram_status'] = $status;
        $meta['telegram_error'] = $error;
        $meta['telegram_checked_at'] = now()->toDateTimeString();

        $message->update(['meta' => $meta]);
        $message->meta = $meta;
    }

    private function loadThreadMessages(ChatConversation $conversation, int $adminId)
    {
        $externalConversationId = $this->externalConversationId($conversation->user_id, $adminId);

        return $conversation->messages()
            ->with(['adminSender:id,name,avatar,username', 'userSender:id,name,avatar'])
            ->orderBy('created_at')
            ->get()
            ->filter(function (ChatMessage $message) use ($adminId, $externalConversationId) {
                return $this->messageBelongsToThread($message, $adminId, $externalConversationId);
            })
            ->values();
    }

    private function messageBelongsToThread(ChatMessage $message, int $adminId, string $externalConversationId): bool
    {
        if ((int) $message->conversation?->admin_id === $adminId) {
            return true;
        }

        if (($message->meta['external_conversation_id'] ?? null) === $externalConversationId) {
            return true;
        }

        if ((int) ($message->meta['admin_id'] ?? 0) === $adminId) {
            return true;
        }

        return $message->sender_type === ChatMessage::SENDER_ADMIN && (int) $message->sender_id === $adminId;
    }
}
