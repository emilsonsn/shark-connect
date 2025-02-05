<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(LeadDistributionCampaign::class, 'lead_distribution_prospect')
            ->using(LeadDistributionProspect::class)
            ->withPivot(['tabulation_id', 'margin', 'convenant', 'organ', 'user_id', 'caught_at'])
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lead_distribution_prospect')
            ->using(LeadDistributionProspect::class)
            ->withPivot(['tabulation_id', 'margin', 'convenant', 'organ', 'user_id', 'caught_at'])
            ->withTimestamps();
    }

    public function employers(): HasMany
    {
        return $this->hasMany(ClientEmployer::class);
    }

}
