<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Group;
use App\Models\LeadDistributionCampaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:manage-users')
            ->except(['getAvailableCampaigns', 'setCurrentCampaign']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {

        $search = $request->query('search');
        $status = $request->query('status', 'active');

        //get all users with the respective superior user
        $users = (new User)->searchWithSuperiors($search, $status, 10);

        return Inertia::render('User/List', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $groups = Group::select()
            ->active()
            ->get();

        return Inertia::render('User/Register', [
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        (new CreateNewUser)->create($request->all());

        //return flash message
        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Usuário criado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load('group');

        $superiorUserList = Group::find($user->group->id)
            ->users()
            ->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->get();

        return Inertia::render('User/Edit', [
            'user' => $user,
            'groups' => Group::all(),
            'superiorUserList' => $superiorUserList
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {

        $update = $request->all();

        //if password is empty, remove it from the request
        if (empty($request->password)) {
            unset($update['password']);
        }

        //if there is password hash it
        if (isset($update['password'])) {
            $update['password'] = Hash::make($update['password']);
        }

        $update['superior_id'] = $update['superior_user_id'];

        //update user
        $user->update($update);

        //return flash message
        return back()->with('flash', [
            'type' => 'success',
            'message' => 'Usuário atualizado com sucesso!'
        ]);
    }

    public function deactivate(User $user)
    {

        $user->update([
            "is_active" => false
        ]);

        //logout user
        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getAuthIdentifier())
            ->delete();
        $user->tokens()->delete();

        return back()->with('flash', [
            'message' => 'Usuario desativado com sucesso!',
            'type' => 'success'
        ]);
    }

    public function activate(User $user)
    {
        $user->update([
            'is_active' => true
        ]);

        return back()->with('flash', [
            'message' => 'Usuario ativado com sucesso!',
            'type' => 'success'
        ]);
    }

    public function getAvailableCampaigns(Request $request){

        $user = $request->user();

        $campaigns = $user->getAvailableCampaigns()->toArray();

        return response()->json($campaigns);

    }

    public function setCurrentCampaign(Request $request){

        $user = $request->user();

        $campaign = LeadDistributionCampaign::find($request->campaign_id);

        if(!$campaign){
            return response()->json([
                'message' => 'Campanha não encontrada!'
            ], 404);
        }

        if(!$campaign->status || $campaign->remaining <= 0){
            return response()->json([
                'message' => 'Campanha não está disponivel!'
            ], 403);
        }

        $ids = $user->getAvailableCampaigns()->pluck('id')->toArray();

        $isAvailable = in_array($campaign->id, $ids);

        if(!$isAvailable){
            return response()->json([
                'message' => 'Campanha não está disponivel para o usuário!'
            ], 403);
        }

        $user->setCurrentCampaign($campaign);

        return response()->json([
            'message' => 'Campanha atualizada com sucesso!'
        ]);

    }

    public function resetToken(User $user){

        $user->resetToken();

        return back()->with('flash', [
            'message' => 'Token resetado com sucesso!',
            'type' => 'success'
        ]);

    }

    public function getAllByGroup(Request $request, $groupId)
    {

        $users = User::select()
            ->active()
            ->where('group_id', $groupId)
            ->get();

        return response()->json($users);
    }
}
