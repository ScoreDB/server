<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * User Model
 *
 * @property int $id
 * @property string $provider
 * @property string $provided_id
 * @property string $avatar
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @mixin Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @package App\Models
 */
class Provider extends Model
{
    protected $fillable = ['provider', 'provided_id', 'user_id', 'avatar'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user () {
        return $this->belongsTo(User::class);
    }
}
