<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class Permission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public static function getPermission(string $permission): Permission
    {
        
        $p = self::getAllFromCache()->where('permission', $permission)->first();

        if(!$p){
            $p = Permission::query()->create(compact('permission'));
        }

        return $p;
    }

    public static function getAllFromCache(): Collection
    {
       //se ja existir ele retorna o cache, se não ele cria fazendo uma query e retorna
        $permissions = Cache::rememberForever("permissions", function(){
            return Permission::all();
        });

        return $permissions;
    }

    public static function existsOnCache(string $permission): bool
    {
        return self::getAllFromCache()->where('permission', $permission)->isNotEmpty();
    }
}
