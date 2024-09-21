<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/update', [AuthController::class, 'updateUser']);
            Route::post('/changePassword', [AuthController::class, 'changePassword']);
            Route::get('/showUserInfo', [AuthController::class, 'showUserInfo']);
            Route::get('/getUser/{id}', [AuthController::class, 'getUser']);
            Route::get('/getAllUsers', [AuthController::class, 'getAllUsers']);
        });
    });
});
