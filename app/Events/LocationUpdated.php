<?php

namespace App\Events;

use App\Models\UserLocation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public UserLocation $location)
    {
        $this->location->loadMissing([
            'user:id,name,email,phone,avatar,branch_id,department_id',
            'user.branch:id,name',
            'user.department:id,dept_name',
        ]);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.locations');
    }

    public function broadcastAs(): string
    {
        return 'LocationUpdated';
    }

    public function broadcastWith(): array
    {
        $user = $this->location->user;

        return [
            'user_id' => $this->location->user_id,
            'employee_id' => $this->location->user_id,
            'name' => $user?->name,
            'email' => $user?->email,
            'phone' => $user?->phone,
            'avatar' => $user?->avatar
                ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $user->avatar)
                : asset('assets/images/img.png'),
            'branch' => $user?->branch?->name,
            'department' => $user?->department?->dept_name,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'accuracy' => $this->location->accuracy,
            'battery_level' => $this->location->battery_level,
            'device_name' => $this->location->device_name,
            'last_updated_at' => $this->location->updated_at?->toIso8601String(),
            'last_updated_human' => $this->location->updated_at?->diffForHumans(),
            'has_location' => true,
            'map_url' => 'https://www.google.com/maps?q=' . $this->location->latitude . ',' . $this->location->longitude,
        ];
    }
}
