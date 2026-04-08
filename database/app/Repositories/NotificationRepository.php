<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;

class NotificationRepository
{
    const ACTIVE = 1;
    const INACTIVE = 0;
    const SEEN = 1;
    const UNSEEN = 0;

    public function getAllCompanyNotifications($filterParameters,$select=['*'],$with=[])
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;

        return Notification::select($select)->with($with)
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->where('type',$filterParameters['type']);
            })
            ->when(isset($branchId) && $authUserId != 1, function($query) use ($branchId){
                $query->whereHas('createdBy',function($query) use ($branchId){
                    $query->where('branch_id', $branchId);
                });
            })
            ->orderBy('notification_publish_date','Desc')
            ->paginate(Notification::RECORDS_PER_PAGE);
    }

    public function getAllCompanyRecentActiveNotification($perPage,$select=['*'])
    {
        return Notification::with(['notifiedUsers' => function($query){
                    $query->where('user_id',getAuthUserCode());
                }
            ])
            ->select($select)
            ->where('is_active',self::ACTIVE)
            ->where('type','general')
            ->whereNotNull('notification_publish_date')
            ->orWhereHas('notifiedUsers',function($query){
                $query->where('user_id',getAuthUserCode());
            })
            ->where('notification_publish_date','>=',Carbon::now()->subDays(30))
            ->orderBy('notification_publish_date','Desc')
            ->paginate($perPage);
    }

    public function findNotificationDetailById($id,$select=['*'],$with=[])
    {
        return Notification::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function getNotificationForNavBar($select)
    {
        return Notification::select($select)
            ->where('company_id',AppHelper::getAuthUserCompanyId() )
            ->where('is_active',self::ACTIVE)
            ->latest()
            ->take(5)
            ->get();
    }

    public function store($validatedData)
    {
        return Notification::create($validatedData)->fresh();
    }

    public function update($notificationDetail,$validatedData)
    {
        return $notificationDetail->update($validatedData);
    }

    public function delete($notificationDetail)
    {
        return $notificationDetail->delete();
    }

    public function toggleStatus($id)
    {
        $notificationDetail = $this->findNotificationDetailById($id);
        return $notificationDetail->update([
            'is_active' => !$notificationDetail->is_active,
        ]);
    }

    public function notifyUser($notificationDetail,$usersArray)
    {
        return $notificationDetail->notifiedUsers()->createMany($usersArray);
    }

    public function findUserNotificationDetailById($id,$select)
    {
        return UserNotification::select($select)
            ->where('id',$id)
            ->first();
    }

    public function changeUserNotificationToSeen($userNotificationDetail)
    {
        return $userNotificationDetail->update([
            'is_seen' => self::SEEN
        ]);
    }

}
