<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialStory;
use App\Models\SocialStoryView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SocialStoryApiController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->company($request);
        $stories = SocialStory::query()
            ->active()
            ->where('company_id', $companyId)
            ->with([
                'author:id,name,avatar,department_id',
                'author.department:id,dept_name',
                'views' => fn ($query) => $query->where('user_id', $request->user()->id),
            ])
            ->latest('id')
            ->get();

        $groups = $stories->groupBy('user_id')->map(function ($items) use ($request) {
            $first = $items->first();
            return [
                'author' => $this->author($first->author),
                'is_mine' => (int) $first->user_id === (int) $request->user()->id,
                'has_unseen' => $items->contains(fn ($story) => $story->views->isEmpty()),
                'stories' => $items->sortBy('id')->values()->map(fn ($story) => $this->storyData($story, $request))->values(),
            ];
        })->sortByDesc('is_mine')->values();

        return response()->json(['data' => $groups]);
    }

    public function store(Request $request)
    {
        $companyId = $this->company($request);
        $data = $request->validate([
            'media_type' => ['required', Rule::in(['text', 'image', 'video'])],
            'caption' => ['nullable', 'string', 'max:1000'],
            'media_url' => ['nullable', 'string', 'max:4096'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:48'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'aspect_ratio' => ['nullable', 'numeric', 'min:0.2', 'max:4'],
            'media' => ['nullable', 'file', 'max:102400', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm'],
            'thumbnail' => ['nullable', 'file', 'image', 'max:8192'],
        ]);

        $type = $data['media_type'];
        $caption = trim((string) ($data['caption'] ?? '')) ?: null;
        $url = trim((string) ($data['media_url'] ?? '')) ?: null;
        $file = $request->file('media');
        $thumbnailUrl = null;
        if ($type === 'text' && !$caption) {
            return response()->json(['errors' => ['caption' => ['Write something for your story.']]], 422);
        }
        if ($file) {
            $mime = (string) $file->getMimeType();
            $isImage = str_starts_with($mime, 'image/');
            $isVideo = str_starts_with($mime, 'video/');
            if (($type === 'image' && !$isImage) || ($type === 'video' && !$isVideo) || ($type === 'text')) {
                return response()->json(['errors' => ['media' => ['The uploaded file does not match the selected story type.']]], 422);
            }
            if ($isImage && $file->getSize() > 10 * 1024 * 1024) {
                return response()->json(['errors' => ['media' => ['Images must be 10 MB or smaller.']]], 422);
            }
            $path = $file->store('stories', 'public');
            if ($type === 'video') app(\App\Services\Community\AdaptiveVideo::class)->enqueue($path);
            $url = url(Storage::disk('public')->url($path));
            if ($type === 'video' && $request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('stories/thumbnails', 'public');
                $thumbnailUrl = url(Storage::disk('public')->url($thumbnailPath));
            }
        } elseif ($type !== 'text') {
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with(strtolower($url), 'https://')) {
                return response()->json(['errors' => ['media_url' => ['Upload an image/video or enter a valid HTTPS URL.']]], 422);
            }
        } else {
            $url = null;
        }

        $story = SocialStory::create([
            'user_id' => $request->user()->id,
            'company_id' => $companyId,
            'media_type' => $type,
            'media_url' => $url,
            'thumbnail_url' => $thumbnailUrl,
            'duration_seconds' => $type === 'video' ? ($data['duration_seconds'] ?? null) : null,
            'aspect_ratio' => $type === 'video' ? ($data['aspect_ratio'] ?? null) : null,
            'caption' => $caption,
            'expires_at' => now()->addHours((int) ($data['duration_hours'] ?? 24)),
        ]);

        return response()->json(['data' => $this->storyData($story->load('author.department'), $request)], 201);
    }

    public function view(Request $request, SocialStory $story)
    {
        $this->scope($request, $story, true);
        SocialStoryView::updateOrCreate(
            ['social_story_id' => $story->id, 'user_id' => $request->user()->id],
            ['viewed_at' => now()],
        );
        return response()->json(['viewed' => true]);
    }

    public function destroy(Request $request, SocialStory $story)
    {
        $this->scope($request, $story, false);
        abort_unless((int) $story->user_id === (int) $request->user()->id, 403, 'You can only delete your own story.');
        $this->removeUpload($story->media_url);
        $this->removeUpload($story->thumbnail_url);
        $story->delete();
        return response()->json(['message' => 'Story deleted.']);
    }

    private function company(Request $request): int
    {
        abort_if($request->user()->company_id === null, 403, 'Your employee account has no company assigned.');
        return (int) $request->user()->company_id;
    }

    private function scope(Request $request, SocialStory $story, bool $active): void
    {
        abort_unless((int) $story->company_id === $this->company($request), 404);
        if ($active) abort_unless($story->expires_at?->isFuture(), 404);
    }

    private function storyData(SocialStory $story, Request $request): array
    {
        $own = (int) $story->user_id === (int) $request->user()->id;
        return [
            'id' => $story->id,
            'media_type' => $story->media_type,
            'media_url' => app(\App\Services\Community\UploadedMediaUrl::class)->resolve($story->media_url),
            'playback_url' => $story->playback_url,
            'thumbnail_url' => app(\App\Services\Community\UploadedMediaUrl::class)->resolve($story->thumbnail_url),
            'duration_seconds' => $story->duration_seconds,
            'aspect_ratio' => $story->aspect_ratio,
            'caption' => $story->caption,
            'created_at' => $story->created_at?->toIso8601String(),
            'expires_at' => $story->expires_at?->toIso8601String(),
            'is_mine' => $own,
            'can_delete' => $own && Gate::allows('social_feed.stories.delete'),
            'is_seen' => $story->relationLoaded('views') ? $story->views->isNotEmpty() : false,
            'author' => $this->author($story->author),
        ];
    }

    private function author($user): array
    {
        return [
            'id' => $user?->id,
            'name' => $user?->name ?? 'Former employee',
            'avatar' => $user?->avatar_url,
            'department' => $user?->department?->dept_name,
        ];
    }

    private function removeUpload(?string $url): void
    {
        if (!$url) return;
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || !preg_match('~/storage/(stories/[A-Za-z0-9._/-]+)$~', $path, $match)) return;
        Storage::disk('public')->delete($match[1]);
            app(\App\Services\Community\AdaptiveVideo::class)->remove($match[1]);
    }
}
