<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('login', $request->email)
                ->select('users.*')
                ->join('groups', 'groups.id', '=', 'users.group_id')
                ->where('users.is_active', true)
                ->where('groups.is_active', true)
                ->first();

            if ($user &&
                Hash::check($request->password, $user->password)) {
                Auth::logoutOtherDevices($request->password);
                try {
                    $this->deleteOtherSessionRecords($user->id);
                } catch (\Exception $e) {
                    // do nothing
                }
                return $user;
            }
        });
    }

    protected function deleteOtherSessionRecords($id)
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', $id)
            ->delete();
    }

    /**
     * Configure the permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
