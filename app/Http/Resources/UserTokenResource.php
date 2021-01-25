<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * API Token Resource
 *
 * @property int id
 * @property string tokenable_type
 * @property int tokenable_id
 * @property string name
 * @property string token
 * @property string[] abilities
 * @property Carbon last_used_at
 * @property Carbon created_at
 * @property Carbon updated_at
 * @mixin PersonalAccessToken
 * @package App\Http\Resources
 */
class UserTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     *
     * @noinspection PhpMissingParamTypeInspection
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'roles'              => $this->abilities,
            'createdAt'          => $this->created_at,
            'createdAtReadable'  => self::toReadableDiff($this->created_at),
            'lastUsedAt'         => $this->last_used_at,
            'lastUsedAtReadable' => self::toReadableDiff($this->last_used_at),
        ];
    }

    protected static function toReadableDiff(?Carbon $time)
    {
        if (empty($time))
            return __('Never');

        return $time->diffForHumans();
    }
}
