<?php

namespace App\Jobs;

use App\Models\LeadDistributionCampaign;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Log;

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
        try{
            Log::info("Entrou");

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

            Log::info("Total de jobs criados para a campanha {$campaign->id}: " . count($jobs));

            fclose($fileHandle);
    
            $queueName = $campaign->name;

            log::info($queueName);
    
            $batch = Bus::batch($jobs)
                ->name($queueName)
                ->onQueue($queueName)
                ->allowFailures()
                ->finally(function ($batch) use ($campaign) {

                    Log::info("Batch Finalizado - ID: {$batch->id}");
                    Log::info("Total de Jobs: {$batch->totalJobs}, Falhas: {$batch->failedJobs}");
                
                    $completedJobs = $batch->totalJobs - $batch->failedJobs;
                
                    if ($completedJobs > 0) {
                        Log::info("Atualizando status da campanha {$campaign->id} - Jobs Concluídos: {$completedJobs}");
                
                        $campaign->status = 1;
                        $campaign->total = $completedJobs;
                        $campaign->remaining = $completedJobs;
                        $campaign->campaign_processing_status_id = LeadDistributionCampaign::STATUS_FINISHED;
                        $campaign->save();
                    } else {
                        Log::warning("Nenhum job foi concluído para a campanha {$campaign->id}");
                    }
                })->dispatch();
    
            $campaign->batch_id = $batch->id;
            $campaign->campaign_processing_status_id = LeadDistributionCampaign::STATUS_PROCESSING;
            $campaign->save();

            exec("php artisan queue:work --queue={$queueName} --once >> storage/logs/queue_exec.log 2>&1 &");
        }catch(Exception $error){
            Log::error($error->getMessage());
        }
    }
}
