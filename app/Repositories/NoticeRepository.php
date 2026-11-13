<?php

namespace App\Repositories;

use App\Models\Notice;
use Illuminate\Support\Carbon;

class NoticeRepository{


    public function getAllCompanyNotices($filterParameters,$select=['*'],$with=[])
    {
        $query = Notice::select($select)->with($with)
            ->when(isset($filterParameters['notice_receiver']), function ($query) use ($filterParameters) {
                $query->whereHas('noticeReceiversDetail',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('notice_receiver_id', $filterParameters['notice_receiver']);
                });
            })
            ->when(isset($filterParameters['publish_date_from']), function($query) use ($filterParameters){
                $query->whereDate('notice_publish_date','>=',$filterParameters['publish_date_from']);
            })
            ->when(isset($filterParameters['publish_date_to']), function($query) use ($filterParameters){
                $query->whereDate('notice_publish_date','<=',$filterParameters['publish_date_to']);
            })
            ->when(!empty($filterParameters['branch_id']), fn ($query) => $query->where(fn ($branches) => $branches
                ->where('branch_id', $filterParameters['branch_id'])
                ->orWhereHas('branches', fn ($branch) => $branch->where('branches.id', $filterParameters['branch_id']))))
            ->when(isset($filterParameters['is_active']) && $filterParameters['is_active'] !== '', fn ($query) => $query->where('is_active', $filterParameters['is_active']))
            ->when(!empty($filterParameters['search']), function ($query) use ($filterParameters) {
                $search = '%'.$filterParameters['search'].'%';
                $query->where(fn ($q) => $q->where('title', 'like', $search)->orWhere('description', 'like', $search)
                    ->orWhereHas('branches', fn ($b) => $b->where('name', 'like', $search))
                    ->orWhereHas('noticeReceiversDetail.employee', fn ($u) => $u->where('name', 'like', $search)));
            })
;
        $sorts = ['title' => 'title', 'published' => 'notice_publish_date', 'status' => 'is_active'];
        $sort = $sorts[$filterParameters['sort'] ?? 'published'] ?? 'notice_publish_date';
        $direction = ($filterParameters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction)->orderByDesc('id');
        $size = (string) ($filterParameters['per_page'] ?? '25');
        if (!in_array($size, ['25', '50', '100', '200', 'all'], true)) $size = '25';
        return $query->paginate($size === 'all' ? max(1, (clone $query)->count()) : (int) $size);
    }

    public function getAllEmployeeNotices($perPage,$select=['*'])
    {
        return Notice::select($select)
            ->whereHas('noticeReceiversDetail',function($query){
                $query->where('notice_receiver_id',getAuthUserCode());
            })
            ->where('notice_publish_date','>=',Carbon::now()->subMonth(12))
            ->orderBy('notice_publish_date','Desc')
            ->paginate($perPage);
    }

    public function findNoticeDetailById($id,$select=['*'],$with=[])
    {
        return Notice::select($select)->with($with)->where('id',$id)->first();
    }

    public function store($validatedData)
    {
        return Notice::create($validatedData)->fresh();
    }

    public function update($noticeDetail,$validatedData)
    {
         $noticeDetail->update($validatedData);
         return $noticeDetail;
    }

    public function delete($noticeDetail)
    {
        return $noticeDetail->delete();
    }

    public function toggleStatus($noticeDetail)
    {
        return $noticeDetail->update([
            'is_active' => !$noticeDetail->is_active,
        ]);
    }

    public function createManyNoticeReceiver(Notice $noticeDetail,$validatedData)
    {
        return $noticeDetail->noticeReceiversDetail()->createMany($validatedData);
    }

    public function deleteNoticeReceiversDetail($noticeDetail)
    {
        $noticeDetail->noticeReceiversDetail()->delete();
        return true;
    }

    public function getRecentNoticeForEmployee($userId)
    {
        return Notice::withoutGlobalScope('branch')
            ->whereHas('noticeReceiversDetail', function($query) use ($userId) {
                $query->where('notice_receiver_id', $userId);
            })
            ->where('is_active', 1)
            ->whereNotNull('notice_publish_date')
            ->where('notice_publish_date', '<=', Carbon::now())
            ->orderByDesc('notice_publish_date')
            ->first();
    }

}

