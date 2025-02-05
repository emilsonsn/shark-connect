<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class LeadDistributionCampaign extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 1;
    public const STATUS_PROCESSING = 2;
    public const STATUS_FINISHED = 3;
    public const STATUS_CANCELED = 4;
    public const SHOULD_RECICLE_TABULATIONS = [1,4,5];

    protected $fillable = [
        'name',
        'description',
        'status',
        'total',
        'remaining',
        'percentage_by_user',
        'batch_id',
        'count_recycle',
        'last_recycle_date',
        'campaign_processing_status_id'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->using(GroupLeadDistributionCampaign::class)
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(LeadDistributionCampaignUser::class)
            ->withPivot(['user_id', 'id', 'lead_distribution_campaign_id', 'limit', 'caught_today'])
            ->withTimestamps();
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'lead_distribution_prospect')
            ->using(LeadDistributionProspect::class)
            ->withPivot(['tabulation_id', 'margin', 'convenant', 'organ', 'user_id', 'id', 'caught_at'])
            ->withTimestamps();
    }

    public function queueName(): string
    {
        $name = implode('-', array_merge(
            explode(" ", $this->name), 
            explode(" ", $this->created_at)
        ));

        return substr($name, 0, 225);
    }

    public function prospects(): HasMany
    {
        return $this->hasMany(LeadDistributionProspect::class);
    }

    public function configs(): HasMany
    {
        return $this->hasMany(LeadDistributionUserConfig::class);
    }

    public function batches(): BelongsTo
    {
        return $this->belongsTo(JobBatch::class, 'batch_id');
    }

}
