<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialRewardAudit extends Model
{
    protected $table = 'hrs_addon_reward_audit';

    protected $guarded = ['id'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(SocialReward::class, 'target_record_id');
    }
}
