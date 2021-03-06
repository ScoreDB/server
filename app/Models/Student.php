<?php

namespace App\Models;

use App\Services\Integrations\Github;
use App\Services\Parsing\StudentInfoFormatter;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Student Model
 *
 * Data is stored in MongoDB.
 *
 * To search data with name or pinyin, use this query
 * (replace '$query' with your own query):
 * { $or: [{ name_index: '$query' }, { pinyin: '$query' }] }
 *
 * @property string $id
 * @property string $gradeId
 * @property string $classId
 * @property string $name
 * @property array $name_index
 * @property array $pinyin
 * @property string $gender
 * @property ?Carbon $birthday
 * @property ?string $eduid
 * @property array $photos
 * @mixin Builder
 * @package App\Models
 */
class Student extends Model
{
    public $timestamps = false;

    protected $keyType = 'string';

    protected $hidden
        = [
            '_id', 'name_index', 'pinyin',
        ];

    protected $casts
        = [
            'name_index' => 'array',
            'pinyin'     => 'array',
        ];

    protected $dates
        = [
            'birthday',
        ];

    public function setNameIndexAttribute(array $value)
    {
        $this->attributes['name_index'] = self::encodeArray($value);
    }

    public function setPinyinAttribute(array $value)
    {
        $this->attributes['pinyin'] = self::encodeArray($value);
    }

    public function getPhotosAttribute()
    {
        $ttl = new DateInterval('PT1M');

        return Cache::remember("student_{$this->id}_photos", $ttl, function () {
            /** @var Github $github */
            $github = resolve(Github::class);
            $result = [];
            foreach ($github->getPhotoPatterns() as $pattern) {
                $photo = StudentInfoFormatter::format($pattern, $this);
                $url   = $github->getFileUrl($photo);
                if (isset($url)) {
                    array_push($result, $url);
                }
            }

            return $result;
        });
    }

    protected static function encodeArray(array $value)
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE);
        $json = str_replace('[', '{', $json);
        $json = str_replace(']', '}', $json);

        return $json;
    }
}
