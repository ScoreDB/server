<?php

namespace App\Http\Controllers\StudentDB;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Parsing\StudentInfoMatcher as Matcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $form = $request->validate([
            'query'     => 'required|string|max:32',
            'page_size' => 'nullable|integer|max:100',
        ]);

        $rawQuery = trim($form['query']);
        $pageSize = intval($form['page_size'] ?? 9);

        $childQueries = explode(' ', $rawQuery);

        // Match id.
        // If match, return result directly.
        $idMatch = Matcher::matchFirst(Matcher::PATTERN_ID, $childQueries);
        if (isset($idMatch)) {
            $idMatch  = strtoupper($idMatch);
            $paginate = Student::where('id', $idMatch)
                ->orWhere('eduid', $idMatch)
                ->paginate($pageSize, ['id', 'name', 'gender']);
        } else {
            $filteredQueries = [];
            $filters         = [];

            // Match classId filter.
            $classMatch = Matcher::matchFirst(Matcher::PATTERN_CLASS_ID,
                $childQueries);

            // Match gradeId filter.
            $gradeMatch = Matcher::matchFirst(Matcher::PATTERN_GRADE_ID,
                $childQueries);

            if (isset($classMatch)) {
                array_push($filteredQueries, $classMatch);
                $filters['classId'] = mb_strtoupper($classMatch);
            }

            if (isset($gradeMatch)) {
                array_push($filteredQueries, $gradeMatch);
                if (empty($classMatch)) {
                    $filters['gradeId'] = mb_strtoupper($gradeMatch);
                }
            }

            $processedQuery = implode(' ',
                array_diff($childQueries, $filteredQueries));

            $dbQuery = Student::query();
            if (count($filters) > 0) {
                $dbQuery->where($filters);
            }
            if (mb_strlen($processedQuery) > 0) {
                $dbQuery->where(function (Builder $query) use ($processedQuery
                ) {
                    $query->whereRaw('? = ANY(name_index) OR ? = ANY(pinyin)',
                        [$processedQuery, $processedQuery]);
                });
            }

            $paginate = $dbQuery->paginate($pageSize, ['id', 'name', 'gender']);
        }

        return [
            'data'         => $paginate->items(),
            'current_page' => $paginate->currentPage(),
            'pages'        => $paginate->lastPage(),
        ];
    }
}
