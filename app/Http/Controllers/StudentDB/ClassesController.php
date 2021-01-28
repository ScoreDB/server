<?php

namespace App\Http\Controllers\StudentDB;

use App\Http\Controllers\Controller;
use App\Models\Student;
use DateInterval;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClassesController extends Controller
{
    public function show(string $classId)
    {
        $classId = mb_strtoupper($classId);

        $classId = Student::where('classId', $classId)
            ->groupBy('classId')
            ->pluck('classId')
            ->first();

        if (empty($classId)) {
            throw new NotFoundHttpException();
        }

        $ttl = new DateInterval('PT10M');

        return Cache::remember("studentdb_class_{$classId}", $ttl,
            function () use ($classId) {
                $students = Student::where('classId', $classId)
                    ->orderBy('id')
                    ->get(['id', 'name', 'gender']);

                $gradeId = Student::where('classId', $classId)
                    ->groupBy('gradeId')
                    ->pluck('gradeId')
                    ->first();

                return [
                    'id'            => $classId,
                    'gradeId'       => $gradeId,
                    'students'      => $students,
                    'studentsCount' => $students->count(),
                ];
            });
    }
}
