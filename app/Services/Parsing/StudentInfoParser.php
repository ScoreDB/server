<?php

namespace App\Services\Parsing;

use League\Csv\Reader;
use Overtrue\Pinyin\Pinyin;

class StudentInfoParser
{
    public static function parse(string $file, ?string $grade = null)
    {
        $reader = Reader::createFromString($file);
        $reader->setHeaderOffset(0);
        $reader->skipEmptyRecords();

        $pinyin_generator = new Pinyin();

        $result = [];
        foreach ($reader->getRecords() as $student) {
            $student['id']      = strtoupper($student['id']);
            $student['classId'] = strtoupper($student['classId']);
            if (isset($grade)) {
                $student['gradeId'] = strtoupper($grade);
            }

            $pinyin = $pinyin_generator->name($student['name']);
            if (count($pinyin) > 0) {
                $pinyin_full  = implode(' ', $pinyin);
                $pinyin_first = implode('', array_map(function ($word) {
                    return $word[0];
                }, $pinyin));
                array_unshift($pinyin, $pinyin_first);
                array_push($pinyin, $pinyin_full);
            }
            $student['pinyin'] = $pinyin;

            if (empty($student['birthday'])) {
                $student['birthday'] = null;
            }
            if (empty($student['eduid'])) {
                $student['eduid'] = null;
            }

            array_push($result, $student);
        }

        return $result;
    }
}
