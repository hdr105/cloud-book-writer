<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BookController;
use App\Http\Controllers\api\v1\SectionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    //---------------------------------------------------------------------------------------------------------//
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::prefix('forgot')->group(function () {
            Route::post('/send-reset-otp', [AuthController::class, 'send_reset_otp']);
            Route::post('/verify-otp', [AuthController::class, 'verify_otp']);
            Route::post('/reset', [AuthController::class, 'reset']);
        });
    });
    //---------------------------------------------------------------------------------------------------------//
    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('books')->group(function () {
            Route::post('/add', [BookController::class, 'store']);
            Route::get('/view', [BookController::class, 'show']);
            Route::put('/edit/{id}', [BookController::class, 'update']);
            Route::delete('/delete/{id}', [BookController::class, 'destroy']);
        });
    //---------------------------------------------------------------------------------------------------------//
        Route::prefix('section')->middleware(['auth:api'])->group(function () {
            Route::post('/add', [SectionController::class, 'section_store']);
            Route::post('/update', [SectionController::class, 'section_update']);
        });
    //---------------------------------------------------------------------------------------------------------//
        Route::prefix('subsection')->middleware(['auth:api'])->group(function () {
            Route::post('/add', [SectionController::class, 'sub_section_store']);
        });
    //---------------------------------------------------------------------------------------------------------//
    });
});

