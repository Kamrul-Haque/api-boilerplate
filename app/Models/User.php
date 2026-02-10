<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasAuthorizeOwner;
use App\Traits\Trashable;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasAuthorizeOwner;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;
    use Trashable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_furigana',
        'email',
        'phone',
        'password',
        'avatar',
        'email_verified_at',
        'active_role_id',
        'route_code_access_token',
        'route_code_r_token',
        'route_code_registered',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'route_code_registered' => 'boolean',
        ];
    }

    /**
     * Checks if the user has the given Role
     */
    public function hasRole(int|string $role): bool
    {
        if (!is_numeric($role)) {
            $role = Role::whereName($role)->first();
        } else {
            $role = Role::find($role);
        }

        return $this->active_role_id === $role->id;
    }

    /**
     * Checks if the user has the given Permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->contains($permission);
    }

    /**
     * Assign the given Role to the user
     *
     * @throws Exception
     */
    public function assignRole(int|string $role): void
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->first();
        } else {
            $role = Role::find($role);
        }

        $this->roles()->sync($role->id, false);

        if (!$this->getRawOriginal('active_role_id')) {
            $this->update(['active_role_id' => $role->id]);
        }
    }

    /**
     * Get permissions assigned to the 'active_role' of the user
     */
    public function permissions(): Collection
    {
        return $this->active_role->permissions->flatten()->pluck('name')->unique();
    }

    public function active_role(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'active_role_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function trashes(): HasMany
    {
        return $this->hasMany(Trash::class);
    }
}
