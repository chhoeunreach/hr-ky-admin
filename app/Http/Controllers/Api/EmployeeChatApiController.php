<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\MobileChatHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Intervention\Image\Facades\Image;

class EmployeeChatApiController extends Controller
{
    public function access(): JsonResponse
    {
        try {
            $user = auth()->user();
            $scope = MobileChatHelper::getScope();
            $supportsPerAdminConversation = $this->supportsPerAdminConversation();
            $adminList = Admin::query()
                ->where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'username', 'avatar']);
            $primaryAdmin = $adminList->first();
            $conversation = $primaryAdmin
                ? $this->getOrCreateAdminConversation($user->id, $primaryAdmin->id)
                : null;
            $adminContact = $this->buildAdminContact($conversation, $primaryAdmin);

            return AppHelper::sendSuccessResponse('Mobile chat access loaded successfully.', [
                'scope' => $scope,
                'scope_label' => MobileChatHelper::scopeOptions()[$scope] ?? $scope,
                'employee_directory_enabled' => $scope === MobileChatHelper::MODE_ALL_EMPLOYEES,
                'admin_chat_enabled' => true,
                'per_admin_conversation_enabled' => $supportsPerAdminConversation,
                'admin_contact' => $adminContact,
                'admins' => $adminList->map(fn (Admin $admin) => $this->transformAdminDirectoryEntry(
                    $admin,
                    $this->getOrCreateAdminConversation($user->id, $admin->id)
                ))->values(),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return AppHelper::sendErrorResponse('Unable to load admin chat access right now.', 500);
        }
    }

