<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\LeadDistributionCampaign;
use App\Models\LeadDistributionCampaignUser;
use App\Models\LeadDistributionProspect;
use App\Models\LeadsTakenByMonthReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class MyListHandleController extends Controller
{
    public function index(Request $request)
    {
        //if is superior and app is not in dev mode
        if($request->user()->isSuperior() && !config('app.debug')){
            return back();
        }

        $search = $request->query('search');

        $leads = Client::select([
                "clients.id as client_id",
                "clients.name as client_name", 
                "lead_distribution_campaigns.name as campaign_name",
                "lead_distribution_prospect.tabulation_id",
                "lead_distribution_prospect.margin",
                "lead_distribution_prospect.convenant",
                "lead_distribution_prospect.organ",
                "lead_distribution_prospect.id as id"
            ])
            ->join("lead_distribution_prospect", "lead_distribution_prospect.client_id", "=", "clients.id")
            ->join("lead_distribution_campaigns", "lead_distribution_campaigns.id", "=", "lead_distribution_prospect.lead_distribution_campaign_id")
            ->join("client_contacts", "client_contacts.client_id", "=", "clients.id")
            ->where('user_id', $request->user()->id)
            ->orderBy("lead_distribution_prospect.updated_at", "desc")

            ->when($search, function($query, $search){

                return $query->where(function ($query) use ($search) {
                    $query->where('clients.name', 'like', "%$search%")
                        ->orWhere('clients.cpf', 'like', "%$search%")
                        ->orWhere('client_contacts.number', 'like', "%$search%");
                });
            })
            ->groupBy([
                "clients.id",
                "clients.name",
                "lead_distribution_campaigns.name",
                "lead_distribution_prospect.tabulation_id",
                "lead_distribution_prospect.margin",
                "lead_distribution_prospect.convenant",
                "lead_distribution_prospect.organ",
                "lead_distribution_prospect.id"
            
            ])
            ->paginate(10);

        //preserve search query
        $leads->appends(['search' => $search]);

        $currentCampaign = $request->user()->getCurrentCampaign();

        return Inertia::render('LeadDistribution/LeadList',
            [
                'leads' => $leads,
                'currentCampaign' => $currentCampaign?->id
            ]
        );
    }

    public function treatLead(Request $request, $id){

        $prospect = Client::join("lead_distribution_prospect", "lead_distribution_prospect.client_id", "=", "clients.id")
            ->where("lead_distribution_prospect.id", $id)
            ->where("lead_distribution_prospect.user_id", $request->user()->id)
            ->select()
            ->first();

        if($prospect == null){
            return back()->with('flash', [
                'message' => 'Lead não encontrado',
                'type' => 'error'
            ]);
        }

        $campanha = LeadDistributionCampaign::find($prospect->lead_distribution_campaign_id)->first();

        return Inertia::render('LeadDistribution/TreatLeadView',
            [
                'lead' => $prospect,
                'campaign' => $campanha,
                'isNew' => false
            ]
        );

    }

    public function treatNewLead(Request $request)
    {

        $user = $request->user();

        return DB::transaction(function () use ($user) {
            
            $campanha = LeadDistributionCampaign::where('status', true)
                ->where('remaining', '>', 0)
                ->whereHas('groups', function($query) use ($user){
                    $query->where('groups.id', $user->group_id);
                })
                ->whereHas('users', function($query) use ($user){
                    $query->where('users.id', $user->id)
                        ->where('lead_distribution_campaign_user.limit', '>', DB::raw('lead_distribution_campaign_user.caught_today'));
                })
                ->inRandomOrder()
                ->lockForUpdate()
                ->first();

            if(!$campanha){
                return back()->with('flash', [
                    'message' => 'Não há campanhas disponiveis para você',
                    'type' => 'error'
                ]);
            }

            //checar essa query
            $prospect = $campanha->clients()
                ->whereNull("user_id")
                ->select(["clients.*", "lead_distribution_prospect.*"])
                ->inRandomOrder()
                ->first();

            //verficar se o lead é nulo
            if(!$prospect){

                $campanha->remaining = 0;
                $campanha->save();

                return back()->with('flash', [
                    'message' => 'A campanha selecionada não possui mais leads disponiveis',
                    'type' => 'error'
                ]);
            }

            $prospect->pivot->user_id = $user->id;
            $prospect->pivot->caught_at = now();
            $prospect->pivot->save();

            $campanha->remaining = $campanha->remaining - 1;

            $campanha->save();

            $report = LeadsTakenByMonthReport::select()
                ->where('user_id', '=', $user->id)
                ->where('day', '=', date('d'))
                ->where('month', '=', date('m'))
                ->where('year', '=', date('Y'))
                ->first();

            if(!$report){
                $report = new LeadsTakenByMonthReport();
                $report->user_id = $user->id;
                $report->day = date('d');
                $report->month = date('m');
                $report->year = date('Y');
                $report->total = 0;
            }
            
            $report->total++;
            $report->save();

            $user->setCurrentCampaign($campanha);

            //update caught_today
            $campUser = $user->leadDistributionCampaigns()->where('lead_distribution_campaign_id', $campanha->id)->first();
            $campUser->pivot->caught_today++;
            $campUser->pivot->save();
        
            return Inertia::render('LeadDistribution/TreatLeadView',
                [
                    'lead' => $prospect,
                    'campaign' => $campanha,
                    'isNew' => true
                ]
            );
        });
    }

    public function updateTabulation(Request $request, $id)
    {
        $prospect = LeadDistributionProspect::find($id);

        if($prospect->tabulation_id != 1 && $prospect->tabulation_id != 5){
            return response()->json([
                'message' => 'Este lead já foi tratado',
                'type' => 'error'
            ], 500);
        }

        if($prospect == null){
            return response()->json([
                'message' => 'Prospect não encontrado',
                'type' => 'error'
            ], 500);
        }

        if($prospect->user_id != $request->user()->id){
            return response()->json([
                'message' => 'Você não tem permissão para alterar este lead',
                'type' => 'error'
            ], 500);
        }

        $prospect->tabulation_id = $request->status;

        $prospect->save();

        return response()->json([
            'message' => 'Lead atualizado com sucesso',
            'type' => 'success'
        ]);
    }
}
