<?php

namespace App\Services\Notice;

use App\Helpers\AppHelper;
use App\Repositories\NoticeRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class NoticeService
{
    private NoticeRepository $noticeRepo;

    public function __construct(NoticeRepository $noticeRepo)
    {
        $this->noticeRepo = $noticeRepo;
    }

    public function getAllCompanyNotices($filterParameters, $select = ['*'], $with = [])
    {
        if (AppHelper::ifDateInBsEnabled()) {
            $filterParameters['publish_date_from'] = isset($filterParameters['publish_date_from']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['publish_date_from']) : null;
            $filterParameters['publish_date_to'] = isset($filterParameters['publish_date_to']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['publish_date_to']) : null;
        }
        return $this->noticeRepo->getAllCompanyNotices($filterParameters, $select, $with);
    }

    public function getAllReceivedNoticeDetail($perPage,$select=['*'])
    {
        return $this->noticeRepo->getAllEmployeeNotices($perPage,$select);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function store($validatedData)
    {
        try {
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            DB::beginTransaction();
            $validatedData['branch_id'] = $validatedData['branch_ids'][0];
            $notice = $this->noticeRepo->store($validatedData);
            $notice->branches()->sync($validatedData['branch_ids']);
            if ($notice) {
                $this->createManyNoticeReceiver($notice, $validatedData);
            }
            DB::commit();
            return $notice;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function createManyNoticeReceiver($noticeDetail, $validatedData)
    {
        $this->noticeRepo->createManyNoticeReceiver($noticeDetail, $validatedData['receiver']);
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function changeNoticeStatus($id): bool
    {
        try {
            $noticeDetail = $this->findOrFailNoticeDetailById($id);
            DB::beginTransaction();
            $this->noticeRepo->toggleStatus($noticeDetail);
            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param $id
     * @param $select
     * @param $with
     * @return mixed
     * @throws Exception
     */
    public function findOrFailNoticeDetailById($id, $select = ['*'], $with = [])
    {
        $noticeDetail = $this->noticeRepo->findNoticeDetailById($id, $select, $with);
        if (!$noticeDetail) {
            throw new Exception(__('message.notice_not_found'), 400);
        }
        return $noticeDetail;
    }

    /**
     * @param $noticeDetail
     * @param $validatedData
     * @return bool
     * @throws Exception
     */
    public function update($noticeDetail, $validatedData): bool
    {
        try {
            DB::beginTransaction();
            $validatedData['branch_id'] = $validatedData['branch_ids'][0];
            $notice = $this->noticeRepo->update($noticeDetail, $validatedData);
            $notice->branches()->sync($validatedData['branch_ids']);
            if ($notice) {
                $deleteReceiverDetail = $this->noticeRepo->deleteNoticeReceiversDetail($notice);
                if ($deleteReceiverDetail) {
                    $this->createManyNoticeReceiver($notice, $validatedData);
                }
            }
            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function updatePublishDateAndStatus($noticeDetail, $validatedData)
    {
        return $this->noticeRepo->update($noticeDetail, $validatedData);

    }

    /**
     * @param $id
     * @return void
     * @throws Exception
     */
    public function deleteNotice($id)
    {
        try {
            DB::beginTransaction();
            $noticeDetail = $this->findOrFailNoticeDetailById($id);
            $this->noticeRepo->delete($noticeDetail);
            DB::commit();
            return;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function getRecentNoticeForEmployee($userId)
    {
        return $this->noticeRepo->getRecentNoticeForEmployee($userId);
    }

}

