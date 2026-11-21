<?php

namespace App\Resources\Notification;

use App\Helpers\AppHelper;
use App\Models\Notice;
use App\Models\Notification;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        $isUpdate = $this->type === Notification::APP_UPDATE;
        $descriptionHtml = $isUpdate ? nl2br(e($this->description)) : $this->description;

        if ($this->type === 'notice' && $this->notification_for_id) {
            $descriptionHtml = Notice::query()
                ->where('id', $this->notification_for_id)
                ->value('description') ?? $descriptionHtml;
        }

        return [
            'id' => $this->id,
            'notification_title' => $isUpdate ? $this->title : ucfirst($this->title),
            'description' => $isUpdate ? $this->description : removeHtmlTags($this->description),
            'description_html' => $descriptionHtml,
            'notification_published_date' => $this->notification_publish_date,
            'published_date_nepali' => AppHelper::formatDateForView($this->notification_publish_date). ', ' .date('h:i A',strtotime($this->notification_publish_date)),
            'type' => $this->type,
            'notification_for_id' => $this->notification_for_id ?? '',
            'is_read' => (bool) ($this->notifiedUsers->where('user_id', getAuthUserCode())->first()?->is_seen ?? false),
            'app_update' => $this->when($isUpdate, $this->app_update),
        ];
    }
}
