<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $email_verified_at
 * @property string $password
 * @property bool $is_admin
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection | Provider[] $providers
 * @property-read ?string $avatar
 * @property-read string[] $roles
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden
        = [
            'email_verified_at',
            'password',
            'is_admin',
            'remember_token',
            'updated_at',
            'providers',
        ];

    protected $appends
        = [
            'avatar',
            'roles',
        ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts
        = [
            'is_admin'          => 'bool',
            'email_verified_at' => 'datetime',
        ];

    public function isAdmin()
    {
        return $this->is_admin === true;
    }

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }

    public function getAvatarAttribute(?string $value)
    {
        if (isset($value)) {
            return $value;
        }

        foreach ($this->providers as $provider) {
            if (isset($provider->avatar)) {
                return $provider->avatar;
            }
        }

        return null;
    }

    public function getRolesAttribute()
    {
        $result = [];

        foreach (Gate::abilities() as $ability => $_c) {
            if (Gate::forUser($this)->allows($ability)) {
                array_push($result, $ability);
            }
        }

        return $result;
    }
}
