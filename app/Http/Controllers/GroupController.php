<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use App\Models\LeadDistributionCampaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->query('search');
        $status = $request->query('status', 'active');

        $groups = Group::select('id', 'name', 'is_active')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->when(in_array($status, ['active', 'inactive']) ? $status : false, function ($query, $status) {
                return $query->where('is_active', $status === 'active' ? true : false);
            })
            ->paginate(20);

        //preserve search query
        $groups->appends(['search' => $search]);
        $groups->appends(['status' => $status]);

        return Inertia::render('Groups/List', [
            'groups' => $groups,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Groups/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $id = Group::create($request->all());

        LeadDistributionCampaign::find($request->campaigns)->each(function ($campaign) use ($id) {
            $campaign->groups()->attach($id);
        });

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Grupo criado com sucesso!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        return Inertia::render('Groups/Edit', [
            'group' => $group,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $group->update($request->all());

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Grupo atualizado com sucesso!',
        ]);
    }

    public function users(string $id)
    {

        $users = Group::find($id)->users()
            ->where('is_active', true)
            ->get();

        return response()->json($users);
    }

    public function activate(Group $group){
        
        $group->update(['is_active' => true]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Grupo ativado com sucesso!',
        ]);

    }

    public function deactivate(Group $group){
        
        $group->update(['is_active' => false]);

        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Grupo desativado com sucesso!',
        ]);
    }

    public function getAllBuisnesses()
    {
        $groups = Group::select('id', 'name')
            ->where('is_active', true)    
            ->get();

        return response()->json($groups);
    }
}
