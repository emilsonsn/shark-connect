<?php

namespace App\Http\Controllers;

use App\Models\LeadDistributionCampaign;
use App\Http\Requests\StoreLeadDistributionCampaignRequest;
use App\Http\Requests\UpdateLeadDistributionCampaignRequest;
use App\Jobs\CampaignRecycleJob;
use App\Jobs\CampaignUnifiedRecycleJob;
use App\Jobs\LeadDistributionCSVImportJob;
use App\Models\Group;
use App\Models\LeadDistributionCampaignUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;

class MyListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if(!$request->user()->hasPermissionTo('manage-users')){
            abort(403);
        }

        $search = $request->query('search');
        $status = $request->query('status', 'active');

        $campaigns = LeadDistributionCampaign::select(
            ["id", "name", "status", "total", "remaining",  "created_at", "batch_id", 'last_recycle_date', 'count_recycle']
        )
        ->with('batches')
        ->orderBy('id', 'desc');

        if(in_array($status, ['active', 'inactive'])) {

            $campaigns = $campaigns->where('status', $status == 'active' ? true : false);

        }

        if($search) {
            $campaigns = $campaigns->where('name', 'like', "%$search%");
        }

        $campaigns = $campaigns->paginate(20);

        // dd($campaigns);

        // add new field to each campaign
        foreach($campaigns as $campaign) {

            if($campaign->created_at == $campaign->last_recycle_date){
                $campaign->last_recycle_date = null;
            }

            if($campaign->batch_id == 0){
                $campaign->progress = [
                    'status' => 'Finalizado',
                    'progress' => 100,
                    'totalJobs' => $campaign->total,
                    'processedJobs' => $campaign->total,
                    'failedJobs' => 0,
                ];

                continue;
            }

            if(!$campaign->batch_id){
                $campaign->progress = null;
                continue;
            }

            if($campaign->batches->total_jobs == $campaign->batches->processedJobs() + $campaign->batches->failed_jobs){
                $campaign->progress = [
                    'status' => 'Finalizado',
                    'progress' => 100,
                    'totalJobs' => $campaign->batches->total_jobs,
                    'processedJobs' => $campaign->batches->processedJobs(),
                    'failedJobs' => $campaign->batches->failed_jobs,
                ];

                continue;
            }

            if(!is_null($campaign->batches->cancelled_at)){
                $campaign->progress = [
                    'status' => 'Cancelado',
                    'progress' => 0,
                    'totalJobs' => $campaign->batches->total_jobs,
                    'processedJobs' => $campaign->batches->processedJobs(),
                    'failedJobs' => $campaign->batches->failed_jobs,
                ]; 

                continue;
            
            }
            
            $campaign->progress = [
                'status' => 'Processando...',
                'progress' => $campaign->batches->progress(),
                'totalJobs' => $campaign->batches->total_jobs,
                'processedJobs' => $campaign->batches->processedJobs(),
                'failedJobs' => $campaign->batches->failed_jobs,
            ];
            
        }   

        //preserve search query
        $campaigns->appends([
            'status' => $status,
            'search' => $search
        ]);

        return Inertia::render('LeadDistribution/CampainList',
            [
                'campaigns' => $campaigns
            ]
        );
    }

    public function listViewOnly(Request $request){

        if(!$request->user()->isSuperior()){
            return back();
        }

        $search = $request->query('search');
        $status = $request->query('status', 'active');

        $campaigns = LeadDistributionCampaign::select(
            ["lead_distribution_campaigns.id", "name", "status", "remaining",  "lead_distribution_campaigns.created_at"]
        )
        ->join('group_lead_distribution_campaign as t2', 'lead_distribution_campaigns.id', '=', 't2.lead_distribution_campaign_id')
        ->where('group_id', $request->user()->group_id)
        ->orderBy('id', 'desc');

        if(in_array($status, ['active', 'inactive'])) {

            $campaigns = $campaigns->where('status', $status == 'active' ? true : false);

        }

        if($search) {
            $campaigns = $campaigns->where('name', 'like', "%$search%");
        }

        $campaigns = $campaigns->paginate(20);

        //preserve search query
        $campaigns->appends([
            'status' => $status,
            'search' => $search
        ]);

        return Inertia::render('LeadDistribution/CampaignViewList',
            [
                'campaigns' => $campaigns
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
    */
    public function store(StoreLeadDistributionCampaignRequest $request)
    {
        $filename = uniqid(). ".csv";
        $path = $request->file->storeAs("campanhas", $filename);

        $name = substr($request->name, 0, 230);
        
        $campaign = LeadDistributionCampaign::create([
            'name' => $name,
            'description' => $request->description,
            'status' => false,
            'total' => 0,
            'remaining' => 0,
            'percentage_by_user' => $request->max_per_user,
            'campaign_processing_status_id' => LeadDistributionCampaign::STATUS_WAITING,
            'last_recycle_date' => now(),
        ]);

        foreach($request->groups as $group) {
            $campaign->groups()->attach(Group::find($group));

            //get the user from the group that has no superior
            $user = User::where('group_id', $group)
                ->whereNull('superior_id')
                ->get();

            if($user) {
                foreach($user as $u) {
                    $u->leadDistributionCampaigns()->attach($campaign);
                }
            }
        }

        LeadDistributionCSVImportJob::dispatch($campaign->id, $path);

        return back()->with('flash', [
            'message' => 'Campanha criada com sucesso!',
            'type' => 'success'
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeadDistributionCampaign $leadDistributionCampaign)
    {

        return Inertia::render('LeadDistribution/CampainEdit',
            [
                'campaign' => $leadDistributionCampaign,
                'groups' => Group::select(["id as value", "name as label"])->get(),
                'selectedGroups' => $leadDistributionCampaign->groups()->select(["groups.id as value", "name as label"])->get(),
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadDistributionCampaignRequest $request, LeadDistributionCampaign $leadDistributionCampaign)
    {
        $leadDistributionCampaign->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $a = $leadDistributionCampaign->groups()->sync($request->groups);

        //the groups that were removed from the campaign, we need to remove the campaign from the user_lead_distribution_campaign table
        $removedGroups = array_diff($a['detached'], $a['attached']);

        foreach($removedGroups as $group) {
            $users = User::where('group_id', $group)
                ->get();

            foreach($users as $user) {
                $user->leadDistributionCampaigns()->detach($leadDistributionCampaign);
            }
        }

        //the groups that were added to the campaign, we need to add the campaign to the user_lead_distribution_campaign table only for the users that have no superior
        $addedGroups = array_diff($a['attached'], $a['detached']);

        foreach($addedGroups as $group) {
            $users = User::where('group_id', $group)
                ->whereNull('superior_id')
                ->get();

            foreach($users as $user) {
                $user->leadDistributionCampaigns()->attach($leadDistributionCampaign);
            }
        }

        return back()->with('flash', [
            'message' => 'Campanha atualizada com sucesso!',
            'type' => 'success'
        ]);
    }

    public function deactivate(LeadDistributionCampaign $leadDistributionCampaign)
    {
        $leadDistributionCampaign->update([
            'status' => false
        ]);

        //forget the cache from all users from each group that is related to this campaign
        foreach($leadDistributionCampaign->groups as $group) {

            $users = $group->users()
                ->join('lead_distribution_user_configs as t2', 'users.id', '=', 't2.user_id')
                ->where('t2.current_campaign_id', $leadDistributionCampaign->id)
                ->get();

            foreach($users as $user) {
                cache()->forget("group-{$group->id}:current_campaign_of_user_{$user->user_id}");
            }
        }

        return back()->with('flash', [
            'message' => 'Campanha desativada com sucesso!',
            'type' => 'success'
        ]);
    }

    public function updateMaxPerUser(Request $request, LeadDistributionCampaign $leadDistributionCampaign)
    {
        $leadDistributionCampaign->update([
            'percentage_by_user' => $request->max_per_user
        ]);

        //forget the cache from all users from each group that is related to this campaign
        foreach($leadDistributionCampaign->groups as $group) {

            $users = $group->users()
                ->join('lead_distribution_user_configs as t2', 'users.id', '=', 't2.user_id')
                ->where('t2.current_campaign_id', $leadDistributionCampaign->id)
                ->get();

            foreach($users as $user) {

                cache()->forget("group-{$group->id}:current_campaign_of_user_{$user->user_id}");
            }
        }

        return back()->with('flash', [
            'message' => 'Campanha atualizada com sucesso!',
            'type' => 'success'
        ]);
    }

    public function activate(Request $request, LeadDistributionCampaign $leadDistributionCampaign)
    {

        if(!$request->user()->hasPermissionTo('manage-users')){
            abort(403);
        }

        $leadDistributionCampaign->update([
            'status' => true
        ]);

        return back()->with('flash', [
            'message' => 'Campanha ativada com sucesso!',
            'type' => 'success'
        ]);
    }

    public function recycle(Request $request, LeadDistributionCampaign $leadDistributionCampaign){

        if(!$request->user()->hasPermissionTo('manage-users')){
            abort(403);
        }

        //get all prospects from campaign that should be recycled
        $prospects = $leadDistributionCampaign->prospects()
            ->whereIn('tabulation_id', LeadDistributionCampaign::SHOULD_RECICLE_TABULATIONS)
            ->count();

        if($prospects == 0){
            return back()->with('flash', [
                'message' => 'Nenhum cliente encontrado para reciclar',
                'type' => 'error'
            ]);
        }

        CampaignRecycleJob::dispatch($leadDistributionCampaign->id);

        return back()->with('flash', [
            'message' => 'Campanha reciclada com sucesso! ' . $prospects . ' clientes voltarão para a base',
            'type' => 'success'
        ]);

    }

    public function getAllCampaigns(Request $request){
        $campaigns = LeadDistributionCampaign::select(["id", "name"])
            ->where('status', 1);

        $campaigns = $campaigns->get();

        return $campaigns;
    }

    public function getCampaingRanking(Request $request, $id){

        if(!$request->user()->hasPermissionTo('manage-users')){
            abort(403);
        }

        $company = $request->query('group', null);
        $user = $request->query('user', null);

        $ranking = LeadDistributionCampaign::selectRaw('count(*) as total, t3.name as name, t4.name as group_name')
            ->join('lead_distribution_prospect as t2', 'lead_distribution_campaigns.id', '=', 't2.lead_distribution_campaign_id')
            ->join('users as t3', 't2.user_id', '=', 't3.id')
            ->join('groups as t4', 't3.group_id', '=', 't4.id')
            ->where('lead_distribution_campaigns.id', $id)
            ->groupBy('name', 'group_name')
            ->orderBy('total', 'desc')
            ->when($company, function($query, $company){
                return $query->where('t4.id', $company);
            })
            ->when($user, function($query, $user){
                return $query->where('t3.id', $user);
            })
        ;

        $ranking = $ranking->paginate(10);

        //preserve search query
        $ranking->appends([
            'company' => $company,
            'user' => $user
        ]);

        $groups = Group::select(["id as value", "name as label"])
            ->active()
            ->get();

        //add an empty option to the select
        $groups->prepend(['value' => '', 'label' => 'Todas']);

        return Inertia::render('LeadDistribution/CampaignRanking', [
                'ranking' => $ranking,
                'groups' => $groups,
            ]
        );

    }

    public function getCampaingsUsedByUserOnDate(Request $request, $userId){
            
        if(!$request->user()->hasPermissionTo('manage-users') && !$request->user()->hasPermissionTo('view-campaigns') ){
            abort(403);
        }

        if(!$request->user()->hasPermissionTo('manage-users') && User::find($userId)->superior_id != $request->user()->id && $userId != $request->user()->id){
            abort(403); 
        }

        $start_date = isset($request->inicio) ? Carbon::parse($request->inicio)->setHours(0,0,0) : Carbon::today()->setHours(0,0,0);
        $end_date = isset($request->fim) ? Carbon::parse($request->fim)->setHours(23,59,59) : Carbon::today()->setHours(23,59,59);

        $campaigns = LeadDistributionCampaign::selectRaw('t1.lead_distribution_campaign_id, lead_distribution_campaigns.name, count(*) as total')
            ->join('lead_distribution_prospect as t1', 'lead_distribution_campaigns.id', '=', 't1.lead_distribution_campaign_id')
            ->where('t1.user_id', $userId)
            ->whereRaw('t1.created_at != t1.updated_at')
            ->whereBetween('t1.updated_at', [$start_date, $end_date])
            ->groupBy('lead_distribution_campaign_id', 'lead_distribution_campaigns.name')
            ->orderBy('total', 'desc')
            ->paginate(10);

        return Inertia::render('Dashboard/CampaignsUsedByUser', [
            'campaigns' => $campaigns
        ]);
    }

    public function cancelProcessing(Request $request){

        $campaign = LeadDistributionCampaign::find($request->campaignId);

        if(!$campaign) {
            return back()->with('flash', [
                'message' => 'Campanha não encontrada',
                'type' => 'error'
            ]);
        }

        if(!$campaign->batch_id){
            return back()->with('flash', [
                'message' => 'Campanha não está em processamento (Erro batch_id não encontrado)',
                'type' => 'error'
            ]);
        }
        
        //cancel batch
        $batch = Bus::findBatch($campaign->batch_id);

        if(!$batch) {
            return back()->with('flash', [
                'message' => 'Lote não encontrado',
                'type' => 'error'
            ]);
        }

        $batch->cancel();
        Artisan::call("queue:clear --queue={$campaign->queueName()}");
        $campaign->campaign_processing_status_id = LeadDistributionCampaign::STATUS_CANCELED;
        $campaign->status = 0;
        $campaign->save();

        return back()->with('flash', [
            'message' => 'Campanha cancelada com sucesso',
            'type' => 'success'
        ]);

    }

    public function updateUserCampaigns(Request $request){

        $user = User::find($request->user_id);

        if(!$user){
            //api return
            return response()->json([
                'message' => 'Usuário não encontrado',
                'type' => 'error'
            ], 404);
        }

        //verify if the user is subordinate to the logged user
        if(!$request->user()->isSuperior() || $user->superior_id != $request->user()->id){
            //api return
            return response()->json([
                'message' => 'Usuário não é subordinado ao usuário logado',
                'type' => 'error'
            ], 403);
        }

        //sync the campaigns
        $user->leadDistributionCampaigns()->sync($request->campaigns);

        //api return
        return response()->json([
            'message' => 'Campanhas atualizadas com sucesso',
            'type' => 'success'
        ], 200);

    }

    public function campaignsUsersPage(Request $request){

        //if user is not superior, redirect to dashboard
        if(!$request->user()->isSuperior()){
            return redirect()->route('dashboard');
        }

        //get the users that are subordinate to the logged user and the campaigns available for each user
        $users = User::select(["id", "name"])
            ->when($request->query('search'), function($query, $search){
                return $query->where('name', 'like', "%$search%");
            })
            ->where('superior_id', $request->user()->id)
            ->with([
                'leadDistributionCampaigns' => function($query){
                    $query->select(["lead_distribution_campaigns.id as value", "lead_distribution_campaigns.name as label", 'lead_distribution_campaign_user.limit'])
                        ->where('status', 1)
                    ;
                }
            ])
            ->paginate(5);

        //get the campaigns that are available for the logged user
        $campaigns = $request->user()->leadDistributionCampaigns()
            ->where('status', 1)
            ->select(["lead_distribution_campaigns.id as value", "lead_distribution_campaigns.name as label"])
            ->get();

        //do the same above but without the pivot
        $campaigns = $campaigns->map(function($campaign){
            return [
                'label' => $campaign->label,
                'value' => $campaign->value
            ];
        });

        return Inertia::render('LeadDistribution/CampaignsUsers', [
            'campaigns' => $campaigns,
            'users' => $users
        ]);
    }

    public function updateLimite(Request $request){
            
        $user = User::find($request->user_id);

        if(!$user){
            return back()->with('flash', [
                'message' => 'Usuário não encontrado',
                'type' => 'error'
            ]);
        }

        //verify if the user is subordinate to the logged user
        if(!$request->user()->isSuperior() || $user->superior_id != $request->user()->id){

            return back()->with('flash', [
                'message' => 'Usuário não é subordinado ao usuário logado',
                'type' => 'error'
            ]);
        }

        //update or create
        LeadDistributionCampaignUser::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'lead_distribution_campaign_id' => $request->campaign_id
            ],
            [
                'limit' => $request->limit
            ]
        );

        return back()->with('flash', [
            'message' => 'Limite atualizado com sucesso',
            'type' => 'success'
        ]);
    }

}
