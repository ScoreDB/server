<?php

namespace App\Services\Parsing;

use App\Models\Student;

class StudentInfoFormatter
{
    public static function format(string $pattern, Student $student) : string
    {
        $result = $pattern;
        foreach ($student->toArray() as $key => $value) {
            $result = str_replace("{{$key}}", $value, $result);
        }

        return $result;
    }
}
