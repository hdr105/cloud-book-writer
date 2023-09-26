<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BookController;

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
    Route::middleware(['auth:api'])->group(function () {

        // Use Route::apiResource for CRUD operations if needed
        Route::post('/add', [BookController::class, 'store']);
        Route::get('/view', [BookController::class, 'show']); // Changed to GET for viewing
        Route::put('/edit/{id}', [BookController::class, 'update']); // Use PUT for updates
        Route::delete('/delete/{id}', [BookController::class, 'destroy']); // Use DELETE for deletions
    });
    
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/sendresetotp', [AuthController::class, 'sendResetOtp']);
    Route::post('/verifyotp', [AuthController::class, 'verifyOtp']);
    Route::post('/passwordreset', [AuthController::class, 'reset']);
    Route::post('/deleteaccount', [AuthController::class, 'accountDelete']);
});

