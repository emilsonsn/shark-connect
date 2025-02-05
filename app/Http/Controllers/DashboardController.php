<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\LeadsTakenByMonthReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request){

        if($request->user()->hasPermissionTo('list-count-leads-taken-by-all')){
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

    private function adminUserDashboard(Request $request){

        $key = "moreConsumedOnMonth:admin";

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
                ->groupBy('users.id', 'users.name', 'groups.name')
                ->orderBy('total', 'desc')
                ->first();

            //cache it for 2 hours
            Cache::put($key, $moreConsumedOnMonth, 120);
        
        }
        
        $key = "moreConsumedToday:admin";

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
                ->groupBy('users.id', 'users.name', 'groups.name')
                ->orderBy('total', 'desc')
                ->first();

            //cache it for 2 hours
            Cache::put($key, $moreConsumedToday, 120);
        
        }

        $key = "moreUsedCampaignOnMonth";

        if(Cache::has($key)){
            $moreUsedCampaignOnMonth = Cache::get($key);
        } 
        else {
            $moreUsedCampaignOnMonth = DB::select("SELECT name, total - remaining as consumed FROM lead_distribution_campaigns where month(last_recycle_date) = month(now()) order by consumed desc limit 1;");

            if(count($moreUsedCampaignOnMonth) > 0){
                $moreUsedCampaignOnMonth = $moreUsedCampaignOnMonth[0];
            } else {
                $moreUsedCampaignOnMonth = null;
            }

            //cache it for 2 hours
            Cache::put($key, $moreUsedCampaignOnMonth, 120);
        
        }

        $key = "moreUsedCampaignToday";

        if(Cache::has($key)){
            $moreUsedCampaignToday = Cache::get($key);
        } 
        else {

            $moreUsedCampaignToday = DB::select("
                SELECT count(*) as total, t2.name FROM lead_distribution_prospect as t1
                join lead_distribution_campaigns as t2
                on t1.lead_distribution_campaign_id = t2.id
                where caught_at is not null and date(caught_at) = date(now()) group by lead_distribution_campaign_id, t2.name order by total desc limit 1;
            ");

            if(count($moreUsedCampaignToday) > 0){
                $moreUsedCampaignToday = $moreUsedCampaignToday[0];
            } else {
                $moreUsedCampaignToday = null;
            }

            //cache it for 2 hours
            Cache::put($key, $moreUsedCampaignToday, 120);
        
        }
        
        return Inertia::render('Dashboard', [
            'moreConsumedOnMonth' => $moreConsumedOnMonth,
            'moreConsumedToday' => $moreConsumedToday,
            'moreUsedCampaignOnMonth' => $moreUsedCampaignOnMonth,
            'moreUsedCampaignToday' => $moreUsedCampaignToday,
        ]);

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
