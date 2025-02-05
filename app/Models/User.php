<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'login',
        'password',
        'is_active',
        'group_id',
        'superior_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [];

    public function isSuperior(): bool
    {
        return $this->subordinates()->exists();
    }

    public function isSubordinate(): bool
    {
        return $this->superior()->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasPermissionTo('admin');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function superior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'superior_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'superior_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'lead_distribution_prospect')
            ->using(LeadDistributionProspect::class)
            ->withPivot(['tabulation_id', 'margin', 'convenant', 'organ', 'id', 'caught_at'])
            ->withTimestamps();
    }

    public function leadDistributionCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(LeadDistributionCampaign::class)
            ->using(LeadDistributionCampaignUser::class)
            ->withPivot(['user_id', 'id', 'lead_distribution_campaign_id', 'limit', 'caught_today'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function leadDistributionConfig(): HasOne
    {
        return $this->hasOne(LeadDistributionUserConfig::class);
    }

    public function givePermissionTo(string $permission): void
    {

        $p = Permission::getPermission($permission);

        $this->permissions()->attach($p);

        Cache::forget("permissions_of_user_{$this->id}");
    }

    public function removePermission(string $permission): void
    {

        $p = Permission::getPermission($permission);

        $this->permissions()->detach($p);

        Cache::forget("permissions_of_user_{$this->id}");
    }

    public function hasPermissionTo(string $permission): bool
    {

        /** @var Collection<Permission> */
        $permissionsOfUser = Cache::rememberForever("permissions_of_user_{$this->id}", function () {
            return $this->permissions()->get();
        });

        return $permissionsOfUser->where('permission', $permission)->isNotEmpty();
    }

    public function getPermissions(): Collection
    {
        /** @var Collection<Permission> */
        $permissionsOfUser = Cache::remember("permissions_of_user_{$this->id}", 60 * 15, function () {
            $permissions = $this->permissions()->get();

            //if user is a superior to at least one user, add 'dashboard-client' permission to his permissions
            if ($this->isSuperior() && !$this->hasPermissionTo('view-campaigns')) {
                $permissions->push(Permission::getPermission('view-campaigns'));
                //attatch permission to user
                $this->givePermissionTo('view-campaigns');
            }

            return $permissions;
        });

        return $permissionsOfUser;
    }

    public function getCurrentCampaign(): ?LeadDistributionCampaign
    {
        $currentCampaign = Cache::rememberForever("group-{$this->group->id}:current_campaign_of_user_{$this->id}", function () {

            $campanha = $this->leadDistributionConfig()->firstOrNew([]);

            if (!$campanha->exists) {

                $campanha->fill([
                    'user_id' => $this->id,
                    'current_campaign_id' => null,
                    'limit_leads' => 500
                ]);

                $campanha->save();

                return null;
            }

            $campanha = $campanha->currentCampaign()->first();

            if (!$campanha) {
                return null;
            }

            if ($campanha->remaining == 0 || !$campanha->status) {
                Cache::forget("group-{$this->group->id}:current_campaign_of_user_{$this->id}");

                $this->leadDistributionConfig()->update([
                    'current_campaign_id' => null
                ]);

                return null;
            }

            $isCampaignAvailable = $this->group->leadDistributionCampaigns()->where('lead_distribution_campaigns.id', $campanha->id)->first();

            if (!$isCampaignAvailable) {
                Cache::forget("group-{$this->group->id}:current_campaign_of_user_{$this->id}");

                $this->leadDistributionConfig()->update([
                    'current_campaign_id' => null
                ]);

                return null;
            }

            return $campanha;
        });

        return $currentCampaign;
    }

    public function getAvailableCampaigns(): Collection
    {
        $availableCampaigns = $this->group->leadDistributionCampaigns()
            ->where('remaining', '>', 0)
            ->where('status', true)
            ->select('lead_distribution_campaigns.id', 'name', 'last_recycle_date')
            ->orderBy('last_recycle_date', 'asc')
            ->get();

        return $availableCampaigns;
    }

    public function setCurrentCampaign(LeadDistributionCampaign $campaign): void
    {
        $this->leadDistributionConfig()->updateOrCreate([], [
            'current_campaign_id' => $campaign->id
        ]);

        Cache::forget("group-{$this->group->id}:current_campaign_of_user_{$this->id}");
    }

    /**
     * Get all users with the respective superior user and group
     *
     * @param string|null $search
     * @param $active
     * @param integer $perPage
     * @return LengthAwarePaginator
     */
    public function searchWithSuperiors(?string $search = "", $active, int $perPage = 10): LengthAwarePaginator
    {

        return $this->select(
            ["id", "login", "is_active", "superior_id", "group_id", "updated_at"]
        )
            ->with(['superior:id,name', 'group:id,name'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('login', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhereHas('group', function ($query) use ($search) {
                            $query->where('name', 'like', "%$search%");
                        });
                });
            })
            ->when(in_array($active, ['active', 'inactive']) ? $active : false, function ($query, $active) {
                return $query->where('is_active', $active == 'active');
            })

            ->paginate($perPage)
            ->withQueryString();
    }

    public function resetToken()
    {
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;
        $this->two_factor_confirmed_at = null;
        $this->save();
    }
}
