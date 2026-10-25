<?php

namespace App\Traits;

use App\Models\AssetActivity;

trait RecordsAssetActivity
{
    protected static function bootRecordsAssetActivity(): void
    {
        static::created(fn ($model) => AssetActivity::record($model, 'created'));
        static::updated(fn ($model) => AssetActivity::record($model, 'updated'));
        static::deleted(fn ($model) => AssetActivity::record($model, 'deleted'));
    }
}
