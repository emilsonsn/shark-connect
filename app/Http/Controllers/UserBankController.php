<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankStoreRequest;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Http\Request;

class UserBankController extends Controller
{
    public function index(User $user)
    {
        return response()->json($user->banks()->latest('id')->get());
    }

    public function store(BankStoreRequest $request, User $user)
    {
        $user->banks()->create($request->only('name','username'));
        return back()->with('message', 'Banco adicionado')->with('type', 'success');
    }

    public function destroy(User $user, Bank $bank)
    {
        $bank->delete();
        return back()->with('message', 'Banco removido')->with('type', 'success');
    }
}
