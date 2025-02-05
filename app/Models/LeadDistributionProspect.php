<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LeadDistributionProspect extends Pivot
{
    protected $table = 'lead_distribution_prospect';

    protected $fillable = [
        'lead_distribution_campaign_id',
        'client_id',
        'user_id',
        'tabulation_id',
        'margin',
        'convenant',
        'organ',
        'caught_at'
    ];

    public function tabulation()
    {
        return $this->belongsTo(Tabulation::class);
    }
}
