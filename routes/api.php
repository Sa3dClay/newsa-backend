<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsApiController;

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

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::get('/user', 'user')->middleware('auth:api');
});

Route::controller(NewsApiController::class)->prefix('news')->middleware('auth:api')->group(function () {
    // news preferences
    Route::prefix('preferences')->group(function () {
        Route::get('/', 'getPreferences');
        Route::post('/save', 'savePreferences');
        Route::get('/options', 'getPreferencesOptions');
    });
    // news
    Route::get('/', 'getNews');
});
