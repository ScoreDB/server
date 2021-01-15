<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Schema\Blueprint;

/**
 * Student Model
 *
 * Data is stored in MongoDB.
 *
 * @property string $id
 * @property string $classId
 * @property string $gradeId
 * @property string $name
 * @property array $pinyin
 * @property string $gender
 * @property ?Carbon $birthday
 * @property ?string $eduid
 * @mixin Builder
 * @package App\Models
 */
class Student extends Model
{
    public $timestamps = false;
    protected $connection = 'mongodb';
    protected $primaryKey = 'id';
    protected $hidden = ['pinyin'];
    protected $dates = ['birthday'];

    public static function dropCollection()
    {
        Schema::connection('mongodb')->drop('students');
    }

    public static function createCollection()
    {
        Schema::connection('mongodb')
            ->create('students', function (Blueprint $collection) {
                $collection->unique('id');
                $collection->index('classId');
                $collection->index('gradeId');
                $collection->unique('eduid', options: [
                    'partialFilterExpression' => [
                        'eduid' => ['$type' => 2],
                    ],
                ]);
                $collection->index([
                    'name'   => 'text',
                    'pinyin' => 'text',
                ]);
            });
    }
}
