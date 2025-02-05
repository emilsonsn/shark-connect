<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LeadDistributionCampaignUser extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'lead_distribution_campaign_id',
        'limit',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(LeadDistributionCampaign::class, 'lead_distribution_campaign_id');
    }
}
