<?php

namespace App\Services\Community;

use App\Helpers\SMPush\SMPushNotification;
use App\Models\HomeHighlight;
use App\Models\User;
use Illuminate\Support\Str;

class HomeHighlightAlert
{
    public function send(HomeHighlight $item): array
    {
        $query = User::withoutGlobalScopes()
            ->where('company_id', $item->company_id)
            ->whereNull('deleted_at')
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->whereNotNull('fcm_token')->where('fcm_token', '!=', '')
            ->whereHas('role', fn ($role) => $role
                ->whereIn('slug', ['admin', 'super-admin', 'supper-admin'])
                ->orWhere(fn ($role) => $role
                    ->whereHas('permission', fn ($permission) => $permission->where('permission_key', 'mobile.menu.home-highlights'))
                    ->whereHas('permission', fn ($permission) => $permission->where('permission_key', 'home_highlight.view'))));

        foreach (['role_ids' => 'role_id', 'branch_ids' => 'branch_id',
            'department_ids' => 'department_id', 'user_ids' => 'id'] as $selection => $column) {
            if ($item->$selection) {
                $query->whereIn($column, $item->$selection);
            }
        }

        $recipients = $query->pluck('fcm_token', 'id')->all();
        if (!$recipients) {
            return ['eligible' => 0, 'sent' => 0, 'failed' => 0, 'error' => null];
        }

        return ['eligible' => count($recipients)] + $this->push($item, $recipients);
    }

    protected function push(HomeHighlight $item, array $recipients): array
    {
        $message = trim(html_entity_decode(strip_tags($item->body ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return SMPushNotification::smSend(
            title: $item->title,
            message: Str::limit($message ?: 'You have a new Home Highlight.', 240),
            data: ['type' => 'home_highlight', 'home_highlight_id' => (string) $item->id],
            recipients: $recipients,
            highPriority: true,
        );
    }
}
