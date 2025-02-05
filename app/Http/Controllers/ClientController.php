<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->query('search');

        if($search){
            $clients = Client::with('contacts')
            ->with('employers')
            ->when($search, function ($query, $search) {
                return $query->where('name', $search)
                    ->orWhere('cpf', $search)
                    ->orWhereHas('contacts', function ($query) use ($search) {
                        return $query->where(DB::raw("CONCAT(ddd, number)"), $search);
    
                    });
            })
            ->paginate(20);

            //preserve search query
            $clients->appends(['search' => $search]);
        }

        return Inertia::render(
            "Client/List",
            [
                "clients" => $clients ?? null
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $client = Client::select()
            ->where('id', $id)
            ->with('contacts')
            ->with('employers')
            ->with('addresses')
            ->first();

        return Inertia::render(
            "Client/Show",
            [
                "client" => $client
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        //
    }

    public function getAllContacts(Request $request, $id){

        //verify if user has this client as a lead
        $prospect = Client::join("lead_distribution_prospect", "lead_distribution_prospect.client_id", "=", "clients.id")
            ->where("lead_distribution_prospect.client_id", $id)
            ->where("lead_distribution_prospect.user_id", $request->user()->id)
            ->first();

        if(!$prospect){
            return response()->json(["error" => "You don't have permission to access this client"], 403);
        }

        $client = Client::find($id);
        $contacts = $client->contacts()->get();
        return response()->json($contacts);
    }
}
