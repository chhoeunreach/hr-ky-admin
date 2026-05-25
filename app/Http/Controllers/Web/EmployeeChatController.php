<?php

namespace App\Http\Controllers\Web;

use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
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

        $staffList = $this->getStaffList();
        $selectedStaff = $this->resolveSelectedStaff($staffList, $request->integer('employee_id'));
        $conversation = $selectedStaff ? $this->getOrCreateConversation($selectedStaff->id, $admin->id) : null;
        $messages = collect();

        if ($conversation) {
            $messages = $this->loadThreadMessages($conversation, $admin->id);
            $this->markMessagesAsReadByAdmin($messages);
        }

        return view('admin.employee-chat', compact(
            'staffList',
            'selectedStaff',
            'conversation',
            'messages'
        ));
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

    public function store(Request $request): JsonResponse
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

        DB::transaction(function () use ($request, $conversation, $admin, $employee) {
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
        });

        $messages = $this->loadThreadMessages($conversation, $admin->id);

        return response()->json([
            'success' => true,
            'html' => view('admin.chat.partials.messages', compact('messages'))->render(),
        ]);
    }

    private function getStaffList()
    {
        $adminId = auth('admin')->id();
        $supportsPerAdminConversation = $this->supportsPerAdminConversation();

        return User::query()
            ->select(['id', 'name', 'username', 'avatar', 'phone', 'department_id', 'branch_id', 'online_status'])
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
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
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

        $path = $file->store('chat/voice', 'public');

        return [ChatMessage::TYPE_VOICE, Storage::disk('public')->url($path), $path, $originalName];
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
