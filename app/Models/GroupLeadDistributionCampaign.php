<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Cache;

class GroupLeadDistributionCampaign extends Pivot
{
    use HasFactory;

    protected $table = 'group_lead_distribution_campaign';

    protected $fillable = [
        'group_id',
        'lead_distribution_campaign_id'
    ];

    public static function boot(){
        parent::boot();

        static::deleted(function($model){
            
            $group = $model->group;
            $campaign = $model->campaign;

            if ($group && $campaign) {

                $users = $group->users()
                    ->join('lead_distribution_user_configs as t2', 'users.id', '=', 't2.user_id')
                    ->where('t2.current_campaign_id', $campaign->id)
                    ->get();

                foreach ($users as $user) {
                    Cache::forget("group-{$group->id}:current_campaign_of_user_{$user->id}");
                }
            }

            //proceed with detaching
            return true;

        });

    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function campaign()
    {
        return $this->belongsTo(LeadDistributionCampaign::class, 'lead_distribution_campaign_id');
    }
}
