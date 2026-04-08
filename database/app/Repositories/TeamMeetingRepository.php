<?php

namespace App\Repositories;

use App\Models\TeamMeeting;
use App\Traits\ImageService;
use Illuminate\Support\Carbon;

class TeamMeetingRepository
{
    use ImageService;

    public function getAllCompanyTeamMeetings($filterParameters,$select=['*'],$with=[])
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;

        return TeamMeeting::select($select)->with($with)
            ->when(isset($filterParameters['participator']), function($query) use ($filterParameters){
                $query->whereHas('teamMeetingParticipator.participator',function($query) use ($filterParameters){
                    $query->where('name', 'like', '%' . $filterParameters['participator'] . '%');
                });

            })
            ->when(isset($filterParameters['meeting_from']) || isset($filterParameters['meeting_to']), function($query) use ($filterParameters){
                if(isset($filterParameters['meeting_from'])){
                    $query->whereDate('meeting_date','>=',date('Y-m-d',strtotime($filterParameters['meeting_from'])));
                }
                if(isset($filterParameters['meeting_to'])){
                    $query->whereDate('meeting_date','<=',date('Y-m-d',strtotime($filterParameters['meeting_to'])));
                }
            })
            ->where('company_id',$filterParameters['company_id'])
            ->when(isset($branchId) && $authUserId != 1, function ($query) use ($branchId) {
                $query->whereHas('createdBy',function($subQuery) use ($branchId){
                    $subQuery->where('branch_id', $branchId);
                });

            })
            ->orderBy('meeting_published_at','Desc')
            ->paginate(TeamMeeting::RECORDS_PER_PAGE);
    }

    public function getAllAssignedEmployeeTeamMeetings($perPage,$select=['*'])
    {
        return TeamMeeting::select($select)
            ->whereHas('teamMeetingParticipator',function($query){
                $query->where('meeting_participator_id',getAuthUserCode());
            })

            ->where('meeting_published_at','>=',Carbon::now()->subMonth(12))
            ->orderBy('meeting_published_at','Desc')
            ->paginate($perPage);
    }

    public function findTeamMeetingDetailById($id,$select=['*'],$with=[])
    {
        return TeamMeeting::select($select)->with($with)->where('id',$id)->first();
    }

    public function store($validatedData)
    {
        if(isset($validatedData['image'])){
            $validatedData['image'] = $this->storeImage($validatedData['image'], TeamMeeting::UPLOAD_PATH);
        }
        return TeamMeeting::create($validatedData)->fresh();
    }

    public function update($teamMeetingDetail,$validatedData)
    {
        if (isset($validatedData['image'])) {
            $this->removeImage(TeamMeeting::UPLOAD_PATH, $teamMeetingDetail['image']);
            $validatedData['image'] = $this->storeImage($validatedData['image'], TeamMeeting::UPLOAD_PATH);
        }
        $teamMeetingDetail->update($validatedData);
        return $teamMeetingDetail;
    }

    public function delete($teamMeetingDetail)
    {
        if($teamMeetingDetail['image']){
            $this->removeImage(TeamMeeting::UPLOAD_PATH, $teamMeetingDetail['image']);
        }
        return $teamMeetingDetail->delete();
    }

    public function deleteMeetingImage($teamMeetingDetail)
    {
        if (isset($teamMeetingDetail['image'])) {
            $this->removeImage(TeamMeeting::UPLOAD_PATH, $teamMeetingDetail['image']);
        }
        return $teamMeetingDetail->update(['image' => null ]);
    }

    public function createManyTeamMeetingParticipator(TeamMeeting $teamMeetingDetail,$validatedData)
    {
        return $teamMeetingDetail->teamMeetingParticipator()->createMany($validatedData);
    }

    public function deleteTeamMeetingParticipatorsDetail($teamMeetingDetail)
    {
        $teamMeetingDetail->teamMeetingParticipator()->delete();
        return true;
    }

}
