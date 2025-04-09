<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\LeadsTakenByMonthReport;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request){

        $auth = Auth::user()->refresh();
        if(in_array($auth->group_id, [1, 3, 5])){
            return $this->adminUserDashboard($request);
        }

        return $this->normalUserDashboard($request);
        
    }

    private function normalUserDashboard(Request $request){

        $key = "moreConsumedOnMonth:" . $request->user()->id;

        if(Cache::has($key)){
            $moreConsumedOnMonth = Cache::get($key);
        } 
        else {

            $moreConsumedOnMonth = DB::query()
                ->select('users.id as id', 'users.name as name', 'groups.name as group_name', DB::raw('SUM(total) as total'))
                ->from('leads_taken_by_month_report')
                ->join('users', 'users.id', '=', 'leads_taken_by_month_report.user_id')
                ->join('groups', 'groups.id', '=', 'users.group_id')
                ->where('year', date('Y'))
                ->where('month', date('m'))
                ->whereIn('users.id', $request->user()->subordinates()->pluck('id'))
                ->groupBy('users.id', 'users.name', 'groups.name')
                ->orderBy('total', 'desc')
                ->first();

            //cache it for 2 hours
            Cache::put($key, $moreConsumedOnMonth, 120);
        
        }

        $key = "moreConsumedToday:" . $request->user()->id;

        if(Cache::has($key)){
            $moreConsumedToday = Cache::get($key);
        } 
        else {
            $moreConsumedToday = DB::query()
                ->select('users.id as id', 'users.name as name', 'groups.name as group_name', DB::raw('SUM(total) as total'))
                ->from('leads_taken_by_month_report')
                ->join('users', 'users.id', '=', 'leads_taken_by_month_report.user_id')
                ->join('groups', 'groups.id', '=', 'users.group_id')
                ->where('year', date('Y'))
                ->where('month', date('m'))
                ->where('day', date('d'))
                ->whereIn('users.id', $request->user()->subordinates()->pluck('id'))
                ->groupBy('users.id', 'users.name', 'groups.name')
                ->orderBy('total', 'desc')
                ->first();

            //cache it for 2 hours
            Cache::put($key, $moreConsumedToday, 120);
        
        }

        return Inertia::render('Dashboard', [
            // 'subordinates' => $subordinates ?? [],
            'moreConsumedOnMonth' => $moreConsumedOnMonth,
            'moreConsumedToday' => $moreConsumedToday,
        ]);

    }

    private function adminUserDashboard(Request $request)
    {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
    
        $moreConsumedOnMonth = DB::query()
            ->select('users.id as id', 'users.name as name', 'groups.name as group_name', DB::raw('SUM(total) as total'))
            ->from('leads_taken_by_month_report')
            ->join('users', 'users.id', '=', 'leads_taken_by_month_report.user_id')
            ->join('groups', 'groups.id', '=', 'users.group_id')
            ->where('year', $year)
            ->where('month', $month)
            ->groupBy('users.id', 'users.name', 'groups.name')
            ->orderBy('total', 'desc')
            ->first();
    
        $rankingConsumedOnMonth = DB::query()
            ->select('users.id as id', 'users.name as name', 'groups.name as group_name', DB::raw('SUM(total) as total'))
            ->from('leads_taken_by_month_report')
            ->join('users', 'users.id', '=', 'leads_taken_by_month_report.user_id')
            ->join('groups', 'groups.id', '=', 'users.group_id')
            ->where('year', $year)
            ->where('month', $month)
            ->groupBy('users.id', 'users.name', 'groups.name')
            ->orderBy('total', 'desc')
            ->get();
                
        $moreConsumedToday = DB::query()
            ->select('users.id as id', 'users.name as name', 'groups.name as group_name', DB::raw('SUM(total) as total'))
            ->from('leads_taken_by_month_report')
            ->join('users', 'users.id', '=', 'leads_taken_by_month_report.user_id')
            ->join('groups', 'groups.id', '=', 'users.group_id')
            ->where('year', $year)
            ->where('month', $month)
            ->where('day', $day)
            ->groupBy('users.id', 'users.name', 'groups.name')
            ->orderBy('total', 'desc')
            ->first();
    
        $moreUsedCampaignOnMonth = DB::selectOne("
            SELECT name, total - remaining as consumed 
            FROM lead_distribution_campaigns 
            WHERE month(last_recycle_date) = month(now()) 
            ORDER BY consumed DESC 
            LIMIT 1
        ");
    
        $moreUsedCampaignToday = DB::selectOne("
            SELECT count(*) as total, t2.name 
            FROM lead_distribution_prospect as t1
            JOIN lead_distribution_campaigns as t2
            ON t1.lead_distribution_campaign_id = t2.id
            WHERE caught_at IS NOT NULL 
            AND DATE(caught_at) = DATE(NOW()) 
            GROUP BY lead_distribution_campaign_id, t2.name 
            ORDER BY total DESC 
            LIMIT 1
        ");

        return Inertia::render('Dashboard', [
            'moreConsumedOnMonth' => $moreConsumedOnMonth,
            'moreConsumedToday' => $moreConsumedToday,
            'moreUsedCampaignOnMonth' => $moreUsedCampaignOnMonth,
            'moreUsedCampaignToday' => $moreUsedCampaignToday,
            'rankingConsumedOnMonth' => $rankingConsumedOnMonth,
        ]);
    }

    public function userCampaignDetails($userId)
    {
        $campaigns = DB::table('lead_distribution_prospect as prospect')
            ->join('lead_distribution_campaigns as campaign', 'prospect.lead_distribution_campaign_id', '=', 'campaign.id')
            ->select(
                'campaign.name as campaign_name',
                DB::raw('COUNT(prospect.id) as total_leads'),
                DB::raw('SUM(CASE WHEN prospect.tabulation_id IN (1,5) THEN 1 ELSE 0 END) as leads_abertos'),
                DB::raw('SUM(CASE WHEN prospect.tabulation_id NOT IN (1,5) THEN 1 ELSE 0 END) as leads_finalizados')
            )
            ->where('prospect.user_id', $userId)
            ->groupBy('campaign.id', 'campaign.name')
            ->orderBy('campaign.id', 'DESC')
            ->take(100)
            ->get();
    
        return response()->json($campaigns);
    }    

    public function monthlyReport(Request $request){

        $isSuperior = $request->user()->isSuperior();

        $isMaster = $request->user()->hasPermissionTo('manage-groups');

        if(!$isMaster && !$isSuperior){
            abort(403);
        }

        $start_date = isset($request->inicio) ? Carbon::parse($request->inicio) : Carbon::today();
        $end_date = isset($request->fim) ? Carbon::parse($request->fim) : Carbon::today();

        $report = LeadsTakenByMonthReport::select('users.id','users.login as login', 'groups.name as group_name', DB::raw('SUM(total) as total'))
            ->join('users', 'users.id', '=', 'leads_taken_by_month_report.user_id')
            ->join('groups', 'groups.id', '=', 'users.group_id')
            ->when($request->query('search'), function($query, $search) {
                return $query->where('users.login', 'like', "%{$search}%");
            })
            ->when($request->query('group'), function($query, $group) {
                return $query->where('groups.id', $group);
            })
            ->when($isSuperior == true && !$request->user()->hasPermissionTo('manage-groups'), function($query) use ($request) {
                return $query->whereIn('users.id', $request->user()->subordinates()->pluck('id'));
            })
            ->where(function ($query) use ($start_date, $end_date) {
                if ($start_date && $end_date) {
                    $query->where(function ($query) use ($start_date, $end_date) {
                        $query->where('year', '>', $start_date->year)
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('year', $start_date->year)
                                    ->where('month', '>', $start_date->month);
                            })
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('year', $start_date->year)
                                    ->where('month', $start_date->month)
                                    ->where('day', '>=', $start_date->day);
                            });
                    })
                    ->where(function ($query) use ($start_date, $end_date) {
                        $query->where('year', '<', $end_date->year)
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('year', $end_date->year)
                                    ->where('month', '<', $end_date->month);
                            })
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('year', $end_date->year)
                                    ->where('month', $end_date->month)
                                    ->where('day', '<=', $end_date->day);
                            });
                    });
                } elseif ($start_date) {
                    $query->where(function ($query) use ($start_date) {
                        $query->where('year', '>', $start_date->year)
                            ->orWhere(function ($query) use ($start_date) {
                                $query->where('year', $start_date->year)
                                    ->where('month', '>', $start_date->month);
                            })
                            ->orWhere(function ($query) use ($start_date) {
                                $query->where('year', $start_date->year)
                                    ->where('month', $start_date->month)
                                    ->where('day', '>=', $start_date->day);
                            });
                    });
                }
            })
            ->groupBy('id', 'login', 'group_name')
            ->orderBy('total', 'desc')
            ->paginate(10);

        //preserve search query
        $report->appends(['search' => $request->query('search')]);
        $report->appends(['inicio' => $request->query('inicio')]);
        $report->appends(['fim' => $request->query('fim')]);

        if($isMaster){
            $groups = Group::select('id', 'name')
                ->active()
                ->get();
        }

        return Inertia::render('Dashboard/MonthReport', [
            'report' => $report,
            'groups' => $groups ?? [],
            'currentMonth' => date('m'),
            'currentYear' => date('Y'),
        ]);

    }
}
