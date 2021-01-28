<?php

namespace App\Services\Parsing;

class StudentInfoMatcher
{
    public const PATTERN_ID = '/(^[xXcCgG][0-9]{6}$)|(^[0-9]{8}$)$/';

    public const PATTERN_CLASS_ID = '/^[xXcCgG][0-9]{4}$/';

    public const PATTERN_GRADE_ID = '/^[xXcCgG][0-9]{2}$/';

    public static function matchFirst(
        string $pattern,
        array $subjects
    ) : ?string {
        foreach ($subjects as $subject) {
            $match = self::matchOne($pattern, $subject);
            if ($match) {
                return $subject;
            }
        }

        return null;
    }

    public static function matchOne(string $pattern, string $subject) : bool
    {
        $subject = trim($subject);

        return preg_match($pattern, $subject) === 1;
    }
}
