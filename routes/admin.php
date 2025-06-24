<?php

use Azuriom\Plugin\ServerMonitoring\Controllers\Admin\AdminController;
use Azuriom\Plugin\ServerMonitoring\Controllers\ServerMonitoringHomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your plugin. These
| routes are loaded by the RouteServiceProvider of your plugin within
| a group which contains the "web" middleware group and your plugin name
| as prefix. Now create something great!
|
*/

// Admin settings routes
Route::get('/', [AdminController::class, 'index'])->name('index');
Route::post('/settings', [AdminController::class, 'update'])->name('settings.update');
Route::post('/test-connection', [AdminController::class, 'testConnection'])->name('test-connection');

// Server monitoring routes (moved from web.php)
Route::get('/monitor', [ServerMonitoringHomeController::class, 'index'])->name('monitor');
Route::get('/logs/{server}', [ServerMonitoringHomeController::class, 'logs'])->name('logs');
Route::get('/debug', [ServerMonitoringHomeController::class, 'debug'])->name('debug');
