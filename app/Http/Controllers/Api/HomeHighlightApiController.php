<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeHighlight;
use App\Services\Community\UploadedMediaUrl;
use Illuminate\Http\Request;

class HomeHighlightApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $items = HomeHighlight::query()
            ->forCompany($user->company_id)
            ->active()
            ->forAudience($user)
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $items->map(fn ($item) => $this->payload($item)),
            'display_settings' => \App\Models\HomeHighlightDisplaySetting::forCompany($user->company_id)->payload(),
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = HomeHighlight::query()
            ->forCompany($user->company_id)
            ->published()
            ->forAudience($user)
            ->orderByRaw('COALESCE(starts_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => collect($paginator->items())->map(fn ($item) => $this->payload($item))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, HomeHighlight $homeHighlight)
    {
        $user = $request->user();
        abort_unless($homeHighlight->company_id === null || (int) $homeHighlight->company_id === (int) $user->company_id, 404);
        abort_unless($homeHighlight->is_active && (!$homeHighlight->starts_at || $homeHighlight->starts_at->lte(now())), 404);
        abort_unless($homeHighlight->visibleTo($user), 404);

        return response()->json(['data' => $this->payload($homeHighlight)]);
    }

    private function payload(HomeHighlight $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'body' => $item->body,
            'image_url' => app(UploadedMediaUrl::class)->resolve($item->image_url),
            'link_url' => $item->link_url,
            'link_label' => $item->link_label,
            'priority' => $item->priority,
            'starts_at' => $item->starts_at?->toIso8601String(),
            'ends_at' => $item->ends_at?->toIso8601String(),
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
