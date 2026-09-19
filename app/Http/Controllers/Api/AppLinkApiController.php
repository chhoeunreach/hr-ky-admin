<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\AppLink;
use App\Repositories\AppLinkRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppLinkApiController extends Controller
{
    public function __construct(
        protected AppLinkRepository $appLinkRepo
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $links = $this->appLinkRepo->getActiveLinks();

            $data = $links->map(function ($link) {
                return [
                    'id' => $link->id,
                    'name' => $link->name,
                    'link_type' => $link->link_type,
                    'url' => $link->url,
                    'image' => $link->image_url,
                    'description' => $link->description,
                    'order' => $link->order,
                    'status' => (bool)$link->status,
                    'created_at' => $link->created_at?->toIso8601String(),
                ];
            });

            return AppHelper::sendSuccessResponse('Link list fetched successfully', $data);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }
}
