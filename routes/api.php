<?php

use Azuriom\Plugin\ServerMonitoring\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [ApiController::class, 'index'])->name('index');
Route::get('/status', [ApiController::class, 'getServerStatus'])->name('status');
Route::post('/start', [ApiController::class, 'startServer'])->name('start');
Route::post('/stop', [ApiController::class, 'stopServer'])->name('stop');
Route::post('/restart', [ApiController::class, 'restartServer'])->name('restart');
Route::get('/logs', [ApiController::class, 'getServerLogs'])->name('logs');
