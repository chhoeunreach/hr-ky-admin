<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\GroupChat;
use App\Models\GroupChatMember;
use App\Models\GroupChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;
use Intervention\Image\Facades\Image;

class GroupChatController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $groupIds = GroupChatMember::query()
                ->where('user_id', $userId)
                ->pluck('group_chat_id');

            $groups = GroupChat::query()
                ->with(['latestMessage.sender:id,name,avatar', 'members.user:id,name,avatar'])
                ->whereIn('id', $groupIds)
                ->orderByDesc('last_message_at')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (GroupChat $group) => $this->transformGroup($group, $userId));

            return AppHelper::sendSuccessResponse('Groups loaded successfully.', $groups);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to load groups.', 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $group = GroupChat::query()
                ->with(['members.user:id,name,username,avatar,email,phone'])
                ->findOrFail($id);

            $member = $group->members->firstWhere('user_id', $userId);
            if (!$member) {
                return AppHelper::sendErrorResponse('You are not a member of this group.', 403);
            }

            return AppHelper::sendSuccessResponse('Group loaded successfully.', [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'avatar' => $group->avatar
                    ? asset('uploads/group/avatar/' . $group->avatar)
                    : null,
                'group_code' => $group->group_code,
                'creator_id' => $group->creator_id,
                'created_at' => $group->created_at?->toIso8601String(),
                'my_role' => $member->role,
                'member_count' => $group->members->count(),
                'members' => $group->members->map(fn (GroupChatMember $m) => [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'name' => $m->user?->name,
                    'username' => $m->user?->username,
                    'avatar' => $m->user?->avatar
                        ? asset(User::AVATAR_UPLOAD_PATH . $m->user->avatar)
                        : asset('assets/images/img.png'),
                    'role' => $m->role,
                    'joined_at' => $m->joined_at?->toIso8601String(),
                ]),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to load group details.', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
            $userId = auth()->id();
            $memberIds = array_unique(array_merge(
                [$userId],
                array_map('intval', $request->input('member_ids', []))
            ));

            return DB::transaction(function () use ($request, $userId, $memberIds) {
                $groupCode = $this->generateGroupCode();

                $group = GroupChat::create([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'creator_id' => $userId,
                    'group_code' => $groupCode,
                ]);

                if ($request->hasFile('avatar')) {
                    $avatarPath = $request->file('avatar')->store('group/avatar', 'public');
                    $group->update(['avatar' => basename($avatarPath)]);
                }

                foreach ($memberIds as $memberId) {
                    $role = $memberId === $userId
                        ? GroupChatMember::ROLE_CREATOR
                        : GroupChatMember::ROLE_MEMBER;

                    GroupChatMember::create([
                        'group_chat_id' => $group->id,
                        'user_id' => $memberId,
                        'role' => $role,
                        'joined_at' => now(),
                    ]);
                }

                $group->load(['members.user:id,name,username,avatar']);

                return AppHelper::sendSuccessResponse('Group created successfully.', [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'avatar' => $group->avatar
                        ? asset('uploads/group/avatar/' . $group->avatar)
                        : null,
                    'group_code' => $group->group_code,
                    'creator_id' => $group->creator_id,
                    'member_count' => $group->members->count(),
                    'members' => $group->members->map(fn (GroupChatMember $m) => [
                        'id' => $m->id,
                        'user_id' => $m->user_id,
                        'name' => $m->user?->name,
                        'username' => $m->user?->username,
                        'avatar' => $m->user?->avatar
                            ? asset(User::AVATAR_UPLOAD_PATH . $m->user->avatar)
                            : asset('assets/images/img.png'),
                        'role' => $m->role,
                    ]),
                ]);
            });
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to create group.', 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
            $userId = auth()->id();
            $group = GroupChat::findOrFail($id);

            $member = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$member || !$member->canManageMembers()) {
                return AppHelper::sendErrorResponse('Only group admins can update group info.', 403);
            }

            $updateData = [];
            if ($request->filled('name')) {
                $updateData['name'] = $request->input('name');
            }
            if ($request->filled('description')) {
                $updateData['description'] = $request->input('description');
            }

            if ($request->hasFile('avatar')) {
                if ($group->avatar) {
                    Storage::disk('public')->delete('group/avatar/' . $group->avatar);
                }
                $avatarPath = $request->file('avatar')->store('group/avatar', 'public');
                $updateData['avatar'] = basename($avatarPath);
            }

            if ($updateData !== []) {
                $group->update($updateData);
            }

            return AppHelper::sendSuccessResponse('Group updated successfully.', [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'avatar' => $group->avatar
                    ? asset('uploads/group/avatar/' . $group->avatar)
                    : null,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to update group.', 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $group = GroupChat::findOrFail($id);

            if ((int) $group->creator_id !== $userId) {
                return AppHelper::sendErrorResponse('Only the group creator can delete this group.', 403);
            }

            $group->delete();

            return AppHelper::sendSuccessResponse('Group deleted successfully.');
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to delete group.', 500);
        }
    }

    public function addMembers(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
            $userId = auth()->id();
            $group = GroupChat::findOrFail($id);

            $member = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$member || !$member->canManageMembers()) {
                return AppHelper::sendErrorResponse('Only group admins can add members.', 403);
            }

            $existingUserIds = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->pluck('user_id')
                ->toArray();

            $newUserIds = array_filter(
                array_map('intval', $request->input('user_ids', [])),
                fn ($uid) => !in_array($uid, $existingUserIds, true)
            );

            if ($newUserIds === []) {
                return AppHelper::sendSuccessResponse('All users are already members.');
            }

            $now = now();
            $records = array_map(fn ($uid) => [
                'group_chat_id' => $id,
                'user_id' => $uid,
                'role' => GroupChatMember::ROLE_MEMBER,
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ], $newUserIds);

            GroupChatMember::insert($records);

            $users = User::whereIn('id', $newUserIds)->pluck('name', 'id');
            $authUser = auth()->user();
            $pushUserIds = array_map('intval', $newUserIds);

            SMPushHelper::sendPushNotification(
                title: $group->name,
                conversation_id: 'group_' . $group->id,
                message: $authUser->name . ' added you to the group',
                type: 'group_chat',
                usernames: $pushUserIds,
                project_id: '',
                chatMessageType: 'text',
                media_url: '',
                latitude: null,
                longitude: null,
                map_url: '',
            );

            return AppHelper::sendSuccessResponse('Members added successfully.', [
                'added_user_ids' => array_values($newUserIds),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to add members.', 500);
        }
    }

    public function removeMember(int $id, int $userIdToRemove): JsonResponse
    {
        try {
            $authUserId = auth()->id();

            if ($authUserId === $userIdToRemove) {
                return $this->leaveGroup($id, $authUserId);
            }

            $group = GroupChat::findOrFail($id);
            $member = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $authUserId)
                ->first();

            if (!$member || !$member->canManageMembers()) {
                return AppHelper::sendErrorResponse('Only group admins can remove members.', 403);
            }

            $targetMember = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $userIdToRemove)
                ->first();

            if (!$targetMember) {
                return AppHelper::sendErrorResponse('User is not a member of this group.', 404);
            }

            if ($targetMember->isCreator()) {
                return AppHelper::sendErrorResponse('Cannot remove the group creator.', 403);
            }

            $targetMember->delete();

            return AppHelper::sendSuccessResponse('Member removed successfully.');
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to remove member.', 500);
        }
    }

    public function updateMemberRole(Request $request, int $id, int $userIdToUpdate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => ['required', 'string', 'in:admin,member'],
        ]);

        if ($validator->fails()) {
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
            $authUserId = auth()->id();
            $group = GroupChat::findOrFail($id);

            if ((int) $group->creator_id !== $authUserId) {
                return AppHelper::sendErrorResponse('Only the group creator can change roles.', 403);
            }

            $targetMember = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $userIdToUpdate)
                ->first();

            if (!$targetMember) {
                return AppHelper::sendErrorResponse('User is not a member of this group.', 404);
            }

            if ($targetMember->isCreator()) {
                return AppHelper::sendErrorResponse('Cannot change the role of the group creator.', 403);
            }

            $targetMember->update(['role' => $request->input('role')]);

            return AppHelper::sendSuccessResponse('Member role updated successfully.', [
                'user_id' => $userIdToUpdate,
                'role' => $request->input('role'),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to update member role.', 500);
        }
    }

    public function leaveGroup(int $id, ?int $userId = null): JsonResponse
    {
        try {
            $userId = $userId ?? auth()->id();
            $group = GroupChat::findOrFail($id);

            $member = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$member) {
                return AppHelper::sendErrorResponse('You are not a member of this group.', 404);
            }

            if ($member->isCreator()) {
                $otherAdmins = GroupChatMember::query()
                    ->where('group_chat_id', $id)
                    ->where('user_id', '!=', $userId)
                    ->whereIn('role', [GroupChatMember::ROLE_ADMIN, GroupChatMember::ROLE_MEMBER])
                    ->orderBy('role')
                    ->orderBy('id')
                    ->first();

                if ($otherAdmins) {
                    $otherAdmins->update(['role' => GroupChatMember::ROLE_CREATOR]);
                    $group->update(['creator_id' => $otherAdmins->user_id]);
                } else {
                    $group->delete();
                    return AppHelper::sendSuccessResponse('Group deleted as there are no remaining members.');
                }
            }

            $member->delete();

            $remainingCount = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->count();

            if ($remainingCount === 0) {
                $group->delete();
                return AppHelper::sendSuccessResponse('Group deleted as there are no remaining members.');
            }

            return AppHelper::sendSuccessResponse('You have left the group.');
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to leave group.', 500);
        }
    }

    public function messages(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $group = GroupChat::findOrFail($id);

            $isMember = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $userId)
                ->exists();

            if (!$isMember) {
                return AppHelper::sendErrorResponse('You are not a member of this group.', 403);
            }

            $messages = GroupChatMessage::query()
                ->with('sender:id,name,username,avatar')
                ->where('group_chat_id', $id)
                ->orderBy('created_at')
                ->get()
                ->map(fn (GroupChatMessage $msg) => $this->transformMessage($msg));

            return AppHelper::sendSuccessResponse('Messages loaded successfully.', [
                'group_id' => $id,
                'messages' => $messages,
            ]);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to load messages.', 500);
        }
    }

    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $hasAttachment = $request->hasFile('attachment')
            || $request->filled('media_url')
            || $request->filled('latitude');

        if (!$hasAttachment && trim((string) $request->input('message')) === '') {
            return AppHelper::sendErrorResponse('Message or attachment is required.', 422);
        }

        $validator = Validator::make($request->all(), [
            'message' => ['nullable', 'string'],
            'message_type' => ['nullable', 'string', 'in:text,image,voice,file,location'],
            'media_url' => ['nullable', 'string'],
            'media_path' => ['nullable', 'string'],
            'file_name' => ['nullable', 'string'],
            'media_width' => ['nullable', 'numeric'],
            'media_height' => ['nullable', 'numeric'],
            'duration_seconds' => ['nullable', 'numeric'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'map_url' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        if ($validator->fails()) {
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
            $userId = auth()->id();
            $group = GroupChat::findOrFail($id);

            $isMember = GroupChatMember::query()
                ->where('group_chat_id', $id)
                ->where('user_id', $userId)
                ->exists();

            if (!$isMember) {
                return AppHelper::sendErrorResponse('You are not a member of this group.', 403);
            }

            $messageType = $this->normalizeMessageType($request);
            $mediaUrl = $request->input('media_url');
            $mediaPath = null;
            $fileName = null;

            if ($request->hasFile('attachment')) {
                [$messageType, $mediaUrl, $mediaPath, $fileName] = $this->storeAttachment($request->file('attachment'));
            }

            $meta = array_filter([
                'media_path' => $mediaPath,
                'media_width' => $request->input('media_width'),
                'media_height' => $request->input('media_height'),
                'duration_seconds' => $request->input('duration_seconds'),
                'file_name' => $request->input('file_name') ?: $fileName,
            ], fn ($v) => $v !== null && $v !== '');

            $mapUrl = $request->input('map_url');
            if ($messageType === GroupChatMessage::TYPE_LOCATION && $mapUrl === null && $request->filled('latitude') && $request->filled('longitude')) {
                $mapUrl = 'https://www.google.com/maps?q=' . $request->input('latitude') . ',' . $request->input('longitude');
            }

            $message = GroupChatMessage::create([
                'group_chat_id' => $id,
                'sender_id' => $userId,
                'message_type' => $messageType,
                'message' => $request->input('message'),
                'media_url' => GroupChatMessage::normalizeMediaUrl($mediaPath, $mediaUrl),
                'media_path' => $mediaPath,
                'file_name' => $fileName ?: $request->input('file_name'),
                'media_width' => $request->input('media_width'),
                'media_height' => $request->input('media_height'),
                'duration_seconds' => $request->input('duration_seconds'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'map_url' => $mapUrl,
                'meta' => $meta === [] ? null : $meta,
            ]);

            $group->update(['last_message_at' => $message->created_at]);

            $this->sendGroupPushNotification($group, $message, $userId);

            $message->load('sender:id,name,username,avatar');

            return AppHelper::sendSuccessResponse('Message sent successfully.', [
                'message' => $this->transformMessage($message),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse('Unable to send message.', 500);
        }
    }

    public function mediaUpload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:image,audio,voice,document,file'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        if ($validator->fails()) {
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        try {
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
                $path = in_array($type, ['audio', 'voice'], true)
                    ? GroupChatMessage::storeVoiceUpload($file, $directory)
                    : $file->store($directory, 'public');
                $width = null;
                $height = null;
            }

            return response()->json([
                'success' => true,
                'status' => true,
                'message' => 'Media uploaded successfully',
                'data' => [
                    'url' => GroupChatMessage::normalizeMediaUrl($path, null),
                    'type' => $responseType,
                    'path' => $path,
                    'width' => $width,
                    'height' => $height,
                ],
            ], 200);
        } catch (Throwable $throwable) {
            report($throwable);
            return AppHelper::sendErrorResponse($throwable->getMessage(), 400);
        }
    }

    private function transformGroup(GroupChat $group, int $userId): array
    {
        $myMember = $group->members->firstWhere('user_id', $userId);
        $lastMessage = $group->latestMessage;

        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'avatar' => $group->avatar
                ? asset('uploads/group/avatar/' . $group->avatar)
                : null,
            'group_code' => $group->group_code,
            'creator_id' => $group->creator_id,
            'my_role' => $myMember?->role,
            'member_count' => $group->members->count(),
            'member_avatars' => $group->members->take(4)->map(fn (GroupChatMember $m) => $m->user?->avatar
                ? asset(User::AVATAR_UPLOAD_PATH . $m->user->avatar)
                : asset('assets/images/img.png'))->values(),
            'last_message' => $lastMessage ? [
                'message' => $lastMessage->message,
                'message_type' => $lastMessage->message_type,
                'sender_name' => $lastMessage->senderName(),
                'sender_id' => $lastMessage->sender_id,
                'created_at' => $lastMessage->created_at?->toIso8601String(),
            ] : null,
            'last_message_at' => $group->last_message_at?->toIso8601String(),
            'created_at' => $group->created_at?->toIso8601String(),
        ];
    }

    private function transformMessage(GroupChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'group_chat_id' => $message->group_chat_id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->senderName(),
            'sender_avatar' => $message->senderAvatar(),
            'message_type' => $message->message_type,
            'message' => $message->message,
            'media_url' => GroupChatMessage::normalizeMediaUrl($message->media_path, $message->media_url),
            'media_path' => $message->media_path,
            'file_name' => $message->file_name,
            'media_width' => $message->media_width,
            'media_height' => $message->media_height,
            'duration_seconds' => $message->duration_seconds,
            'latitude' => $message->latitude,
            'longitude' => $message->longitude,
            'map_url' => $message->map_url,
            'created_at' => $message->created_at?->toIso8601String(),
        ];
    }

    private function generateGroupCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (GroupChat::where('group_code', $code)->exists());

        return $code;
    }

    private function normalizeMessageType(Request $request): string
    {
        $messageType = strtolower((string) $request->input('message_type', ''));

        return match ($messageType) {
            'image' => GroupChatMessage::TYPE_IMAGE,
            'audio', 'voice' => GroupChatMessage::TYPE_VOICE,
            'document', 'file' => GroupChatMessage::TYPE_FILE,
            'location' => GroupChatMessage::TYPE_LOCATION,
            default => GroupChatMessage::TYPE_TEXT,
        };
    }

    private function storeAttachment(\Illuminate\Http\UploadedFile $file): array
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

            return [GroupChatMessage::TYPE_IMAGE, Storage::disk('public')->url($path), $path, $originalName];
        }

        $directory = in_array($extension, $documentExtensions, true) ? 'chat/files' : 'chat/voice';
        $messageType = in_array($extension, $documentExtensions, true) ? 'file' : GroupChatMessage::TYPE_VOICE;
        $path = $messageType === GroupChatMessage::TYPE_VOICE
            ? GroupChatMessage::storeVoiceUpload($file, $directory)
            : $file->store($directory, 'public');

        return [
            $messageType,
            GroupChatMessage::normalizeMediaUrl($path, null),
            $path,
            $originalName,
        ];
    }

    private function sendGroupPushNotification(GroupChat $group, GroupChatMessage $message, int $senderId): void
    {
        try {
            $memberIds = GroupChatMember::query()
                ->where('group_chat_id', $group->id)
                ->where('user_id', '!=', $senderId)
                ->pluck('user_id')
                ->toArray();

            if ($memberIds === []) {
                return;
            }

            $authUser = auth()->user();
            $notificationBody = match ($message->message_type) {
                GroupChatMessage::TYPE_IMAGE => 'Sent a photo',
                GroupChatMessage::TYPE_VOICE => 'Sent a voice message',
                GroupChatMessage::TYPE_LOCATION => 'Sent a location',
                GroupChatMessage::TYPE_FILE => 'Sent a file',
                default => $message->message,
            };

            SMPushHelper::sendPushNotification(
                title: $group->name,
                conversation_id: 'group_' . $group->id,
                message: $authUser?->name . ': ' . ($notificationBody ?? ''),
                type: 'group_chat',
                usernames: $memberIds,
                project_id: '',
                chatMessageType: $message->message_type,
                media_url: $message->media_url ?? '',
                latitude: $message->latitude,
                longitude: $message->longitude,
                map_url: $message->map_url ?? '',
            );
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
