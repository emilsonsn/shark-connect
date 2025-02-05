<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ResetLeadDistributionRateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-lead-distribution-rate-limit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reseta o limite de distribuição de leads para todos os usuarios';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //get all users
        $users = User::all();
        
        //reset rate limiters for all campaigns for all users
        foreach($users as $user){
            //update pivot
            $user->leadDistributionCampaigns()->each(function($campaign) use ($user){
                $campaign->pivot->caught_today = 0;
                $campaign->pivot->save();

            });
        }

        $this->info('Rate limiters have been reset for all users.');
    }
}
