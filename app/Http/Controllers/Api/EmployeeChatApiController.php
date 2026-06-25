<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\MobileChatHelper;
use App\Helpers\SMPush\SMPushHelper;
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
    private const HR_ASSISTANT_BRANCH_NAME = 'វីអាយភី';
    private const HR_ASSISTANT_ROLE_TERMS = [
        'hr assistance',
        'hr assistant',
    ];

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
            $adminDirectoryEntries = $adminList->map(fn (Admin $admin) => $this->transformAdminDirectoryEntry(
                $admin,
                $this->getOrCreateAdminConversation($user->id, $admin->id)
            ))->values();
            $hrAssistantContacts = $this->getHrAssistantDirectoryEntries($user->id);

            return AppHelper::sendSuccessResponse('Mobile chat access loaded successfully.', [
                'scope' => $scope,
                'scope_label' => MobileChatHelper::scopeOptions()[$scope] ?? $scope,
                'employee_directory_enabled' => $scope === MobileChatHelper::MODE_ALL_EMPLOYEES,
                'admin_chat_enabled' => true,
                'per_admin_conversation_enabled' => $supportsPerAdminConversation,
                'admin_contact' => $adminContact,
                'admins' => $this->mergeDirectoryContacts($adminDirectoryEntries, $hrAssistantContacts),
                'hr_assistant_contacts' => $hrAssistantContacts,
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
            $adminDirectoryEntries = $this->getAdminDirectoryEntries($authUserId);
            $hrAssistantContacts = $this->getHrAssistantDirectoryEntries($authUserId);

            if ($scope !== MobileChatHelper::MODE_ALL_EMPLOYEES) {
                return AppHelper::sendSuccessResponse('Mobile chat contacts loaded successfully.', [
                    'scope' => $scope,
                    'admin_contact' => $adminContact,
                    'pinned_contacts' => [$adminContact],
                    'online_contacts' => [],
                    'contacts' => $this->mergeDirectoryContacts($adminDirectoryEntries, $hrAssistantContacts),
                    'hr_assistant_contacts' => $hrAssistantContacts,
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
                ->concat($adminDirectoryEntries)
                ->concat($hrAssistantContacts)
                ->unique(fn (array $contact) => ($contact['directory_type'] ?? 'unknown') . ':' . ($contact['source_id'] ?? $contact['id'] ?? ''))
                ->values();

            return AppHelper::sendSuccessResponse('Mobile chat contacts loaded successfully.', [
                'scope' => $scope,
                'per_admin_conversation_enabled' => $this->supportsPerAdminConversation(),
                'admin_contact' => $adminContact,
                'pinned_contacts' => [$adminContact],
                'online_contacts' => $employeeContacts->where('is_online', true)->values(),
                'contacts' => $contacts,
                'hr_assistant_contacts' => $hrAssistantContacts,
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
            [$conversation, $threadMeta] = $this->resolveConversation(auth()->id(), $request);
            $messages = $this->loadConversationThreadMessages($conversation, $threadMeta);
            $this->markThreadMessagesAsReadByUser($messages, $threadMeta);

            return AppHelper::sendSuccessResponse('Mobile chat messages loaded successfully.', [
                'conversation_id' => $this->threadConversationIdentifier($conversation, $threadMeta),
                'internal_conversation_id' => (string) $conversation->id,
                'admin_id' => $threadMeta['admin_id'],
                'admin_username' => $threadMeta['admin_username'],
                'peer_user_id' => $threadMeta['peer_user_id'],
                'messages' => $messages->map(fn (ChatMessage $message) => $this->transformMessage($message, $threadMeta))->values(),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

                return AppHelper::sendSuccessResponse('Mobile chat loaded with fallback state.', [
                    'conversation_id' => $this->requestedConversationIdentifier(auth()->id(), $request),
                    'internal_conversation_id' => null,
                    'admin_id' => $this->requestedAdminId($request),
                    'admin_username' => $this->requestedAdminUsername($request),
                    'peer_user_id' => $this->requestedPeerUserId($request, auth()->id()),
                    'messages' => [],
                    'fallback' => true,
                ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $hasInlineMedia = $request->filled('media_url')
            || $request->filled('media_path')
            || $request->filled('file_name')
            || $request->filled('duration_seconds')
            || in_array(strtolower((string) $request->input('message_type')), ['image', 'voice', 'file', 'location'], true)
            || in_array(strtolower((string) $request->input('chat_message_type')), ['image', 'voice', 'file', 'location'], true)
            || in_array(strtolower((string) $request->input('media_type')), ['image', 'audio', 'voice', 'document', 'file'], true);

        if (!$request->hasFile('attachment')
            && !$hasInlineMedia
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
            [$conversation, $threadMeta] = $this->resolveConversation(auth()->id(), $request);
            $sentMessage = null;

            DB::transaction(function () use ($request, $conversation, $threadMeta, &$sentMessage) {
                $messageType = $this->normalizeIncomingMessageType($request);
                $mediaUrl = $request->input('media_url');
                $storedMediaPath = null;
                $storedFileName = null;

                if ($request->hasFile('attachment')) {
                    [$messageType, $mediaUrl, $storedMediaPath, $storedFileName] = $this->storeAttachment($request->file('attachment'));
                } elseif ($request->filled('latitude') && $request->filled('longitude')) {
                    $messageType = ChatMessage::TYPE_LOCATION;
                }

                $normalizedMediaPath = ChatMessage::normalizeMediaPath(
                    $request->input('media_path'),
                    $mediaUrl
                );
                $mediaUrl = ChatMessage::normalizeMediaUrl($normalizedMediaPath, $mediaUrl);

                $meta = array_filter([
                    'media_path' => $normalizedMediaPath ?? $storedMediaPath,
                    'media_width' => $request->input('media_width'),
                    'media_height' => $request->input('media_height'),
                    'duration_seconds' => $request->input('duration_seconds'),
                    'file_name' => $request->input('file_name') ?: $storedFileName,
                    'admin_id' => $threadMeta['admin_id'],
                    'admin_username' => $threadMeta['admin_username'],
                    'peer_user_id' => $threadMeta['peer_user_id'],
                    'external_conversation_id' => $this->threadConversationIdentifier($conversation, $threadMeta),
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
                    'is_read_by_admin' => $threadMeta['type'] === 'admin',
                    'is_read_by_user' => $threadMeta['type'] === 'employee' ? false : true,
                ]);

                $conversation->update([
                    'last_message_at' => $message->created_at,
                ]);

                $sentMessage = $message;
            });

            if ($sentMessage !== null) {
                $this->sendEmployeeChatPushNotification($conversation, $sentMessage, $threadMeta);
            }

            $messages = $this->loadConversationThreadMessages($conversation, $threadMeta);

            return AppHelper::sendSuccessResponse('Message sent successfully.', [
                'conversation_id' => $this->threadConversationIdentifier($conversation, $threadMeta),
                'internal_conversation_id' => (string) $conversation->id,
                'admin_id' => $threadMeta['admin_id'],
                'admin_username' => $threadMeta['admin_username'],
                'peer_user_id' => $threadMeta['peer_user_id'],
                'messages' => $messages->map(fn (ChatMessage $message) => $this->transformMessage($message, $threadMeta))->values(),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return AppHelper::sendErrorResponse('Unable to send this message right now.', 500);
        }
    }

    private function transformMessage(ChatMessage $message, array $threadMeta = []): array
    {
        $conversation = $message->conversation;
        $normalizedType = $this->normalizeStoredMessageType($message->message_type, $message->meta ?? []);
        $resolvedAdminId = ($threadMeta['admin_id'] ?? null)
            ?? $conversation?->admin_id
            ?? ($message->meta['admin_id'] ?? null)
            ?? ($message->sender_type === ChatMessage::SENDER_ADMIN ? $message->sender_id : null);
        $resolvedAdminUsername = ($threadMeta['admin_username'] ?? null)
            ?? $conversation?->admin?->username
            ?? ($message->meta['admin_username'] ?? null)
            ?? $message->adminSender?->username;
        $resolvedPeerUserId = ($threadMeta['peer_user_id'] ?? null)
            ?? $conversation?->peer_user_id
            ?? ($message->meta['peer_user_id'] ?? null);
        $conversationId = $conversation
            ? $this->conversationIdentifierForThread($conversation, $threadMeta)
            : (string) $message->conversation_id;

        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender' => $message->sender_type,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->senderName(),
            'sender_avatar' => $message->senderAvatar(),
            'conversation_id' => $conversationId,
            'internal_conversation_id' => (string) $message->conversation_id,
            'admin_id' => $resolvedAdminId ? (int) $resolvedAdminId : null,
            'admin_username' => $resolvedAdminUsername,
            'peer_user_id' => $resolvedPeerUserId ? (int) $resolvedPeerUserId : null,
            'message_type' => $normalizedType,
            'type' => $normalizedType,
            'message' => $message->message,
            'body' => $message->message,
            'media_url' => $message->resolvedMediaUrl(),
            'media_path' => $message->resolvedMediaPath(),
            'media_width' => $message->meta['media_width'] ?? null,
            'media_height' => $message->meta['media_height'] ?? null,
            'duration_seconds' => $message->meta['duration_seconds'] ?? null,
            'file_name' => $message->meta['file_name'] ?? null,
            'latitude' => $message->latitude,
            'longitude' => $message->longitude,
            'map_url' => $message->resolvedMapUrl(),
            'location' => $message->latitude !== null && $message->longitude !== null ? [
                'latitude' => (float) $message->latitude,
                'longitude' => (float) $message->longitude,
                'map_url' => $message->resolvedMapUrl(),
            ] : null,
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
            'online' => (string) ((int) $user->online_status),
            'role' => $user->mobileDirectoryRole(),
            'user_type' => $user->mobileDirectoryUserType(),
            'is_admin' => $isAdmin ? '1' : '0',
            'admin' => $isAdmin ? '1' : '0',
            'is_online' => (int) $user->online_status === User::ONLINE,
            'directory_type' => 'employee',
            'source_id' => $user->id,
            'conversation_id' => $this->supportsEmployeeConversations()
                ? $this->employeeConversationExternalId(auth()->id(), $user->id)
                : null,
            'peer_user_id' => $user->id,
            'chat_mode' => 'employee_thread',
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

    private function getHrAssistantDirectoryEntries(int $authUserId)
    {
        return User::withoutGlobalScopes()
            ->select(['id', 'name', 'username', 'email', 'avatar', 'phone', 'department_id', 'branch_id', 'post_id', 'role_id', 'user_type', 'online_status'])
            ->with(['department:id,dept_name', 'branch:id,name', 'post:id,post_name', 'role:id,name,slug'])
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->where('id', '!=', $authUserId)
            ->whereIn('branch_id', function ($query) {
                $query->select('id')
                    ->from('branches')
                    ->where('name', self::HR_ASSISTANT_BRANCH_NAME);
            })
            ->whereHas('role', function ($query) {
                $query->where(function ($roleQuery) {
                    foreach (self::HR_ASSISTANT_ROLE_TERMS as $term) {
                        $roleQuery
                            ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $term . '%'])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ['%' . str_replace(' ', '-', $term) . '%'])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ['%' . str_replace(' ', '_', $term) . '%']);
                    }
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $this->transformEmployeeDirectoryEntry($user))
            ->values();
    }

    private function mergeDirectoryContacts($contacts, $additionalContacts)
    {
        return $contacts
            ->concat($additionalContacts)
            ->unique(fn (array $contact) => ($contact['directory_type'] ?? 'unknown') . ':' . ($contact['source_id'] ?? $contact['id'] ?? ''))
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
            'online' => '0',
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

    private function resolveConversation(int $userId, Request $request): array
    {
        $peerUserId = $this->requestedPeerUserId($request, $userId);

        if ($peerUserId !== null && $this->supportsEmployeeConversations()) {
            $conversation = $this->resolveEmployeeConversation($userId, $peerUserId, $request);

            return [$conversation, [
                'type' => 'employee',
                'admin_id' => null,
                'admin_username' => null,
                'peer_user_id' => $peerUserId,
            ]];
        }

        $conversation = $this->resolveAdminConversation($userId, $request);
        $adminId = $this->threadAdminId($conversation, $request);

        return [$conversation, [
            'type' => 'admin',
            'admin_id' => $adminId,
            'admin_username' => $this->threadAdminUsername($conversation, $adminId, $request),
            'peer_user_id' => null,
        ]];
    }

    private function resolveEmployeeConversation(int $userId, int $peerUserId, Request $request): ChatConversation
    {
        $internalConversation = $this->conversationFromInternalIdentifier(
            $request->input('internal_conversation_id'),
            $userId
        ) ?? $this->conversationFromInternalIdentifier(
            $request->input('conversation_id'),
            $userId
        );

        if ($internalConversation && $this->conversationHasPeerUser($internalConversation, $peerUserId)) {
            return $internalConversation;
        }

        if ($request->filled('conversation_id')) {
            $peerIdFromConversation = $this->peerUserIdFromExternalConversationId(
                (string) $request->input('conversation_id'),
                $userId
            );

            if ($peerIdFromConversation !== null) {
                $peerUserId = $peerIdFromConversation;
            }
        }

        return $this->getOrCreateEmployeeConversation($userId, $peerUserId);
    }

    private function getOrCreateEmployeeConversation(int $userId, int $peerUserId): ChatConversation
    {
        [$primaryUserId, $secondaryUserId] = $this->normalizeEmployeeConversationUsers($userId, $peerUserId);

        return ChatConversation::firstOrCreate([
            'user_id' => $primaryUserId,
            'peer_user_id' => $secondaryUserId,
            'admin_id' => null,
        ]);
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

    private function supportsEmployeeConversations(): bool
    {
        static $supportsEmployeeConversations = null;

        if ($supportsEmployeeConversations !== null) {
            return $supportsEmployeeConversations;
        }

        try {
            $supportsEmployeeConversations = Schema::hasColumn('chat_conversations', 'peer_user_id');
        } catch (Throwable $throwable) {
            report($throwable);
            $supportsEmployeeConversations = false;
        }

        return $supportsEmployeeConversations;
    }

    private function conversationIdentifier(ChatConversation $conversation): string
    {
        if ($conversation->peer_user_id) {
            return $this->employeeConversationExternalId($conversation->user_id, (int) $conversation->peer_user_id);
        }

        if ($conversation->admin_id) {
            return $this->externalConversationId($conversation->user_id, (int) $conversation->admin_id);
        }

        return (string) $conversation->id;
    }

    private function externalConversationId(int $userId, int $adminId): string
    {
        return 'employee_admin_' . $userId . '_' . $adminId;
    }

    private function employeeConversationExternalId(int $userId, int $peerUserId): string
    {
        [$primaryUserId, $secondaryUserId] = $this->normalizeEmployeeConversationUsers($userId, $peerUserId);

        return 'employee_dm_' . $primaryUserId . '_' . $secondaryUserId;
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

    private function peerUserIdFromExternalConversationId(string $conversationId, int $expectedUserId): ?int
    {
        if (!preg_match('/^employee_dm_(\d+)_(\d+)$/', $conversationId, $matches)) {
            return null;
        }

        $firstUserId = (int) $matches[1];
        $secondUserId = (int) $matches[2];

        if ($expectedUserId !== $firstUserId && $expectedUserId !== $secondUserId) {
            return null;
        }

        return $expectedUserId === $firstUserId ? $secondUserId : $firstUserId;
    }

    private function requestedConversationIdentifier(int $userId, Request $request): ?string
    {
        if ($request->filled('conversation_id')) {
            return (string) $request->input('conversation_id');
        }

        $peerUserId = $this->requestedPeerUserId($request, $userId);

        if ($peerUserId) {
            return $this->employeeConversationExternalId($userId, $peerUserId);
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

        if ($request->filled('source_id') && $this->requestTargetsAdmin($request)) {
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

    private function requestedPeerUserId(Request $request, int $userId): ?int
    {
        if (!$this->supportsEmployeeConversations()) {
            return null;
        }

        if ($request->filled('peer_user_id')) {
            $peerUserId = (int) $request->input('peer_user_id');

            return $peerUserId !== $userId ? $peerUserId : null;
        }

        if ($request->filled('source_id') && $this->requestTargetsEmployee($request)) {
            $peerUserId = (int) $request->input('source_id');

            return $peerUserId !== $userId ? $peerUserId : null;
        }

        if ($request->filled('conversation_id')) {
            return $this->peerUserIdFromExternalConversationId(
                (string) $request->input('conversation_id'),
                $userId
            );
        }

        $internalConversation = $this->conversationFromInternalIdentifier(
            $request->input('internal_conversation_id'),
            $userId
        );

        if ($internalConversation) {
            return $this->otherUserIdForConversation($internalConversation, $userId);
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
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId);

                if ($this->supportsEmployeeConversations()) {
                    $query->orWhere('peer_user_id', $userId);
                }
            })
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

    private function loadConversationThreadMessages(ChatConversation $conversation, array $threadMeta)
    {
        $externalConversationId = $this->conversationIdentifierForThread($conversation, $threadMeta);

        return $conversation->messages()
            ->with(['adminSender:id,name,avatar,username', 'userSender:id,name,avatar', 'conversation.admin:id,username', 'conversation.peerUser:id,name,username,avatar'])
            ->orderBy('created_at')
            ->get()
            ->filter(function (ChatMessage $message) use ($threadMeta, $externalConversationId) {
                return $this->messageBelongsToThread($message, $threadMeta, $externalConversationId);
            })
            ->values();
    }

    private function messageBelongsToThread(ChatMessage $message, array $threadMeta, ?string $externalConversationId): bool
    {
        if (($threadMeta['type'] ?? 'admin') === 'employee') {
            $peerUserId = (int) ($threadMeta['peer_user_id'] ?? 0);

            if ($peerUserId === 0) {
                return true;
            }

            if ((int) $message->conversation?->peer_user_id === $peerUserId || (int) $message->conversation?->user_id === $peerUserId) {
                return true;
            }

            return (int) ($message->meta['peer_user_id'] ?? 0) === $peerUserId;
        }

        $adminId = $threadMeta['admin_id'] ?? null;

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

    private function markThreadMessagesAsReadByUser($messages, array $threadMeta): void
    {
        $messageIds = collect($messages)
            ->filter(function (ChatMessage $message) use ($threadMeta) {
                if (($threadMeta['type'] ?? 'admin') === 'employee') {
                    return $message->sender_type === ChatMessage::SENDER_USER
                        && (int) $message->sender_id !== auth()->id()
                        && !$message->is_read_by_user;
                }

                return $message->sender_type === ChatMessage::SENDER_ADMIN && !$message->is_read_by_user;
            })
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

    private function threadConversationIdentifier(ChatConversation $conversation, array $threadMeta): string
    {
        if (($threadMeta['type'] ?? 'admin') === 'employee' && !empty($threadMeta['peer_user_id'])) {
            return $this->employeeConversationExternalId($conversation->user_id, (int) $threadMeta['peer_user_id']);
        }

        if (!empty($threadMeta['admin_id'])) {
            return $this->externalConversationId($conversation->user_id, (int) $threadMeta['admin_id']);
        }

        return $this->conversationIdentifier($conversation);
    }

    private function conversationIdentifierForThread(ChatConversation $conversation, array $threadMeta): string
    {
        return $this->threadConversationIdentifier($conversation, $threadMeta);
    }

    private function requestTargetsAdmin(Request $request): bool
    {
        $directoryType = strtolower((string) $request->input('directory_type', ''));

        return in_array($directoryType, ['admin', 'administrator'], true)
            || $request->filled('admin_id')
            || $request->filled('admin_username')
            || ($request->filled('conversation_id')
                && str_starts_with((string) $request->input('conversation_id'), 'employee_admin_'));
    }

    private function requestTargetsEmployee(Request $request): bool
    {
        $directoryType = strtolower((string) $request->input('directory_type', ''));

        return $directoryType === 'employee'
            || ($request->filled('conversation_id')
                && str_starts_with((string) $request->input('conversation_id'), 'employee_dm_'));
    }

    private function normalizeEmployeeConversationUsers(int $userId, int $peerUserId): array
    {
        return $userId < $peerUserId
            ? [$userId, $peerUserId]
            : [$peerUserId, $userId];
    }

    private function conversationHasPeerUser(ChatConversation $conversation, int $peerUserId): bool
    {
        return (int) $conversation->user_id === $peerUserId
            || (int) $conversation->peer_user_id === $peerUserId;
    }

    private function otherUserIdForConversation(ChatConversation $conversation, int $userId): ?int
    {
        if ((int) $conversation->user_id === $userId) {
            return $conversation->peer_user_id ? (int) $conversation->peer_user_id : null;
        }

        if ((int) $conversation->peer_user_id === $userId) {
            return (int) $conversation->user_id;
        }

        return null;
    }

    private function storeAttachment(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());
        $originalName = $file->getClientOriginalName();

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

            return [ChatMessage::TYPE_IMAGE, Storage::disk('public')->url($path), $path, $originalName];
        }

        $directory = in_array($extension, $documentExtensions, true) ? 'chat/files' : 'chat/voice';
        $messageType = in_array($extension, $documentExtensions, true) ? 'file' : ChatMessage::TYPE_VOICE;
        $path = $messageType === ChatMessage::TYPE_VOICE
            ? ChatMessage::storeVoiceUpload($file, $directory)
            : $file->store($directory, 'public');

        return [
            $messageType,
            ChatMessage::normalizeMediaUrl($path, null, $messageType),
            ChatMessage::normalizeMediaPath($path, null, $messageType),
            $originalName,
        ];
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

    private function sendEmployeeChatPushNotification(ChatConversation $conversation, ChatMessage $message, array $threadMeta): void
    {
        if (($threadMeta['type'] ?? null) !== 'employee' || empty($threadMeta['peer_user_id'])) {
            return;
        }

        try {
            $peer = User::query()->select(['id', 'username'])->find($threadMeta['peer_user_id']);
            if (!$peer || !$peer->username) {
                return;
            }

            $sender = auth()->user();
            $notificationBody = match ($message->message_type) {
                ChatMessage::TYPE_IMAGE => 'Sent a photo',
                ChatMessage::TYPE_VOICE => 'Sent a voice message',
                ChatMessage::TYPE_LOCATION => 'Sent a location',
                'file' => 'Sent a file',
                default => (string) ($message->message ?: 'New message'),
            };

            SMPushHelper::sendPushNotification(
                title: $sender?->name ?? 'New message',
                conversation_id: $this->threadConversationIdentifier($conversation, $threadMeta),
                message: $notificationBody,
                type: 'chat',
                usernames: [$peer->username],
                project_id: '',
                chatMessageType: $message->message_type,
                media_url: $message->media_url ?? '',
                latitude: $message->latitude,
                longitude: $message->longitude,
                map_url: $message->map_url ?? '',
                internalConversationId: (string) $conversation->id,
            );
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
