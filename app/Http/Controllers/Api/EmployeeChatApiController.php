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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class EmployeeChatApiController extends Controller
{
    public function access(): JsonResponse
    {
        $user = auth()->user();
        $scope = MobileChatHelper::getScope();
        $conversation = ChatConversation::firstOrCreate(['user_id' => $user->id]);
        $adminList = Admin::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'avatar']);
        $adminContact = $this->buildAdminContact($conversation, $adminList->first());

        return AppHelper::sendSuccessResponse('Mobile chat access loaded successfully.', [
            'scope' => $scope,
            'scope_label' => MobileChatHelper::scopeOptions()[$scope] ?? $scope,
            'employee_directory_enabled' => $scope === MobileChatHelper::MODE_ALL_EMPLOYEES,
            'admin_chat_enabled' => true,
            'admin_contact' => $adminContact,
            'admins' => $adminList->map(fn (Admin $admin) => $this->transformAdminDirectoryEntry($admin))->values(),
        ]);
    }

    public function contacts(): JsonResponse
    {
        $scope = MobileChatHelper::getScope();
        $conversation = ChatConversation::firstOrCreate(['user_id' => auth()->id()]);
        $adminContact = $this->buildAdminContact(
            $conversation,
            Admin::query()
                ->where('is_active', 1)
                ->orderBy('name')
                ->first(['id', 'name', 'username', 'avatar'])
        );

        if ($scope !== MobileChatHelper::MODE_ALL_EMPLOYEES) {
            return AppHelper::sendSuccessResponse('Mobile chat contacts loaded successfully.', [
                'scope' => $scope,
                'admin_contact' => $adminContact,
                'pinned_contacts' => [$adminContact],
                'contacts' => $this->getAdminDirectoryEntries(),
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
            ->concat($this->getAdminDirectoryEntries())
            ->values();

        return AppHelper::sendSuccessResponse('Mobile chat contacts loaded successfully.', [
            'scope' => $scope,
            'admin_contact' => $adminContact,
            'pinned_contacts' => [$adminContact],
            'contacts' => $contacts,
        ]);
    }

    public function messages(): JsonResponse
    {
        $conversation = ChatConversation::firstOrCreate(['user_id' => auth()->id()]);
        $conversation->messages()
            ->where('sender_type', ChatMessage::SENDER_ADMIN)
            ->where('is_read_by_user', false)
            ->update(['is_read_by_user' => true]);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ChatMessage $message) => $this->transformMessage($message));

        return AppHelper::sendSuccessResponse('Mobile admin chat messages loaded successfully.', [
            'conversation_id' => (string) $conversation->id,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => ['nullable', 'string'],
            'message_type' => ['nullable', 'string', 'in:text,image,voice,location'],
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
            return AppHelper::sendErrorResponse($validator->errors()->first(), 422, $validator->errors()->toArray());
        }

        $conversation = ChatConversation::firstOrCreate(['user_id' => auth()->id()]);

        DB::transaction(function () use ($request, $conversation) {
            $messageType = $request->input('message_type', ChatMessage::TYPE_TEXT);
            $mediaUrl = $request->input('media_url');

            if ($request->hasFile('attachment')) {
                [$messageType, $mediaUrl] = $this->storeAttachment($request->file('attachment'));
            } elseif ($request->filled('latitude') && $request->filled('longitude')) {
                $messageType = ChatMessage::TYPE_LOCATION;
            }

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
                'is_read_by_admin' => false,
                'is_read_by_user' => true,
            ]);

            $conversation->update([
                'last_message_at' => $message->created_at,
            ]);
        });

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ChatMessage $message) => $this->transformMessage($message));

        return AppHelper::sendSuccessResponse('Message sent successfully.', [
            'conversation_id' => (string) $conversation->id,
            'messages' => $messages,
        ]);
    }

    private function transformMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_id' => $message->sender_id,
            'message_type' => $message->message_type,
            'message' => $message->message,
            'media_url' => $message->media_url,
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

    private function getAdminDirectoryEntries()
    {
        return Admin::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'avatar'])
            ->map(fn (Admin $admin) => $this->transformAdminDirectoryEntry($admin))
            ->values();
    }

    private function transformAdminDirectoryEntry(Admin $admin): array
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
        ];
    }

    private function buildAdminContact(ChatConversation $conversation, ?Admin $admin): array
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
            'conversation_id' => (string) $conversation->id,
        ];
    }

    private function storeAttachment(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $imageMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

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

        $path = $file->store('chat/voice', 'public');

        return [ChatMessage::TYPE_VOICE, Storage::disk('public')->url($path)];
    }
}
