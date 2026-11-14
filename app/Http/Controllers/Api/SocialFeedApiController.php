<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialComment;
use App\Models\SocialNotification;
use App\Models\SocialPost;
use App\Models\SocialPostReport;
use App\Models\SocialPostSave;
use App\Models\SocialPostPreference;
use App\Models\SocialPoll;
use App\Models\SocialPollVote;
use App\Services\Community\CommunityMedia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SocialFeedApiController extends Controller
{
    private ?array $reactionSummaries = null;
    private array $myReactions = [];
    private array $savedIds = [];
    private array $preferences = [];
    private bool $summaryHydrated = false;
    public const REACTIONS = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

    private function company(Request $request): int
    {
        abort_if($request->user()->company_id === null, 403, 'Your employee account has no company assigned.');
        return (int) $request->user()->company_id;
    }

    private function scoped(Request $request, SocialPost $post, bool $checkVisibility = true): void
    {
        $this->company($request);
        abort_unless((string) $post->company_id === (string) $request->user()->company_id, 404);
        if ($checkVisibility) abort_unless($post->visibleTo($request->user()), 404);
    }

    private function owner(Request $request, SocialPost $post): void
    {
        $this->scoped($request, $post, false);
        abort_unless((int) $post->user_id === (int) $request->user()->id, 403, 'You can only change your own post.');
    }

    private function paginator($query, Request $request, int $perPage = 20): LengthAwarePaginatorContract
    {
        return $query->paginate($perPage, ['*'], 'page', max(1, (int) $request->integer('page', 1)));
    }

    private function hydrateSummary($posts, Request $request): void
    {
        $collection = $posts instanceof LengthAwarePaginatorContract ? $posts->getCollection() : collect($posts);
        $ids = $collection->pluck('id')->merge($collection->pluck('original_post_id')->filter())->unique()->values();
        $this->reactionSummaries = [];
        $this->myReactions = [];
        $this->savedIds = [];
        $this->preferences = [];
        $this->summaryHydrated = true;
        if ($ids->isEmpty()) return;
        foreach (DB::table('social_reactions')->whereIn('social_post_id', $ids)->selectRaw('social_post_id, reaction, COUNT(*) as total')->groupBy('social_post_id', 'reaction')->get() as $reaction) {
            $this->reactionSummaries[$reaction->social_post_id][$reaction->reaction] = (int) $reaction->total;
        }
        $this->myReactions = DB::table('social_reactions')->whereIn('social_post_id', $ids)->where('user_id', $request->user()->id)->pluck('reaction', 'social_post_id')->all();
        $this->savedIds = SocialPostSave::where('user_id', $request->user()->id)->whereIn('social_post_id', $ids)->pluck('social_post_id')->map(fn ($id) => (int) $id)->all();
        $this->preferences = SocialPostPreference::where('user_id', $request->user()->id)->whereIn('social_post_id', $ids)->get()->keyBy('social_post_id')->all();
    }

    private function feedOrder($query)
    {
        return $query->orderByRaw('CASE WHEN pinned_at IS NULL THEN 0 ELSE 1 END DESC')
            ->orderByRaw('CASE WHEN is_announcement = 1 THEN 1 ELSE 0 END DESC')
            ->orderByDesc('id');
    }

    private function encodeCursor(SocialPost $post): string
    {
        $payload = json_encode([
            'p' => $post->pinned_at === null ? 0 : 1,
            'a' => $post->is_announcement ? 1 : 0,
            'id' => (int) $post->id,
        ]);
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    private function decodeCursor($raw): ?array
    {
        if (!is_string($raw) || $raw === '') return null;
        $normalized = strtr($raw, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding) $normalized .= str_repeat('=', 4 - $padding);
        $decoded = base64_decode($normalized, true);
        if ($decoded === false) return null;
        $data = json_decode($decoded, true);
        if (!is_array($data)) return null;
        $pinned = $data['p'] ?? null;
        $announcement = $data['a'] ?? null;
        $id = $data['id'] ?? null;
        if (!in_array($pinned, [0, 1], true) || !in_array($announcement, [0, 1], true) || !is_numeric($id) || (int) $id <= 0) return null;
        return ['p' => (int) $pinned, 'a' => (int) $announcement, 'id' => (int) $id];
    }

    private function applyCursor($query, array $cursor): void
    {
        $pinned = 'CASE WHEN pinned_at IS NULL THEN 0 ELSE 1 END';
        $announcement = 'CASE WHEN is_announcement = 1 THEN 1 ELSE 0 END';
        $query->where(function ($outer) use ($cursor, $pinned, $announcement) {
            $outer->whereRaw("$pinned < ?", [$cursor['p']])
                ->orWhere(function ($samePinned) use ($cursor, $pinned, $announcement) {
                    $samePinned->whereRaw("$pinned = ?", [$cursor['p']])
                        ->where(function ($sameAnnouncement) use ($cursor, $announcement) {
                            $sameAnnouncement->whereRaw("$announcement < ?", [$cursor['a']])
                                ->orWhere(function ($sameId) use ($cursor, $announcement) {
                                    $sameId->whereRaw("$announcement = ?", [$cursor['a']])
                                        ->where('id', '<', $cursor['id']);
                                });
                        });
                });
        });
    }

    public function index(Request $request)
    {
        $this->company($request);
        $perPage = min(30, max(5, (int) $request->integer('per_page', 12)));
        $query = $this->feedOrder(SocialPost::query()->visibleTo($request->user())
            ->with(['author:id,name,avatar,department_id', 'author.department:id,dept_name', 'media', 'poll.options' => fn ($q) => $q->withCount('votes'), 'original' => fn ($q) => $q->with(['author:id,name,avatar,department_id', 'author.department:id,dept_name', 'media', 'poll.options' => fn ($inner) => $inner->withCount('votes')])->withCount(['comments', 'reactions', 'shares'])])
            ->withCount(['comments', 'reactions', 'shares']));

        $cursor = $this->decodeCursor($request->query('cursor'));
        if ($cursor !== null) {
            $this->applyCursor($query, $cursor);
            $rows = $query->limit($perPage + 1)->get();
            $hasMore = $rows->count() > $perPage;
            $rows = $rows->take($perPage)->values();
            $this->hydrateSummary($rows, $request);
            $data = $rows->map(fn ($post) => $this->postData($post, $request))->values();
            $next = $hasMore && $rows->isNotEmpty() ? $this->encodeCursor($rows->last()) : null;
            return response()->json([
                'data' => $data,
                'current_page' => max(1, (int) $request->integer('page', 1)),
                'per_page' => $perPage,
                'next_cursor' => $next,
                'has_more' => $hasMore,
                'next_page_url' => $next === null ? null : url()->current().'?'.http_build_query(['cursor' => $next, 'per_page' => $perPage]),
            ]);
        }

        $posts = $query->paginate($perPage, ['*'], 'page', max(1, (int) $request->integer('page', 1)));
        $this->hydrateSummary($posts, $request);
        $models = $posts->getCollection();
        $hasMore = $posts->hasMorePages();
        $nextCursor = $hasMore && $models->isNotEmpty() ? $this->encodeCursor($models->last()) : null;
        $posts->setCollection($models->map(fn ($post) => $this->postData($post, $request)));
        $payload = $posts->toArray();
        $payload['next_cursor'] = $nextCursor;
        $payload['has_more'] = $hasMore;
        return response()->json($payload);
    }

    public function show(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        return response()->json(['data' => $this->postData($post, $request)]);
    }

    private function audience(Request $request): array
    {
        $data = $request->validate([
            'visibility' => ['nullable', Rule::in(['company', 'department', 'employees', 'management', 'hr'])],
            'audience_department_ids' => ['nullable', 'array', 'max:50'], 'audience_department_ids.*' => ['integer', 'distinct', 'exists:departments,id'],
            'audience_user_ids' => ['nullable', 'array', 'max:200'], 'audience_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
        $visibility = $data['visibility'] ?? 'company';
        $departmentIds = array_values(array_map('intval', $data['audience_department_ids'] ?? []));
        $userIds = array_values(array_map('intval', $data['audience_user_ids'] ?? []));
        if ($visibility === 'department') abort_if(!$departmentIds, 422, 'Select at least one department.');
        if ($visibility === 'employees') abort_if(!$userIds, 422, 'Select at least one employee.');
        if ($departmentIds) {
            $valid = DB::table('departments')->where('company_id', $request->user()->company_id)->whereIn('id', $departmentIds)->count();
            abort_unless($valid === count($departmentIds), 422, 'Selected departments must belong to your company.');
        }
        if ($userIds) {
            $valid = \App\Models\User::withoutGlobalScopes()->where('company_id', $request->user()->company_id)->whereIn('id', $userIds)->count();
            abort_unless($valid === count($userIds), 422, 'Selected employees must belong to your company.');
        }
        return ['visibility' => $visibility, 'audience_department_ids' => $departmentIds ?: null, 'audience_user_ids' => $userIds ?: null];
    }

    public function store(Request $request, CommunityMedia $media)
    {
        $company = $this->company($request);
        if ($request->hasFile('media_files')) {
            if (!$request->filled('media_type')) $request->merge(['media_type' => 'text']);
            if (!$request->filled('body') && !$request->filled('media_url')) $request->merge(['body' => ' ']);
        }
        $data = $media->content($request) + $this->audience($request);
        if ($request->boolean('is_announcement')) abort_unless(Gate::allows('social_feed.moderate'), 403);
        $post = SocialPost::create($data + ['user_id' => $request->user()->id, 'company_id' => $company, 'is_announcement' => $request->boolean('is_announcement')]);
        $media->storeAdditional($request, $post);
        $this->storePoll($request, $post);
        return response()->json(['data' => $this->postData($post, $request)], 201);
    }

    public function update(Request $request, SocialPost $post, CommunityMedia $media)
    {
        $this->owner($request, $post);
        if ($post->original_post_id) $post->update($request->validate(['body' => ['nullable', 'string', 'max:10000']]) + $this->audience($request));
        else {
            $content = $media->content($request);
            if (!$request->hasFile('media')) {
                unset($content['thumbnail_url'], $content['duration_seconds'], $content['aspect_ratio']);
            }
            $post->update($content + $this->audience($request));
            $media->storeAdditional($request, $post);
        }
        return response()->json(['data' => $this->postData($post->fresh(), $request)]);
    }

    public function destroy(Request $request, SocialPost $post)
    {
        $this->owner($request, $post);
        $post->delete();
        return response()->json(['message' => 'Post deleted.']);
    }

    public function comments(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $comments = $post->comments()->whereNull('parent_id')->with(['author:id,name,avatar,department_id', 'author.department:id,dept_name'])->withCount(['replies', 'reactions'])->latest('id')->paginate(30);
        $comments->setCollection($comments->getCollection()->map(fn ($comment) => $this->commentData($comment, $request)));
        return response()->json($comments);
    }

    public function replies(Request $request, SocialPost $post, SocialComment $comment)
    {
        $this->scoped($request, $post);
        abort_unless((int) $comment->social_post_id === (int) $post->id, 404);
        $replies = $comment->replies()->with(['author:id,name,avatar,department_id', 'author.department:id,dept_name'])->withCount(['reactions'])->latest('id')->paginate(30);
        $replies->setCollection($replies->getCollection()->map(fn ($reply) => $this->commentData($reply, $request)));
        return response()->json($replies);
    }

    public function comment(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000'], 'parent_id' => ['nullable', 'integer', 'exists:social_comments,id']]);
        $parent = null;
        if (!empty($data['parent_id'])) $parent = SocialComment::whereKey($data['parent_id'])->where('social_post_id', $post->id)->firstOrFail();
        $comment = $post->comments()->create($data + ['user_id' => $request->user()->id]);
        if ((int) $post->user_id !== (int) $request->user()->id) $this->notify($post->user_id, $request->user()->id, 'comment', $post, $comment);
        if ($parent && (int) $parent->user_id !== (int) $request->user()->id) $this->notify($parent->user_id, $request->user()->id, 'reply', $post, $comment);
        return response()->json(['data' => $this->commentData($comment->fresh(), $request)], 201);
    }

    public function editComment(Request $request, SocialPost $post, SocialComment $comment)
    {
        $this->scoped($request, $post);
        abort_unless((int) $comment->social_post_id === (int) $post->id, 404);
        abort_unless((int) $comment->user_id === (int) $request->user()->id, 403, 'You can only change your own comment.');
        $comment->update($request->validate(['body' => ['required', 'string', 'max:2000']]));
        return response()->json(['data' => $this->commentData($comment->fresh(), $request)]);
    }

    public function deleteComment(Request $request, SocialPost $post, SocialComment $comment)
    {
        $this->scoped($request, $post);
        abort_unless((int) $comment->social_post_id === (int) $post->id, 404);
        abort_unless((int) $comment->user_id === (int) $request->user()->id || Gate::allows('social_feed.moderate'), 403, 'You can only delete your own comment.');
        $comment->delete();
        return response()->json(['message' => 'Comment deleted.']);
    }

    public function react(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $data = $request->validate(['reaction' => ['nullable', Rule::in(self::REACTIONS)]]);
        $previous = $post->reactions()->where('user_id', $request->user()->id)->value('reaction');
        DB::transaction(function () use ($post, $request, $data) {
            SocialPost::whereKey($post->id)->lockForUpdate()->firstOrFail();
            if (empty($data['reaction'])) $post->reactions()->where('user_id', $request->user()->id)->delete();
            else $post->reactions()->updateOrCreate(['user_id' => $request->user()->id], ['reaction' => $data['reaction']]);
        });
        if (!$previous && !empty($data['reaction']) && (int) $post->user_id !== (int) $request->user()->id) $this->notify($post->user_id, $request->user()->id, 'reaction', $post);
        return response()->json(['data' => $this->postData($post->fresh(), $request)]);
    }

    public function reactions(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $items = $post->reactions()->with('user:id,name,avatar')->latest('id')->get()->map(fn ($reaction) => [
            'reaction' => $reaction->reaction,
            'user' => $this->author($reaction->user),
        ])->values();
        return response()->json(['data' => $items]);
    }

    public function reactComment(Request $request, SocialPost $post, SocialComment $comment)
    {
        $this->scoped($request, $post);
        abort_unless((int) $comment->social_post_id === (int) $post->id, 404);
        $data = $request->validate(['reaction' => ['nullable', Rule::in(self::REACTIONS)]]);
        if (empty($data['reaction'])) $comment->reactions()->where('user_id', $request->user()->id)->delete();
        else $comment->reactions()->updateOrCreate(['user_id' => $request->user()->id], ['reaction' => $data['reaction']]);
        if (!empty($data['reaction']) && (int) $comment->user_id !== (int) $request->user()->id) $this->notify($comment->user_id, $request->user()->id, 'comment_reaction', $post, $comment);
        return response()->json(['data' => $this->commentData($comment->fresh(), $request)]);
    }

    public function share(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $data = $request->validate(['body' => ['nullable', 'string', 'max:10000']]);
        $original = $post->original_post_id ? $post->original : $post;
        abort_unless($original && $original->visibleTo($request->user()), 410, 'The original post is no longer available.');
        $share = SocialPost::create($data + ['user_id' => $request->user()->id, 'company_id' => $request->user()->company_id, 'media_type' => 'text', 'original_post_id' => $original->id, 'visibility' => $original->visibility ?: 'company', 'audience_department_ids' => $original->audience_department_ids, 'audience_user_ids' => $original->audience_user_ids]);
        if ((int) $original->user_id !== (int) $request->user()->id) $this->notify($original->user_id, $request->user()->id, 'share', $original);
        return response()->json(['data' => $this->postData($share, $request)], 201);
    }

    public function save(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        SocialPostSave::firstOrCreate(['social_post_id' => $post->id, 'user_id' => $request->user()->id]);
        return response()->json(['message' => 'Post saved.']);
    }

    public function unsave(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post, false);
        SocialPostSave::where('social_post_id', $post->id)->where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Post removed from saved posts.']);
    }

    public function saved(Request $request)
    {
        $this->company($request);
        $ids = SocialPostSave::where('user_id', $request->user()->id)->latest('created_at')->pluck('social_post_id');
        $posts = $this->paginator(SocialPost::query()->visibleTo($request->user())->whereIn('id', $ids)->with(['author:id,name,avatar,department_id', 'author.department:id,dept_name', 'media', 'original.author'])->withCount(['comments', 'reactions', 'shares'])->latest('id'), $request);
        $this->hydrateSummary($posts, $request);
        $posts->setCollection($posts->getCollection()->map(fn ($post) => $this->postData($post, $request)));
        return response()->json($posts);
    }

    public function search(Request $request)
    {
        $this->company($request);
        $term = trim((string) $request->query('q', ''));
        abort_if(mb_strlen($term) < 2, 422, 'Enter at least two characters to search.');
        $posts = $this->paginator(SocialPost::query()->visibleTo($request->user())->where(function ($q) use ($term) {
            $q->where('body', 'like', '%'.$term.'%')->orWhereHas('author', fn ($author) => $author->where('name', 'like', '%'.$term.'%'));
        })->with(['author:id,name,avatar,department_id', 'author.department:id,dept_name', 'media'])->withCount(['comments', 'reactions', 'shares'])->latest('id'), $request);
        $this->hydrateSummary($posts, $request);
        $posts->setCollection($posts->getCollection()->map(fn ($post) => $this->postData($post, $request)));
        return response()->json($posts);
    }

    public function report(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        SocialPostReport::updateOrCreate(['social_post_id' => $post->id, 'user_id' => $request->user()->id], ['reason' => $data['reason'], 'status' => 'open']);
        return response()->json(['message' => 'Report submitted for review.']);
    }

    public function pin(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post, false);
        abort_unless(Gate::allows('social_feed.pin') || Gate::allows('social_feed.moderate'), 403);
        if ($post->pinned_at) $post->forceFill(['pinned_at' => null, 'pinned_by' => null])->save();
        else $post->forceFill(['pinned_at' => now(), 'pinned_by' => $request->user()->id])->save();
        return response()->json(['data' => $this->postData($post->fresh(), $request)]);
    }

    public function vote(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $poll = $post->poll;
        abort_unless($poll, 404);
        abort_if($poll->closes_at && $poll->closes_at->isPast(), 422, 'This poll is closed.');
        $data = $request->validate(['option_ids' => ['required', 'array', 'min:1', 'max:10'], 'option_ids.*' => ['integer', 'distinct', 'exists:social_poll_options,id']]);
        $optionIds = array_values(array_map('intval', $data['option_ids']));
        $valid = $poll->options()->whereIn('id', $optionIds)->count();
        abort_unless($valid === count($optionIds), 422, 'Select valid poll options.');
        if (!$poll->allow_multiple) abort_if(count($optionIds) !== 1, 422, 'Choose one option.');
        DB::transaction(function () use ($poll, $optionIds, $request) {
            SocialPollVote::where('social_poll_id', $poll->id)->where('user_id', $request->user()->id)->delete();
            foreach ($optionIds as $optionId) SocialPollVote::create(['social_poll_id' => $poll->id, 'social_poll_option_id' => $optionId, 'user_id' => $request->user()->id]);
        });
        return response()->json(['data' => $this->pollData($poll->fresh(), $request)]);
    }

    public function toggleNotifications(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $preference = SocialPostPreference::firstOrCreate([
            'social_post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);
        $preference->update(['notifications_enabled' => !$preference->notifications_enabled]);
        return response()->json([
            'notifications_enabled' => (bool) $preference->notifications_enabled,
            'message' => $preference->notifications_enabled
                ? 'Post notifications turned on.'
                : 'Post notifications turned off.',
        ]);
    }

    public function hide(Request $request, SocialPost $post)
    {
        $this->scoped($request, $post);
        $preference = SocialPostPreference::firstOrCreate([
            'social_post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);
        $preference->update(['hidden_at' => $preference->hidden_at ? null : now()]);
        return response()->json([
            'hidden' => (bool) $preference->hidden_at,
            'message' => $preference->hidden_at ? 'Post hidden.' : 'Post visible again.',
        ]);
    }

    public function notifications(Request $request)
    {
        $this->company($request);
        $items = SocialNotification::where('user_id', $request->user()->id)->with('actor:id,name,avatar')->latest('id')->paginate(30);
        $items->setCollection($items->getCollection()->map(fn ($notification) => ['id' => $notification->id, 'type' => $notification->type, 'data' => $notification->data, 'read_at' => $notification->read_at?->toIso8601String(), 'created_at' => $notification->created_at->toIso8601String(), 'actor' => $this->author($notification->actor), 'social_post_id' => $notification->social_post_id, 'social_comment_id' => $notification->social_comment_id]));
        return response()->json($items);
    }

    public function markNotificationRead(Request $request, SocialNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);
        $notification->forceFill(['read_at' => now()])->save();
        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markNotificationsRead(Request $request)
    {
        SocialNotification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['message' => 'Notifications marked as read.']);
    }

    private function notify(int $userId, int $actorId, string $type, SocialPost $post, ?SocialComment $comment = null): void
    {
        if ($userId === $actorId) return;
        SocialNotification::create(['user_id' => $userId, 'actor_id' => $actorId, 'social_post_id' => $post->id, 'social_comment_id' => $comment?->id, 'type' => $type, 'data' => ['post_id' => $post->id, 'comment_id' => $comment?->id]]);
    }

    private function storePoll(Request $request, SocialPost $post): void
    {
        if (!$request->filled('poll_question')) return;
        $data = $request->validate([
            'poll_question' => ['required', 'string', 'max:500'],
            'poll_options' => ['required', 'array', 'min:2', 'max:10'],
            'poll_options.*' => ['required', 'string', 'max:160'],
            'poll_allow_multiple' => ['nullable', 'boolean'],
            'poll_closes_at' => ['nullable', 'date', 'after:now'],
        ]);
        $poll = $post->poll()->create(['question' => trim($data['poll_question']), 'allow_multiple' => (bool) ($data['poll_allow_multiple'] ?? false), 'closes_at' => $data['poll_closes_at'] ?? null]);
        foreach (array_values($data['poll_options']) as $position => $label) $poll->options()->create(['label' => trim($label), 'position' => $position]);
    }

    private function pollData(?SocialPoll $poll, Request $request): ?array
    {
        if (!$poll) return null;
        $selected = SocialPollVote::where('social_poll_id', $poll->id)->where('user_id', $request->user()->id)->pluck('social_poll_option_id')->map(fn ($id) => (int) $id)->all();
        return ['id' => $poll->id, 'question' => $poll->question, 'allow_multiple' => (bool) $poll->allow_multiple, 'closes_at' => $poll->closes_at?->toIso8601String(), 'closed' => (bool) ($poll->closes_at && $poll->closes_at->isPast()), 'my_option_ids' => $selected, 'options' => $poll->options->map(fn ($option) => ['id' => $option->id, 'label' => $option->label, 'votes_count' => (int) ($option->votes_count ?? $option->votes()->count())])->values()];
    }

    private function commentData(SocialComment $comment, Request $request): array
    {
        $reactions = $comment->reactions()->selectRaw('reaction, COUNT(*) as total')->groupBy('reaction')->pluck('total', 'reaction')->all();
        return ['id' => $comment->id, 'body' => $comment->body, 'parent_id' => $comment->parent_id, 'author' => $this->author($comment->author), 'created_at' => $comment->created_at->toIso8601String(), 'updated_at' => $comment->updated_at->toIso8601String(), 'replies_count' => (int) ($comment->replies_count ?? $comment->replies()->count()), 'reactions' => (object) $reactions, 'reactions_count' => array_sum($reactions), 'my_reaction' => $comment->reactions()->where('user_id', $request->user()->id)->value('reaction'), 'can_edit' => (int) $comment->user_id === (int) $request->user()->id && Gate::allows('social_feed.edit_comment'), 'can_delete' => ((int) $comment->user_id === (int) $request->user()->id && Gate::allows('social_feed.delete_comment')) || Gate::allows('social_feed.moderate')];
    }

    private function author($user): array
    {
        return ['id' => $user?->id, 'name' => $user?->name ?? 'Former employee', 'avatar' => $user?->avatar_url, 'department' => $user?->department?->dept_name];
    }

    public function postData(SocialPost $post, Request $request, bool $includeOriginal = true): array
    {
        $post->loadMissing(['author:id,name,avatar,department_id', 'author.department:id,dept_name', 'media', 'poll.options' => fn ($query) => $query->withCount('votes')]);
        if (!array_key_exists('comments_count', $post->getAttributes())) $post->loadCount(['comments', 'reactions', 'shares']);
        $own = (int) $post->user_id === (int) $request->user()->id;
        $preference = $this->summaryHydrated
            ? ($this->preferences[$post->id] ?? null)
            : $post->preferences()->where('user_id', $request->user()->id)->first();
        $reactions = $this->reactionSummaries !== null ? ($this->reactionSummaries[$post->id] ?? []) : $post->reactions()->selectRaw('reaction, COUNT(*) as total')->groupBy('reaction')->pluck('total', 'reaction')->all();
        return app(\App\Services\Community\UploadedMediaUrl::class)->payload(['id' => $post->id, 'body' => $post->body, 'location_name' => $post->location_name, 'media_type' => $post->media_type, 'media_url' => $post->media_url, 'playback_url' => $post->playback_url, 'thumbnail_url' => $post->thumbnail_url ?: $post->adaptive_thumbnail_url, 'duration_seconds' => $post->duration_seconds, 'aspect_ratio' => $post->aspect_ratio, 'media' => $post->media->map(fn ($media) => ['id' => $media->id, 'media_type' => $media->media_type, 'media_url' => $media->media_url, 'playback_url' => $media->playback_url, 'thumbnail_url' => $media->thumbnail_url ?: $media->adaptive_thumbnail_url, 'duration_seconds' => $media->duration_seconds, 'aspect_ratio' => $media->aspect_ratio, 'file_name' => $media->file_name])->values(), 'poll' => $this->pollData($post->poll, $request), 'author' => $this->author($post->author), 'created_at' => $post->created_at->toIso8601String(), 'updated_at' => $post->updated_at->toIso8601String(), 'comments_count' => (int) $post->comments_count, 'reactions_count' => (int) $post->reactions_count, 'shares_count' => (int) $post->shares_count, 'reactions' => (object) $reactions, 'my_reaction' => $this->reactionSummaries !== null ? ($this->myReactions[$post->id] ?? null) : $post->reactions()->where('user_id', $request->user()->id)->value('reaction'), 'visibility' => $post->visibility ?: 'company', 'is_announcement' => (bool) $post->is_announcement, 'is_pinned' => (bool) $post->pinned_at, 'is_saved' => $this->summaryHydrated ? in_array((int) $post->id, $this->savedIds, true) : SocialPostSave::where('social_post_id', $post->id)->where('user_id', $request->user()->id)->exists(), 'notifications_enabled' => (bool) ($preference?->notifications_enabled ?? false), 'is_hidden' => (bool) ($preference?->hidden_at), 'can_edit' => $own && Gate::allows('social_feed.edit'), 'can_delete' => $own && Gate::allows('social_feed.delete'), 'can_pin' => Gate::allows('social_feed.pin') || Gate::allows('social_feed.moderate'), 'can_report' => !$own && Gate::allows('social_feed.report'), 'can_change_audience' => $own, 'original_post_id' => $post->original_post_id, 'original' => $includeOriginal && $post->original ? $this->postData($post->original, $request, false) : null]);
    }
}
