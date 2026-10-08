<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Notice\NoticeCollection;
use App\Resources\Notice\NoticeResource;
use App\Services\Notice\NoticeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeApiController extends Controller
{
    private NoticeService $noticeService;

    public function __construct(NoticeService $noticeService)
    {
        $this->noticeService = $noticeService;
    }

    public function getAllRecentlyReceivedNotice(Request $request): JsonResponse|NoticeCollection
    {
        try {
            $perPage = $request->get('per_page') ?? 20;
            $notice = $this->noticeService->getAllReceivedNoticeDetail($perPage);
            return new NoticeCollection($notice);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getReceivedNoticeDetail($id): JsonResponse|NoticeResource
    {
        try {
            $notice = $this->noticeService->findOrFailNoticeDetailById($id);

            $isReceiver = $notice->noticeReceiversDetail()
                ->where('notice_receiver_id', getAuthUserCode())
                ->exists();

            if (!$isReceiver) {
                return AppHelper::sendErrorResponse(__('message.notice_not_found'), 404);
            }

            return new NoticeResource($notice);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }
}
