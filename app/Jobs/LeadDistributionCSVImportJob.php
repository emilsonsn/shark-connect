<?php

namespace App\Jobs;

use App\Models\LeadDistributionCampaign;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Log;

class LeadDistributionCSVImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $campaignId,
        private string $path
    ) {}

    public function handle(): void
    {
        try {
            Log::info("Entrou");

            $campaign = LeadDistributionCampaign::find($this->campaignId);

            $fileHandle = fopen(Storage::path($this->path), "r");

            $counter = 0;
            $successCount = 0;

            while (!feof($fileHandle)) {
                $line = fgetcsv($fileHandle, 0, ";");

                if ($counter == 0) {
                    $counter++;
                    continue;
                }

                if (!$line) {
                    continue;
                }

                $line = array_map(function ($item) {
                    return mb_convert_encoding($item, "UTF-8", "ISO-8859-1");
                }, $line);

                try {
                    $job = new ProcessDistributionCSVLineJob($line, $this->campaignId);
                    $job->handle();
                    $successCount++;
                } catch (Exception $e) {
                    Log::error("Erro ao processar linha: " . $e->getMessage());
                }
            }

            fclose($fileHandle);

            Log::info("Total de linhas processadas com sucesso: $successCount");

            $campaign->status = 1;
            $campaign->total = $successCount;
            $campaign->remaining = $successCount;
            $campaign->campaign_processing_status_id = LeadDistributionCampaign::STATUS_FINISHED;
            $campaign->save();

        } catch (Exception $error) {
            Log::error($error->getMessage());
        }
    }
}
