<?php

namespace App\Http\Controllers;

use App\Models\LeadDistributionUserConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ConfigurationController extends Controller
{
    public function configureLeadsPerUserPage(Request $request)
    {

        $search = $request->query('search');
        $status = $request->query('status', 'active');

        $users = User::select(["id", "login", "is_active", "group_id"])
            ->with(['leadDistributionConfig:id,user_id,limit_leads','group:id,name'])
            ->when($search, function ($query, $search) {
                return $query->where(function($query) use ($search){
                    $query->where('login', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                });
            })
            ->when(in_array($status, ['active', 'inactive']) ? $status : false, function ($query, $active) {
                return $query->where('is_active', $active == 'active' ? true : false);
            })
            ->paginate(10);

        return Inertia::render('Configuration/LeadsConfig', [
            'users' => $users
        ]);
    }

    public function updateLeadsPerUser(Request $request){
            
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'limit_leads' => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($request->user_id);

        $user->leadDistributionConfig()->updateOrCreate([],[
            'limit_leads' => $request->limit_leads
        ]);

        //put limit on cache
        $key = 'leads-limit:'.$user->id;
        Cache::forever($key, $request->limit_leads);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Configuração de leads por usuário atualizada com sucesso!'
        ]);
    }

    public function updateAllLeadsPerUser(Request $request){
                
        $request->validate([
            'limit_leads' => 'required|integer|min:1'
        ]);

        LeadDistributionUserConfig::all()->each(function($config) use ($request){
            $config->update([
                'limit_leads' => $request->limit_leads
            ]);

            //put limit on cache
            $key = 'leads-limit:'.$config->user_id;
            Cache::forever($key, $request->limit_leads);
        });

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Configuração de leads por usuário atualizada com sucesso!'
        ]);
    }
}
