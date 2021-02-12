<?php

namespace App\Http\Controllers\StudentDB;

use App\Http\Controllers\Controller;
use App\Models\Student;
use DateInterval;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Facades\Cache;

class ClassesController extends Controller
{
    public function show(string $classId)
    {
        $classId = mb_strtoupper($classId);

        $classId = Student::where('classId', $classId)
            ->pluck('classId')
            ->first();

        if (empty($classId)) {
            throw new RecordsNotFoundException();
        }

        $ttl = new DateInterval('PT10M');

        return Cache::remember("studentdb_class_{$classId}", $ttl,
            function () use ($classId) {
                $students = Student::where('classId', $classId)
                    ->orderBy('id')
                    ->get(['id', 'name', 'gender']);

                $gradeId = Student::where('classId', $classId)
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
