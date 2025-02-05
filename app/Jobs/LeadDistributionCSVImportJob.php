<?php

namespace App\Jobs;

use App\Models\LeadDistributionCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class LeadDistributionCSVImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $campaignId,
        private string $path
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaign = LeadDistributionCampaign::find($this->campaignId);
        
        $fileHandle = fopen(Storage::path($this->path), "r");
        
        $jobs = [];

        $counter = 0;

        while(!feof($fileHandle)) {

            $line = fgetcsv($fileHandle, 0, ";");

            if($counter == 0) {
                $counter++;
                continue;
            }

            if(!$line) {
                continue;
            }

            //deal with cedilla in the array
            $line = array_map(function($item) {
                return mb_convert_encoding($item, "UTF-8", "ISO-8859-1");
            }, $line);

            $jobs[] = new ProcessDistributionCSVLineJob($line, $this->campaignId);

        }

        fclose($fileHandle);

        $queueName = $campaign->queueName();

        $batch = Bus::batch($jobs)
            ->name($queueName)
            ->onQueue($queueName)
            ->allowFailures()
            ->finally(function ($batch) use ($campaign) {

                $completedJobs = $batch->totalJobs - $batch->failedJobs;

                if($completedJobs > 0) {

                    $campaign->status = 1;
                    $campaign->total = $completedJobs;
                    $campaign->remaining = $completedJobs;
                    $campaign->campaign_processing_status_id = LeadDistributionCampaign::STATUS_FINISHED;
                    $campaign->save();

                }
            
            })->dispatch();

        $campaign->batch_id = $batch->id;
        $campaign->campaign_processing_status_id = LeadDistributionCampaign::STATUS_PROCESSING;
        $campaign->save();
    }
}
