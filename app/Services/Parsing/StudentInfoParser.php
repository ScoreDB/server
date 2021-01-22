<?php

namespace App\Services\Parsing;

use League\Csv\Reader;

class StudentInfoParser
{
    public static function parse(string $file, ?string $grade = null)
    {
        $reader = Reader::createFromString($file);
        $reader->setHeaderOffset(0);
        $reader->skipEmptyRecords();

        $result = [];
        foreach ($reader->getRecords() as $student) {
            $student['id']      = strtoupper($student['id']);
            $student['classId'] = strtoupper($student['classId']);
            if (isset($grade)) {
                $student['gradeId'] = strtoupper($grade);
            }

            // Process student's name to generate search index.
            $name_delimiter = ' ';
            $name_words     = explode($name_delimiter, $student['name']);
            if (count($name_words) === 1) {
                $name_delimiter = '';
                $name_words     = mb_str_split($student['name']);
            }
            $student['name_index'] = [];
            for ($length = 1; $length <= count($name_words); $length++) {
                for (
                    $start = 0; $start <= count($name_words) - $length; $start++
                ) {
                    $slice     = array_slice($name_words, $start, $length);
                    $name_part = implode($name_delimiter, $slice);
                    array_push($student['name_index'], $name_part);
                }
            }

            // Process student's name's pinyin to generate search index.
            $pinyin_full_all   = explode(' / ', $student['pinyin']);
            $student['pinyin'] = [];
            foreach ($pinyin_full_all as $pinyin_full) {
                $pinyin_parts = explode(' ', $pinyin_full);
                for ($length = 1; $length <= count($pinyin_parts); $length++) {
                    for (
                        $start = 0; $start <= count($pinyin_parts) - $length;
                        $start++
                    ) {
                        $slice       = array_slice($pinyin_parts, $start,
                            $length);
                        $pinyin_part = implode(' ', $slice);
                        array_push($student['pinyin'], $pinyin_part);
                        if ($length > 1) {
                            $slice_words_first = array_map(function ($word) {
                                return mb_substr($word, 0, 1);
                            }, $slice);
                            $pinyin_part_first = implode('',
                                $slice_words_first);
                            array_push($student['pinyin'], $pinyin_part_first);
                        }
                    }
                }
            }

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
