<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MyListHandleController;
use App\Http\Controllers\MyListController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('dashboard/campanhas/{userId}', [MyListController::class, 'getCampaingsUsedByUserOnDate'])
        ->name('dashboard.campanhasByUser');

    Route::prefix("usuarios")->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->name('usuarios.index');

        Route::get('criar', [UserController::class, 'create'])
            ->name('usuarios.create');
    
        Route::post('/', [UserController::class, 'store'])
            ->name('usuarios.store');
        
        Route::get('/{user}', [UserController::class, 'show'])
            ->name('usuarios.show');
        
        Route::get('/{user}/editar', [UserController::class, 'edit'])
            ->name('usuarios.edit');
        
        Route::put('/{user}', [UserController::class, 'update'])
            ->name('usuarios.update');
        
        Route::patch('/{user}/ativar', [UserController::class, 'activate'])
            ->name('usuarios.activate');

        Route::patch('/{user}/desativar', [UserController::class, 'deactivate'])
            ->name('usuarios.deactivate');

        Route::post('set-current-campaign', [UserController::class, 'setCurrentCampaign'])
            ->name('usuarios.set-current-campaign');

        Route::prefix("api")->group(function () {
            Route::get('available-campaigns', [UserController::class, 'getAvailableCampaigns'])
                ->name('usuarios.api.available-campaigns');

            Route::get('get-all-by-group/{groupId}', [UserController::class, 'getAllByGroup'])
                ->name('usuarios.api.all-by-group');
        });

        Route::patch('/{user}/token', [UserController::class, 'resetToken'])
            ->name('usuarios.resetToken');

    });

    Route::prefix("minha-lista")->group(function () {

        Route::get('/', [MyListHandleController::class, 'index'])
            ->name('lead-distribution.handle.index');

        Route::get('/campanhas', [MyListController::class, "index"])
            ->name('lead-distribution.index');

        Route::get('/campanhas/{leadDistributionCampaign}/editar', [MyListController::class, "edit"])
            ->name('lead-distribution.edit');

        Route::put('/campanhas/{leadDistributionCampaign}', [MyListController::class, "update"])
            ->name('lead-distribution.update');

        Route::put('/campanhas/{leadDistributionCampaign}/maxLeads', [MyListController::class, "updateMaxPerUser"])
            ->name('lead-distribution.updateMaxLeads');

        Route::patch('/campanhas/{leadDistributionCampaign}/ativar', [MyListController::class, "activate"])
            ->name('lead-distribution.activate');

        Route::patch('/campanhas/{leadDistributionCampaign}/desativar', [MyListController::class, "deactivate"])
            ->name('lead-distribution.deactivate');

        Route::patch('/campanhas/{leadDistributionCampaign}/reciclar', [MyListController::class, "recycle"])
            ->name('lead-distribution.recycle');

        Route::post('/campanhas/cancelar', [MyListController::class, "cancelProcessing"])
            ->name('lead-distribution.cancel');
        
        Route::get('/novo', [MyListHandleController::class, 'treatNewLead'])
            ->name('lead-distribution.handle.treatNewLead');

        Route::get("/ranking/{id}", [MyListController::class, "getCampaingRanking"])
            ->name("lead-distribution.ranking");

        Route::get('/{id}', [MyListHandleController::class, 'treatLead'])
            ->name('lead-distribution.handle.treatLead');

        Route::put('/{id}', [MyListHandleController::class, 'updateTabulation'])
            ->name('lead-distribution.handle.updateStatus');

        Route::post('/campanhas', [MyListController::class, "store"])
            ->name('lead-distribution.store');

        Route::get("/api/campanhas/get", [MyListController::class, "getAllCampaigns"])
            ->name("lead-distribution.api.campaigns");

        Route::get("/campanhas/usuarios", [MyListController::class, "campaignsUsersPage"])
            ->name("lead-distribution.campaigns.users");

        Route::put("/campanhasUsuarios/atualizar", [MyListController::class, "updateUserCampaigns"])
            ->name("lead-distribution.campaigns.users.update");

        Route::put("/campanhasUsuarios/atualizarLimite", [MyListController::class, "updateLimite"])
            ->name("lead-distribution.campaigns.users.updateLimit");

    });

    Route::prefix("grupos")->middleware("permission:manage-groups")->group(function () {

        Route::get('/', [GroupController::class, 'index'])
            ->name('grupos.index');

        Route::get('/criar', [GroupController::class, 'create'])
            ->name('grupos.create');
    
        Route::post('/', [GroupController::class, 'store'])
            ->name('grupos.store');
        
        Route::get('/{group}', [GroupController::class, 'show'])
            ->name('grupos.show');
        
        Route::get('/{group}/editar', [GroupController::class, 'edit'])
            ->name('grupos.edit');
        
        Route::put('/{group}', [GroupController::class, 'update'])
            ->name('grupos.update');

        Route::patch('/{group}/ativar', [GroupController::class, 'activate'])
            ->name('grupos.activate');

        Route::patch('/{group}/desativar', [GroupController::class, 'deactivate'])
            ->name('grupos.deactivate');
        
        Route::delete('/{group}', [GroupController::class, 'destroy'])
            ->name('grupos.destroy');

        Route::get('/{group}/usuarios', [GroupController::class, 'users'])
            ->name('grupos.users');

        Route::get("/api/a", [GroupController::class, "getAllBuisnesses"])
            ->name("grupos.api.index");

    });

    Route::prefix("clientes")->group(function () {

        Route::prefix("/{id}")->group(function () {

            Route::get('/contatos', [ClientController::class, 'getAllContacts'])
                ->name('clientes.api.contatos.index');
        });

    });

});
