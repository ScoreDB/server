<?php

use App\Http\Controllers\StudentDB\ClassesController;
use App\Http\Controllers\StudentDB\GradesController;
use App\Http\Controllers\StudentDB\StudentsController;
use App\Http\Controllers\User\TokensController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('can:user:tokens')->group(function () {
        Route::apiResource('/user/tokens', TokensController::class)->only([
            'index', 'store', 'destroy',
        ]);
    });

    Route::middleware('can:studentdb:read')->group(function () {
        Route::apiResource('/studentdb/grades', GradesController::class)->only([
            'index', 'show',
        ]);

        Route::apiResource('/studentdb/classes', ClassesController::class)
            ->only([
                'show',
            ]);

        Route::apiResource('/studentdb/students', StudentsController::class)
            ->only([
                'show',
            ]);

        Route::middleware('throttle:search')->group(function () {
            Route::get('/studentdb/search',
                [StudentsController::class, 'search']);
        });
    });
});

Route::get('/user/challenge/{challenge}', function ($challenge) {
    $key  = "login_challenge_{$challenge}";
    $user = cache($key);
    cache()->delete($key);
    if (empty($user)) {
        throw new NotFoundHttpException();
    }

    return $user;
});
