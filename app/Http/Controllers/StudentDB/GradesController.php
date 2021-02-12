<?php

namespace App\Http\Controllers\StudentDB;

use App\Http\Controllers\Controller;
use App\Models\Student;
use DateInterval;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\Cache;

class GradesController extends Controller
{
    public function index()
    {
        $ttl = new DateInterval('PT10M');

        return Cache::remember('studentdb_grades', $ttl, function () {
            return Student::select('gradeId')
                ->distinct()
                ->orderBy('gradeId')
                ->pluck('gradeId')
                ->transform(function (string $gradeId) {
                    $classes = Student::select('classId')
                        ->distinct()
                        ->where('gradeId', $gradeId)
                        ->count('classId');

                    $students = Student::where('gradeId', $gradeId)->count();

                    return [
                        'id'            => $gradeId,
                        'classesCount'  => $classes,
                        'studentsCount' => $students,
                    ];
                });
        });
    }

    public function show(string $gradeId)
    {
        $gradeId = mb_strtoupper($gradeId);

        $gradeId = Student::where('gradeId', $gradeId)
            ->pluck('gradeId')
            ->first();

        if (empty($gradeId)) {
            throw new RecordsNotFoundException();
        }

        $ttl = new DateInterval('PT10M');

        return Cache::remember("studentdb_grade_{$gradeId}", $ttl,
            function () use ($gradeId) {
                $classes = Student::select('classId')
                    ->distinct()
                    ->where('gradeId', $gradeId)
                    ->orderBy('classId')
                    ->pluck('classId')
                    ->transform(function (string $classId) {
                        $students = Student::where('classId', $classId)
                            ->count();

                        return [
                            'id'            => $classId,
                            'studentsCount' => $students,
                        ];
                    });

                $count = Student::where('gradeId', $gradeId)->count();

                return [
                    'id'            => $gradeId,
                    'classes'       => $classes,
                    'classesCount'  => $classes->count(),
                    'studentsCount' => $count,
                ];
            });
    }
}
