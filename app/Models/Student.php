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
 * To search data with name or pinyin, use this query
 * (replace '$query' with your own query):
 * { $or: [{ name_index: '$query' }, { pinyin: '$query' }] }
 *
 * @property string $id
 * @property string $classId
 * @property string $gradeId
 * @property string $name
 * @property array $name_index
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
    protected $hidden = ['name_index', 'pinyin'];
    protected $dates = ['birthday'];

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
            });
    }

    public static function dropCollection()
    {
        Schema::connection('mongodb')->drop('students');
    }
}
