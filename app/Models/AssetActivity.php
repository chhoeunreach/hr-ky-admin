<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetActivity extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];

    public static function record(Model $model, string $event): void
    {
        $isAsset = $model instanceof Asset;
        $asset = $isAsset ? $model : Asset::withoutGlobalScopes()->findOrFail($model->asset_id);
        $changes = $event === 'updated' ? array_diff_key($model->getChanges(), array_flip(['updated_at', 'updated_by'])) : $model->getAttributes();
        if (!$changes) return;
        $before = $event === 'created' ? null : ($event === 'deleted' ? $model->getAttributes() : array_intersect_key($model->getRawOriginal(), $changes));
        $after = $event === 'deleted' ? null : $changes;
        foreach (['asset_password', 'screen_time_passcode'] as $secret) {
            if (isset($before[$secret])) $before[$secret] = '[redacted]';
            if (isset($after[$secret])) $after[$secret] = '[redacted]';
        }
        if (!$isAsset) {
            $snapshot = ['employee_name' => User::withTrashed()->find($model->user_id)?->name];
            if ($before !== null) $before += $snapshot;
            if ($after !== null) $after += $snapshot;
        }
        $guard = auth('admin')->check() ? 'admin' : config('auth.defaults.guard');
        $actor = auth($guard)->user();
        static::create([
            'asset_id' => $asset->id, 'branch_id' => $asset->branch_id, 'asset_name' => $asset->name,
            'assignment_id' => $isAsset ? null : $model->id,
            'event' => ($isAsset ? 'asset_' : 'assignment_').$event,
            'actor_id' => $actor?->id, 'actor_name' => $actor?->name, 'actor_guard' => $guard,
            'before' => $before, 'after' => $after, 'created_at' => now(),
        ]);
    }
}
