<?php

namespace App\Providers;

use App\Models\LeadDistributionCampaign;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use App\Observers\PermissionObserver;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Permission::observe(PermissionObserver::class);

        Gate::before(function (User $user, $ability) {
            if(Permission::existsOnCache($ability)){
                return $user->hasPermissionTo($ability);
            }
        });

        Queue::popUsing('minhaListaImport', function ($pop) {
            
            //get all lotes from database
            $lotes = LeadDistributionCampaign::where('campaign_processing_status_id', LeadDistributionCampaign::STATUS_PROCESSING)
                ->get();

            //shuffle lotes
            $lotes = $lotes->shuffle();

            foreach($lotes as $lote){
                $queueName = $lote->queueName();

               if(!is_null($job = $pop($queueName))){
                    return $job;
               }

            }

        });

    }
}
