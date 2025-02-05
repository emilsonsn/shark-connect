<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\LeadDistributionProspect;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDistributionCSVLineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $line,
        protected int $campaignId
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $cpf = preg_replace("/[^0-9]/", "", $this->line[1]);
        $phone = preg_replace("/[^0-9]/", "", $this->line[5]);

        $client = Client::where("cpf", $cpf)->first();

        if(!$client) {
            $client = new Client();
            $client->name = $this->line[0];
            $client->cpf = $cpf;
            $client->save();
        }

        $ddd = substr($phone, 0, 2);
        $number = substr($phone, 2);

        $contact = $client->contacts()
            ->where("ddd", $ddd)
            ->where("number", $number)
            ->first();

        if(!$contact) {

            $contact = new ClientContact();
            $contact->ddd = $ddd;
            $contact->number = $number;
            $contact->save();

            $client->contacts()->save($contact);
        }

        if($this->line[2] == "") {
            $this->line[2] = 0;
        }

        LeadDistributionProspect::create([
            "client_id" => $client->id,
            "lead_distribution_campaign_id" => $this->campaignId,
            "tabulation_id" => 1,
            "margin" => str_replace(",", ".", $this->line[2]),
            "convenant" => $this->line[3] ?? "",
            "organ" => $this->line[4] ?? ""
        ]);

        //get all the columns after the 6th
        $phones = array_slice($this->line, 6);

        if(count($phones) == 0) {
            return;
        }

        foreach($phones as $phone) {

            $phone = preg_replace("/[^0-9]/", "", $phone);

            if($phone == "") {
                continue;
            }

            if(!is_numeric($phone)) {
                continue;
            }

            $ddd = substr($phone, 0, 2);
            $number = substr($phone, 2);

            $contact = $client->contacts()
                ->where("ddd", $ddd)
                ->where("number", $number)
                ->first();

            if(!$contact) {

                $contact = new ClientContact();
                $contact->ddd = $ddd;
                $contact->number = $number;
                $contact->save();

                $client->contacts()->save($contact);
            }
        }

    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('minhaListaLine')->error($exception->getMessage());
    }
}