    public function contacts(): JsonResponse
    {
        try {
            $scope = MobileChatHelper::getScope();
            $authUserId = auth()->id();
            $primaryAdmin = Admin::query()
                ->where('is_active', 1)
                ->orderBy('name')
                ->first(['id', 'name', 'username', 'avatar']);
            $conversation = $primaryAdmin
                ? $this->getOrCreateAdminConversation($authUserId, $primaryAdmin->id)
                : null;
            $adminContact = $this->buildAdminContact(
                $conversation,
                $primaryAdmin
            );

            if ($scope !== MobileChatHelper::MODE_ALL_EMPLOYEES) {
                return AppHelper::sendSuccessResponse('Mobile chat contacts loaded successfully.', [
                    'scope' => $scope,
                    'admin_contact' => $adminContact,
                    'pinned_contacts' => [$adminContact],
                    'contacts' => $this->getAdminDirectoryEntries($authUserId),
                ]);
            }

            $authUser = auth()->user();
            $employeeContacts = User::query()
                ->select(['id', 'name', 'username', 'email', 'avatar', 'phone', 'department_id', 'branch_id', 'post_id', 'role_id', 'user_type', 'online_status'])
                ->with(['department:id,dept_name', 'branch:id,name', 'post:id,post_name', 'role:id,name,slug'])
                ->where('status', 'verified')
                ->where('is_active', 1)
                ->where('id', '!=', $authUser->id)
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => $this->transformEmployeeDirectoryEntry($user))
                ->values();

            $contacts = $employeeContacts
                ->concat($this->getAdminDirectoryEntries($authUserId))
                ->values();

            return AppHelper::sendSuccessResponse('Mobile chat contacts loaded successfully.', [
                'scope' => $scope,
                'per_admin_conversation_enabled' => $this->supportsPerAdminConversation(),
                'admin_contact' => $adminContact,
                'pinned_contacts' => [$adminContact],
                'contacts' => $contacts,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return AppHelper::sendErrorResponse('Unable to load chat contacts right now.', 500);
        }
    }

    public function messages(): JsonResponse
    {
        $request = request();

        try {
            $conversation = $this->resolveAdminConversation(auth()->id(), $request);
            $adminId = $this->threadAdminId($conversation, $request);
            $messages = $this->loadConversationThreadMessages($conversation, $adminId);
            $this->markThreadMessagesAsReadByUser($messages);
            $adminUsername = $this->threadAdminUsername($conversation, $adminId, $request);

            return AppHelper::sendSuccessResponse('Mobile admin chat messages loaded successfully.', [
                'conversation_id' => $this->threadConversationIdentifier($conversation, $adminId),
                'internal_conversation_id' => (string) $conversation->id,
                'admin_id' => $adminId,
                'admin_username' => $adminUsername,
                'messages' => $messages->map(fn (ChatMessage $message) => $this->transformMessage($message, $adminId, $adminUsername))->values(),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

                return AppHelper::sendSuccessResponse('Mobile admin chat loaded with fallback state.', [
                    'conversation_id' => $this->requestedConversationIdentifier(auth()->id(), $request),
                    'internal_conversation_id' => null,
                    'admin_id' => $this->requestedAdminId($request),
                    'admin_username' => $this->requestedAdminUsername($request),
                    'messages' => [],
                    'fallback' => true,
                ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->hasFile('attachment')
            && trim((string) $request->input('message')) === ''
            && !($request->filled('latitude') && $request->filled('longitude'))) {
            try {
                $conversation = $this->resolveAdminConversation(auth()->id(), $request);
                $adminId = $this->threadAdminId($conversation, $request);
                $adminUsername = $this->threadAdminUsername($conversation, $adminId, $request);
                $messages = $this->loadConversationThreadMessages($conversation, $adminId);

                return AppHelper::sendSuccessResponse('No new message was sent.', [
                    'conversation_id' => $this->threadConversationIdentifier($conversation, $adminId),
                    'internal_conversation_id' => (string) $conversation->id,
                    'admin_id' => $adminId,
                    'admin_username' => $adminUsername,
                    'messages' => $messages->map(fn (ChatMessage $message) => $this->transformMessage($message, $adminId, $adminUsername))->values(),
                    'noop' => true,
                ]);
            } catch (Throwable $throwable) {
                report($throwable);

                return AppHelper::sendSuccessResponse('No new message was sent.', [
                    'conversation_id' => $this->requestedConversationIdentifier(auth()->id(), $request),
                    'internal_conversation_id' => null,
                    'admin_id' => $this->requestedAdminId($request),
                    'admin_username' => $this->requestedAdminUsername($request),
                    'messages' => [],
                    'noop' => true,
                    'fallback' => true,
                ]);
            }
        }

        $validator = Validator::make($request->all(), [
            'message' => ['nullable', 'string'],
            'message_type' => ['nullable', 'string', 'in:text,image,voice,file,location'],
            'chat_message_type' => ['nullable', 'string', 'in:text,image,voice,file,location'],
            'type' => ['nullable', 'string'],
            'media_type' => ['nullable', 'string', 'in:image,audio,voice,document,file'],
            'conversation_id' => ['nullable', 'string'],
            'internal_conversation_id' => ['nullable', 'string'],
            'admin_id' => ['nullable', 'integer'],
            'admin_username' => ['nullable', 'string'],
            'media_url' => ['nullable', 'string'],
            'media_path' => ['nullable', 'string'],
            'media_width' => ['nullable', 'numeric'],
            'media_height' => ['nullable', 'numeric'],
            'duration_seconds' => ['nullable', 'numeric'],
            'file_name' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'map_url' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        if ($validator->fails()) {
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
            $conversation = $this->resolveAdminConversation(auth()->id(), $request);
            $adminId = $this->threadAdminId($conversation, $request);
            $adminUsername = $this->threadAdminUsername($conversation, $adminId, $request);

            DB::transaction(function () use ($request, $conversation, $adminId, $adminUsername) {
                $messageType = $this->normalizeIncomingMessageType($request);
                $mediaUrl = $request->input('media_url');

                if ($request->hasFile('attachment')) {
                    [$messageType, $mediaUrl] = $this->storeAttachment($request->file('attachment'));
                } elseif ($request->filled('latitude') && $request->filled('longitude')) {
                    $messageType = ChatMessage::TYPE_LOCATION;
                }

                $meta = array_filter([
                    'media_path' => $request->input('media_path'),
                    'media_width' => $request->input('media_width'),
                    'media_height' => $request->input('media_height'),
                    'duration_seconds' => $request->input('duration_seconds'),
                    'file_name' => $request->input('file_name'),
                    'admin_id' => $adminId,
                    'admin_username' => $adminUsername,
                    'external_conversation_id' => $this->threadConversationIdentifier($conversation, $adminId),
                ], fn ($value) => $value !== null && $value !== '');

                $mapUrl = $request->input('map_url');
                if ($messageType === ChatMessage::TYPE_LOCATION && $mapUrl === null) {
                    $mapUrl = 'https://www.google.com/maps?q=' . $request->input('latitude') . ',' . $request->input('longitude');
                }

                $message = $conversation->messages()->create([
                    'sender_type' => ChatMessage::SENDER_USER,
                    'sender_id' => auth()->id(),
                    'message_type' => $messageType,
                    'message' => $request->input('message'),
                    'media_url' => $mediaUrl,
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'map_url' => $mapUrl,
                    'meta' => $meta === [] ? null : $meta,
                    'is_read_by_admin' => false,
                    'is_read_by_user' => true,
                ]);

                $conversation->update([
                    'last_message_at' => $message->created_at,
                ]);
            });

            $messages = $this->loadConversationThreadMessages($conversation, $adminId);

            return AppHelper::sendSuccessResponse('Message sent successfully.', [
                'conversation_id' => $this->threadConversationIdentifier($conversation, $adminId),
                'internal_conversation_id' => (string) $conversation->id,
                'admin_id' => $adminId,
                'admin_username' => $adminUsername,
                'messages' => $messages->map(fn (ChatMessage $message) => $this->transformMessage($message, $adminId, $adminUsername))->values(),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return AppHelper::sendErrorResponse('Unable to send this message right now.', 500);
        }
    }

    private function transformMessage(ChatMessage $message, ?int $threadAdminId = null, ?string $threadAdminUsername = null): array
    {
        $conversation = $message->conversation;
        $normalizedType = $this->normalizeStoredMessageType($message->message_type, $message->meta ?? []);
        $resolvedAdminId = $threadAdminId
            ?? $conversation?->admin_id
            ?? ($message->meta['admin_id'] ?? null)
            ?? ($message->sender_type === ChatMessage::SENDER_ADMIN ? $message->sender_id : null);
        $resolvedAdminUsername = $threadAdminUsername
            ?? $conversation?->admin?->username
            ?? ($message->meta['admin_username'] ?? null)
            ?? $message->adminSender?->username;

        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender' => $message->sender_type,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->senderName(),
            'sender_avatar' => $message->senderAvatar(),
            'conversation_id' => $resolvedAdminId
                ? $this->externalConversationId($conversation?->user_id ?? auth()->id(), (int) $resolvedAdminId)
                : ($conversation ? $this->conversationIdentifier($conversation) : (string) $message->conversation_id),
            'internal_conversation_id' => (string) $message->conversation_id,
            'admin_id' => $resolvedAdminId ? (int) $resolvedAdminId : null,
            'admin_username' => $resolvedAdminUsername,
            'message_type' => $normalizedType,
            'type' => $normalizedType,
            'message' => $message->message,
            'body' => $message->message,
            'media_url' => $message->media_url,
            'media_path' => $message->meta['media_path'] ?? null,
            'media_width' => $message->meta['media_width'] ?? null,
            'media_height' => $message->meta['media_height'] ?? null,
            'duration_seconds' => $message->meta['duration_seconds'] ?? null,
            'file_name' => $message->meta['file_name'] ?? null,
            'latitude' => $message->latitude,
            'longitude' => $message->longitude,
            'map_url' => $message->map_url,
            'is_read_by_admin' => (bool) $message->is_read_by_admin,
            'is_read_by_user' => (bool) $message->is_read_by_user,
            'created_at' => $message->created_at?->toIso8601String(),
            'created_at_human' => $message->created_at?->diffForHumans(),
        ];
    }

    private function transformEmployeeDirectoryEntry(User $user): array
    {
        $isAdmin = $user->hasAdminIdentity();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => $user->avatar
                ? asset(User::AVATAR_UPLOAD_PATH . $user->avatar)
                : asset('assets/images/img.png'),
            'phone' => $user->phone,
            'department' => $user->department?->dept_name,
            'branch' => $user->branch?->name,
            'post' => $user->post?->post_name,
            'online_status' => (string) ((int) $user->online_status),
            'role' => $user->mobileDirectoryRole(),
            'user_type' => $user->mobileDirectoryUserType(),
            'is_admin' => $isAdmin ? '1' : '0',
            'admin' => $isAdmin ? '1' : '0',
            'is_online' => (int) $user->online_status === User::ONLINE,
            'directory_type' => 'employee',
            'source_id' => $user->id,
        ];
    }

    private function getAdminDirectoryEntries(int $userId)
    {
        return Admin::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'avatar'])
            ->map(function (Admin $admin) use ($userId) {
                try {
                    $conversation = $this->getOrCreateAdminConversation($userId, $admin->id);
                } catch (Throwable $throwable) {
                    report($throwable);
                    $conversation = null;
                }

                return $this->transformAdminDirectoryEntry($admin, $conversation);
            })
            ->values();
    }

    private function transformAdminDirectoryEntry(Admin $admin, ?ChatConversation $conversation = null): array
    {
        $directoryId = 1000000 + (int) $admin->id;

        return [
            'id' => $directoryId,
            'name' => $admin->name ?? 'Admin',
            'username' => $admin->username ?? 'admin',
            'email' => $admin->email ?? '',
            'phone' => '',
            'department' => 'Administration',
            'branch' => '',
            'post' => 'Admin',
            'avatar' => $admin->avatar
                ? asset(Admin::AVATAR_UPLOAD_PATH . $admin->avatar)
                : asset('assets/images/img.png'),
            'online_status' => '0',
            'role' => 'admin',
            'user_type' => 'admin',
            'is_admin' => '1',
            'admin' => '1',
            'is_online' => false,
            'directory_type' => 'admin',
            'source_id' => $admin->id,
            'conversation_id' => $conversation ? $this->externalConversationId($conversation->user_id, (int) $admin->id) : null,
            'internal_conversation_id' => $conversation ? (string) $conversation->id : null,
            'admin_id' => $admin->id,
            'admin_username' => $admin->username,
            'chat_mode' => 'admin_thread',
        ];
    }

    private function buildAdminContact(?ChatConversation $conversation, ?Admin $admin): array
    {
        return [
            'id' => 'admin-thread',
            'type' => 'admin',
            'is_pinned' => true,
            'sort_order' => 0,
            'name' => $admin?->name ?? 'Admin Team',
            'username' => $admin?->username ?? 'admin',
            'avatar' => $admin?->avatar
                ? asset(Admin::AVATAR_UPLOAD_PATH . $admin->avatar)
                : asset('assets/images/img.png'),
            'conversation_id' => ($conversation && $admin) ? $this->externalConversationId($conversation->user_id, $admin->id) : null,
            'internal_conversation_id' => $conversation ? (string) $conversation->id : null,
            'admin_id' => $admin?->id,
            'admin_username' => $admin?->username,
        ];
    }

    private function getOrCreateAdminConversation(int $userId, int $adminId): ChatConversation
    {
        if (!$this->supportsPerAdminConversation()) {
            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
            ]);
        }
        try {
            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
                'admin_id' => $adminId,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
            ]);
        }
    }

    private function resolveAdminConversation(int $userId, Request $request): ChatConversation
    {
        if (!$this->supportsPerAdminConversation()) {
            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
            ]);
        }

