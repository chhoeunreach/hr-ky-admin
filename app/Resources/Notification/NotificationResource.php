<?php

namespace App\Resources\Notification;

use App\Helpers\AppHelper;
use App\Models\Notice;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        $descriptionHtml = $this->description;

        if ($this->type === 'notice' && $this->notification_for_id) {
            $descriptionHtml = Notice::query()
                ->where('id', $this->notification_for_id)
                ->value('description') ?? $descriptionHtml;
        }

        return [
            'id' => $this->id,
            'notification_title' => ucfirst($this->title),
            'description' => removeHtmlTags($this->description),
            'description_html' => $descriptionHtml,
            'notification_published_date' => $this->notification_publish_date,
            'published_date_nepali' => AppHelper::formatDateForView($this->notification_publish_date). ', ' .date('h:i A',strtotime($this->notification_publish_date)),
            'type' => $this->type,
            'notification_for_id' => $this->notification_for_id ?? '',
            'is_read' => (bool) ($this->notifiedUsers->where('user_id', getAuthUserCode())->first()?->is_seen ?? false),
        ];
    }
}
