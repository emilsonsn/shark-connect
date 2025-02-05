<?php

namespace App\Jobs;

use App\Models\LeadDistributionCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CampaignRecycleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $campaignId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaign = LeadDistributionCampaign::find($this->campaignId);

        $prospects = $campaign->prospects()
            ->whereIn('tabulation_id', LeadDistributionCampaign::SHOULD_RECICLE_TABULATIONS)
            ->get();

        //update tabulation_id to 0, set user_id to null
        foreach($prospects as $prospect) {
            $prospect->update([
                'tabulation_id' => 1,
                'user_id' => null,
                'caught_at' => null
            ]);
        }

        //update campaign remaining, using the count of prospects previously updated + the remaining of the campaign

        $campaign->update([
            'remaining' => $campaign->remaining + $prospects->count(),
            'count_recycle' => $campaign->count_recycle + 1,
            'last_recycle_date' => now()
        ]);

        //should update the pulled leads campaign
        //get all caches of users that have access to the campaign
        // $users = $campaign->groups()
        //     ->with('users')
        //     ->get()
        //     ->pluck('users')
        //     ->flatten();

        // foreach($users as $user) {
        //     Cache::forget("user-{$user->id}:pulled-leads-campaign-{$campaign->id}");
        // }

    }
}
