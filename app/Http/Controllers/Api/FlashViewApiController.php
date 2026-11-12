<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlashView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashViewApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $items = FlashView::active()->orderByDesc('priority')->orderByDesc('id')->get()
            ->filter(fn ($flash) => $flash->visibleTo($user))
            ->filter(fn ($flash) => $flash->display_policy !== 'once' || !DB::table('flash_view_receipts')
                ->where('flash_view_id', $flash->id)->where('user_id', $user->id)->where('revision', $flash->revision)->exists())
            ->take(10)->values();
        return response()->json(['data' => $items->map(fn ($flash) => app(\App\Services\Community\UploadedMediaUrl::class)->payload($flash->toArray()))]);
    }

    public function acknowledge(Request $request, FlashView $flashView)
    {
        $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        abort_unless($flashView->visibleTo($request->user()) && FlashView::active()->whereKey($flashView->id)->exists(), 404);
        abort_unless((int) $request->revision === (int) $flashView->revision, 409, 'This popup has changed. Refresh to see its latest version.');
        DB::table('flash_view_receipts')->insertOrIgnore([
            'flash_view_id' => $flashView->id, 'user_id' => $request->user()->id,
            'revision' => $flashView->revision, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['message' => 'Popup dismissed.']);
    }
}
