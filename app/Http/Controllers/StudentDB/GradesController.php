<?php

namespace App\Http\Controllers\StudentDB;

use App\Http\Controllers\Controller;
use App\Models\Student;
use DateInterval;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GradesController extends Controller
{
    public function index()
    {
        $ttl = new DateInterval('PT10M');

        return Cache::remember('studentdb_grades', $ttl, function () {
            return Student::groupBy('gradeId')
                ->orderBy('gradeId')
                ->pluck('gradeId')
                ->transform(function (string $gradeId) {
                    $classes = Student::where('gradeId', $gradeId)
                        ->groupBy('classId')
                        ->get()->count();

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
            ->groupBy('gradeId')
            ->pluck('gradeId')
            ->first();

        if (empty($gradeId)) {
            throw new NotFoundHttpException();
        }

        $ttl = new DateInterval('PT10M');

        return Cache::remember("studentdb_grade_{$gradeId}", $ttl,
            function () use ($gradeId) {
                $classes = Student::where('gradeId', $gradeId)
                    ->groupBy('classId')
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