        $requestedConversationId = $request->filled('conversation_id')
            ? (string) $request->input('conversation_id')
            : null;
        $requestedAdminId = $this->requestedAdminId($request, $userId);

        $internalConversation = $this->conversationFromInternalIdentifier(
            $request->input('internal_conversation_id'),
            $userId
        ) ?? $this->conversationFromInternalIdentifier(
            $requestedConversationId,
            $userId
        );

        if ($internalConversation && ($internalConversation->admin_id === null || $requestedAdminId === null || (int) $internalConversation->admin_id === $requestedAdminId)) {
            return $internalConversation;
        }

        if ($requestedConversationId) {
            $adminIdFromConversation = $this->adminIdFromExternalConversationId($requestedConversationId, $userId);

            if ($adminIdFromConversation !== null) {
                return $this->getOrCreateAdminConversation($userId, $adminIdFromConversation);
            }
        }

        if ($requestedAdminId !== null) {
            return $this->getOrCreateAdminConversation($userId, $requestedAdminId);
        }

        $defaultAdmin = Admin::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->first(['id']);

        if (!$defaultAdmin) {
            return ChatConversation::firstOrCreate([
                'user_id' => $userId,
            ]);
        }

        return $this->getOrCreateAdminConversation($userId, $defaultAdmin->id);
    }

    private function supportsPerAdminConversation(): bool
    {
        static $supportsPerAdminConversation = null;

        if ($supportsPerAdminConversation !== null) {
            return $supportsPerAdminConversation;
        }

        try {
            $supportsPerAdminConversation = Schema::hasColumn('chat_conversations', 'admin_id');
        } catch (Throwable $throwable) {
            report($throwable);
            $supportsPerAdminConversation = false;
        }

        return $supportsPerAdminConversation;
    }

    private function conversationIdentifier(ChatConversation $conversation): string
    {
        if ($conversation->admin_id) {
            return $this->externalConversationId($conversation->user_id, (int) $conversation->admin_id);
        }

        return (string) $conversation->id;
    }

    private function externalConversationId(int $userId, int $adminId): string
    {
        return 'employee_admin_' . $userId . '_' . $adminId;
    }

    private function adminIdFromExternalConversationId(string $conversationId, int $expectedUserId): ?int
    {
        if (!preg_match('/^employee_admin_(\d+)_(\d+)$/', $conversationId, $matches)) {
            return null;
        }

        $userId = (int) $matches[1];
        $adminId = (int) $matches[2];

        if ($userId !== $expectedUserId) {
            return null;
        }

        return $adminId;
    }

    private function requestedConversationIdentifier(int $userId, Request $request): ?string
    {
        if ($request->filled('conversation_id')) {
            return (string) $request->input('conversation_id');
        }

        $adminId = $this->requestedAdminId($request);

        if ($adminId) {
            return $this->externalConversationId($userId, $adminId);
        }

        return null;
    }

    private function requestedAdminId(Request $request, ?int $userId = null): ?int
    {
        if ($request->filled('admin_id')) {
            return (int) $request->input('admin_id');
        }

        if ($request->filled('source_id')) {
            return (int) $request->input('source_id');
        }

        if ($request->filled('conversation_id')) {
            return $this->adminIdFromExternalConversationId(
                (string) $request->input('conversation_id'),
                $userId ?? auth()->id()
            );
        }

        $internalConversation = $this->conversationFromInternalIdentifier(
            $request->input('internal_conversation_id'),
            $userId ?? auth()->id()
        );

        if ($internalConversation?->admin_id) {
            return (int) $internalConversation->admin_id;
        }

        if ($request->filled('admin_username')) {
            $adminId = Admin::query()
                ->where('username', (string) $request->input('admin_username'))
                ->value('id');

            return $adminId ? (int) $adminId : null;
        }

        return null;
    }

    private function conversationFromInternalIdentifier(mixed $identifier, int $userId): ?ChatConversation
    {
        if (!is_numeric($identifier)) {
            return null;
        }

        return ChatConversation::query()
            ->where('id', (int) $identifier)
            ->where('user_id', $userId)
            ->first();
    }

    private function requestedAdminUsername(Request $request): ?string
    {
        if ($request->filled('admin_username')) {
            return (string) $request->input('admin_username');
        }

        $adminId = $this->requestedAdminId($request);

        if (!$adminId) {
            return null;
        }

        return Admin::query()
            ->where('id', $adminId)
            ->value('username');
    }

    private function loadConversationThreadMessages(ChatConversation $conversation, ?int $adminId)
    {
        $externalConversationId = $adminId
            ? $this->externalConversationId($conversation->user_id, $adminId)
            : null;

        return $conversation->messages()
            ->with(['adminSender:id,name,avatar,username', 'userSender:id,name,avatar', 'conversation.admin:id,username'])
            ->orderBy('created_at')
            ->get()
            ->filter(function (ChatMessage $message) use ($adminId, $externalConversationId) {
                return $this->messageBelongsToThread($message, $adminId, $externalConversationId);
            })
            ->values();
    }

    private function messageBelongsToThread(ChatMessage $message, ?int $adminId, ?string $externalConversationId): bool
    {
        if ($adminId === null) {
            return true;
        }

        if ((int) $message->conversation?->admin_id === $adminId) {
            return true;
        }

        if ($externalConversationId !== null && ($message->meta['external_conversation_id'] ?? null) === $externalConversationId) {
            return true;
        }

        if ((int) ($message->meta['admin_id'] ?? 0) === $adminId) {
            return true;
        }

        return $message->sender_type === ChatMessage::SENDER_ADMIN && (int) $message->sender_id === $adminId;
    }

    private function markThreadMessagesAsReadByUser($messages): void
    {
        $messageIds = collect($messages)
            ->filter(fn (ChatMessage $message) => $message->sender_type === ChatMessage::SENDER_ADMIN && !$message->is_read_by_user)
            ->pluck('id')
            ->all();

        if ($messageIds === []) {
            return;
        }

        ChatMessage::query()
            ->whereIn('id', $messageIds)
            ->update(['is_read_by_user' => true]);
    }

    private function threadAdminId(ChatConversation $conversation, Request $request): ?int
    {
        return $this->requestedAdminId($request, $conversation->user_id)
            ?? ($conversation->admin_id ? (int) $conversation->admin_id : null);
    }

    private function threadAdminUsername(ChatConversation $conversation, ?int $adminId, Request $request): ?string
    {
        if ($request->filled('admin_username')) {
            return (string) $request->input('admin_username');
        }

        if ($conversation->admin?->username && ((int) $conversation->admin_id === (int) $adminId || $adminId === null)) {
            return $conversation->admin->username;
        }

        if (!$adminId) {
            return null;
        }

        return Admin::query()
            ->where('id', $adminId)
            ->value('username');
    }

    private function threadConversationIdentifier(ChatConversation $conversation, ?int $adminId): string
    {
        if ($adminId) {
            return $this->externalConversationId($conversation->user_id, $adminId);
        }

        return $this->conversationIdentifier($conversation);
    }

    private function storeAttachment(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $imageMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar'];

        if (in_array($extension, $imageExtensions, true) || in_array($mimeType, $imageMimeTypes, true)) {
            $directory = 'chat/images';
            $safeExtension = in_array($extension, $imageExtensions, true) ? $extension : 'jpg';
            $fileName = uniqid('chat_', true) . '.' . $safeExtension;
            $path = $directory . '/' . $fileName;

            $image = Image::make($file->getRealPath())
                ->orientate()
                ->resize(1600, 1600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

            Storage::disk('public')->put($path, (string) $image->encode($safeExtension, 82));

            return [ChatMessage::TYPE_IMAGE, Storage::disk('public')->url($path)];
        }

        $directory = in_array($extension, $documentExtensions, true) ? 'chat/files' : 'chat/voice';
        $messageType = in_array($extension, $documentExtensions, true) ? 'file' : ChatMessage::TYPE_VOICE;
        $path = $file->store($directory, 'public');

        return [$messageType, Storage::disk('public')->url($path)];
    }

    private function normalizeIncomingMessageType(Request $request): string
    {
        $messageType = strtolower((string) (
            $request->input('chat_message_type')
            ?? $request->input('message_type')
            ?? $request->input('type')
            ?? ''
        ));

        if ($messageType === '' && $request->filled('media_type')) {
            $messageType = strtolower((string) $request->input('media_type'));
        }

        return match ($messageType) {
            'image' => ChatMessage::TYPE_IMAGE,
            'audio', 'voice' => ChatMessage::TYPE_VOICE,
            'document', 'file' => 'file',
            'location' => ChatMessage::TYPE_LOCATION,
            default => ChatMessage::TYPE_TEXT,
        };
    }

    private function normalizeStoredMessageType(?string $messageType, array $meta = []): string
    {
        return match (strtolower((string) $messageType)) {
            'file', 'document' => 'file',
            'audio' => 'voice',
            default => strtolower((string) $messageType) ?: (isset($meta['duration_seconds']) ? 'voice' : 'text'),
        };
    }
}
