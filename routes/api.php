<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProtectedArea\ProtectedAreaList;
use App\Http\Controllers\ProtectedArea\DashboardController;
use App\Http\Controllers\ProtectedArea\LawinPatrolController;
use App\Http\Controllers\DATS\DocumentsController;
use App\Http\Controllers\Activity\ActivityController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('api')->group(function () {
Route::get('/hired_staff/{pa_id}/pa_staff', [ProtectedAreaList::class, 'getPAStaffPerPA']);



Route::get('/summary', [DashboardController::class, 'summary']);
Route::get('/monthly-monitoring', [DashboardController::class, 'monthlyMonitoring']);
Route::get('/monthly-accomplishment', [DashboardController::class, 'monthlySubmissionMonitoring']);
Route::get('/googlesheet', [DocumentsController::class, 'index']);
Route::get('/googlesheet/export', [DocumentsController::class, 'exportToSheet']);//working
Route::get('/googlesheet/sync-rdats', [DocumentsController::class, 'syncFromRdats']);
Route::get('/googlesheet/syncdatabase', [DocumentsController::class, 'syncFromRdats']);
Route::get('/googlesheet/preview', [DocumentsController::class, 'previewFromRdats']);

Route::post('/googlesheet', [DocumentsController::class, 'sendToSheet']);
Route::get('/lawin/data', [LawinPatrolController::class, 'getLawinData']);
Route::get('/iec_cepa', [DashboardController::class, 'iec_cepa']);
Route::get('/activities', [ActivityController::class, 'index']);
Route::post('/activities', [ActivityController::class, 'store']);



});
