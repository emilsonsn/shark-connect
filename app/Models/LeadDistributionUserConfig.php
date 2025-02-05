<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeadDistributionUserConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_campaign_id',
        'limit_leads'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentCampaign(): HasOne
    {
        return $this->hasOne(LeadDistributionCampaign::class, 'id', 'current_campaign_id');
    }
}
