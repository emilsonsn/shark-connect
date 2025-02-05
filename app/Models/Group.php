<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leadDistributionCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(LeadDistributionCampaign::class)->withTimestamps();
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
