<?php

namespace App\Http\Controllers\StudentDB;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    public function show(Request $request, string $id)
    {
        $id      = mb_strtoupper($id);
        $student = Student::where('id', $id)
            ->orWhere('eduid', $id)
            ->firstOrFail();

        $result = $student->toArray();
        if ($request->boolean('photos', false)) {
            $result['photos'] = $student->photos;
        }

        return $result;
    }
}
