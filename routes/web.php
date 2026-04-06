<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Application\ApplicationController;
use App\Http\Controllers\Assessment\AssessmentController;
use App\Http\Controllers\Chainsaw\ChainsawController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Reports\PDFController;
use App\Http\Controllers\Dashboard\PENROController;
use App\Http\Controllers\Dashboard\TSDController;
use App\Http\Controllers\Dashboard\FUSController;
use App\Http\Controllers\Dashboard\ARDTSController;
use App\Http\Controllers\Dashboard\RegionalExecutiveController;

use App\Http\Controllers\Dashboard\RPSChiefDashboardController; // if you have a controller
use App\Http\Controllers\ProtectedArea\ResolutionClearanceController;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// Route::get('/generateApplicationNumber', [ApplicationController::class, 'generateApplicationNumber']);

// web.php
Route::middleware('auth')->get('/generateApplicationNumber',
    [ApplicationController::class, 'generateApplicationNumber']
);


Route::get('monitoring/index', function () {
    return Inertia::render('monitoring/index');
})->middleware(['auth', 'verified'])->name('monitoring.index');

Route::get('/monitoring/form/insert', function () {
    return Inertia::render('monitoring/form/insert');
})->middleware(['auth', 'verified'])->name('monitoring.form.insert');

// RESOLUTION AND CLEARANCES
Route::middleware(['auth','verified'])->group(function () {

    Route::get('/monitoring/index', [ResolutionClearanceController::class,'index'])->name('monitoring.index');

    Route::post('/monitoring/store', [ResolutionClearanceController::class,'store'])->name('monitoring.store');

    

    Route::put('/resolution/update/{id}', [ResolutionClearanceController::class,'update'])->name('resolution.update');

    Route::delete('/resolution/delete/{id}', [ResolutionClearanceController::class,'destroy'])->name('resolution.delete');

});



























Route::get('/signature/boss', function () {
    $filePath = 'private/signatures/red_signature.png';

    if (!Storage::exists($filePath)) {
        abort(404);
    }

    return Response::make(Storage::get($filePath), 200, [
        'Content-Type' => Storage::mimeType($filePath),
        'Content-Disposition' => 'inline; filename="boss_signature.png"',
    ]);
})->middleware('auth'); // optional, restrict access


Route::get('/chainsaw-permit/{id}/word', [PDFController::class, 'generatePermitDocx'])
    ->name('chainsaw.permit.word');

Route::get('/chainsaw-permit-multiple-brands-models/{id}/word', 
    [PDFController::class, 'generatePermitDocxMultipleBrandsModels'])
    ->name('chainsaw.permit.multiple.word');








Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');




Route::get('rps-chief-dashboard', function () {
    return Inertia::render('RPSChiefDashboard');
})->middleware(['auth', 'verified'])->name('rps.chief.dashboard');

Route::get('cenro-dashboard', function () {
    return Inertia::render('CENRODashboard');
})->middleware(['auth', 'verified'])->name('cenro.dashboard');

Route::get('penro-technical-dashboard', function () {
    return Inertia::render('PENROTechnicalDashboard');
})->middleware(['auth', 'verified'])->name('penro.technical.dashboard');

Route::get('penro-rps-chief-dashboard', function () {
    return Inertia::render('PENRORPSChiefDashboard');
})->middleware(['auth', 'verified'])->name('penro.rps.chief.dashboard');


Route::get('penro-tsd-chief-dashboard', function () {
    return Inertia::render('PENROTSDChiefDashboard');
})->middleware(['auth', 'verified'])->name('penro.tsd.chief.dashboard');

Route::get('penro-dashboard', function () {
    return Inertia::render('PENRODashboard');
})->middleware(['auth', 'verified'])->name('penro.dashboard');

Route::get('rts-dashboard', function () {
    return Inertia::render('RegionalTechnicalStaff');
})->middleware(['auth', 'verified'])->name('rts.dashboard');

Route::get('fus-dashboard', function () {
    return Inertia::render('FUSDashboard');
})->middleware(['auth', 'verified'])->name('fus.dashboard');

Route::get('lpdd-chief-dashboard', function () {
    return Inertia::render('LPDDChiefDashboard');
})->middleware(['auth', 'verified'])->name('lpdd.chief.dashboard');

Route::get('ardts-dashboard', function () {
    return Inertia::render('ARDTSDashboard');
})->middleware(['auth', 'verified'])->name('ardts.dashboard');

Route::get('regional-executive-dashboard', function () {
    return Inertia::render('RegionalExecutiveDashboard');
})->middleware(['auth', 'verified'])->name('regional.executive.dashboard');

// =============================================================






Route::get('tsd-chief-dashboard', function () {
    return Inertia::render('TSDChiefDashboard');
})->middleware(['auth', 'verified'])->name('tsd.chief.dashboard');





Route::get('rps-staff-dashboard', function () {
    return Inertia::render('RPSStaffDashboard');
})->middleware(['auth', 'verified'])->name('rps.staff.dashboard');




Route::get('applications/index', function () {
    return Inertia::render('applications/index');
})->middleware(['auth', 'verified'])->name('applications.index');

Route::get('applications/pending_application', function () {
    return Inertia::render('applications/pending_application');
})->middleware(['auth', 'verified'])->name('applications.pending_application');

Route::prefix('applications')->group(function () {
    Route::get('{id}/view', [ApplicationController::class, 'view'])
        ->name('applications.view');

    Route::get('{id}/edit', [ApplicationController::class, 'edit'])
        ->name('applications.edit');
});


Route::get('/application/index/{id}', function ($id) {
    return Inertia::render('application/index', [
        'id' => $id,
    ]);
})->middleware(['auth', 'verified'])->name('application.index');


Route::get('/applications/{id}/edit', [ApplicationController::class, 'edit'])
    ->name('applications.edit');
// ====================== UPDATE CHAINSAW INFORMATION  =====================================
Route::post('/applications/updateStatus', [ApplicationController::class, 'updateStatus'])
    ->name('applications.updateStatus');

Route::post('/applications/receivedApplication', [RPSChiefDashboardController::class, 'receivedApplication'])
    ->name('applications.receivedApplication');

Route::post('/applications/receivedByCENRO', [RPSChiefDashboardController::class, 'receivedByCENRO'])
    ->name('applications.cenro.receive');

Route::post('/applications/receivedByPENROTechnical', [PENROController::class, 'receivedByPENROTechnical'])
    ->name('applications.penro.technical.receive');

Route::post('/applications/receivedByPENRORPSChief', [PENROController::class, 'receivedByPENRORPSChief'])
    ->name('applications.penro.rps.chief.receive');


Route::post('/applications/receivedByPENROTSDChief', [AssessmentController::class, 'receive'])
    ->name('applications.penro.tsd.chief.receive');



    


Route::post('/applications/rps/return', [AssessmentController::class, 'return'])
    ->name('applications.rps.return');


Route::post('/applications/tsd/receive', [TSDController::class, 'receivedApplication'])
    ->name('applications.tsd.receive');

Route::post('/applications/tsd/endorse', [TSDController::class, 'endorseApplication'])
    ->name('applications.tsd.endorse');

Route::post('/applications/tsd/return', [TSDController::class, 'returnApplication'])
    ->name('applications.tsd.return');




Route::post('/applications/fus/return', [FUSController::class, 'returnApplication'])
    ->name('applications.fus.return');

Route::post('/applications/fus/endorse', [FUSController::class, 'endorseApplication'])
    ->name('applications.fus.endorse');

Route::post('/applications/ardts/endorse', [ARDTSController::class, 'endorseApplication'])
    ->name('applications.ardts.endorse');



Route::post('/applications/ardts/return', [ARDTSController::class, 'returnApplication'])
    ->name('applications.ardts.return');



Route::post('/applications/red/return', [RegionalExecutiveController::class, 'returnApplication'])
    ->name('applications.red.return');

    Route::post('/applications/technical/endorse', [ChainsawController::class, 'endorseApplication'])
    ->name('applications.technical.endorse');


// ====================== ENDORSED TO PENRO ================================================
Route::post('/applications/endorseApplication', [RPSChiefDashboardController::class, 'endorseApplication'])
    ->name('applications.rpschief.endorse');

Route::post('/applications/receive', [RPSChiefDashboardController::class, 'receivedApplication'])
    ->name('applications.rpschief.receive');



// ====================== RECEIVED BY PENRO =================================================
Route::post('/applications/penro/receive', [AssessmentController::class, 'receive'])
    ->name('applications.penro.receive');

Route::post('/applications/penro/return', [PENROController::class, 'returnApplication'])
    ->name('applications.penro.return');

// ======================== RECEIVED BY REGIONAL TECHNICAL STAFF ===============================
Route::post('/applications/rts/receive', [AssessmentController::class, 'receive'])
    ->name('applications.rts.receive');

// ========================= FUS =================================
Route::post('/applications/fus/receive', [AssessmentController::class, 'receive'])
    ->name('applications.fus.receive');


    // ======================== LPDD ================================
    Route::post('/applications/lpdd/receive', [AssessmentController::class, 'receive'])
    ->name('applications.lpdd.receive');


    Route::post('/applications/ardts/receive', [AssessmentController::class, 'receive'])
    ->name('applications.ardts.receive');


    Route::post('/applications/red/receive', [AssessmentController::class, 'receive'])
    ->name('applications.red.receive');
    
  


// ====================== ENDORSED TO LPDD_FUS ===================================================
Route::post('/applications/endorseToFUS', [PENROController::class, 'endorseToFUS'])
    ->name('applications.penro.endorse');
// ====================== ====================================================================
Route::put(
    '/applications/{id}/update-applicant-data',
    [ApplicationController::class, 'updateIndividualApplicant']
)->name('applications.update.individual.data');

Route::post(
    '/applications/{id}/update-company-data',
    [ApplicationController::class, 'updateCompanyApplicant']
)->name('applications.update.company.data');

Route::post(
    '/applications/{id}/update-company-payment-data',
    [ApplicationController::class, 'updateCompanyPayemnt']
)->name('applications.update.company.payment.data');



Route::put(
    '/applications/{id}/update-chainsaw-info',
    [ChainsawController::class, 'updateChainsawInformation']
)->name('applications.update.chainsaw.info');


Route::put(
    '/applications/{id}/update-payment-info',
    [PaymentController::class, 'updatePaymentInformation']
)
    ->name('applications.update.payment.info');

Route::put(
    '/applications/{id}/submit-application',
    [ApplicationController::class, 'submitApplication']
)
    ->name('applications.submit.application');


Route::post('/applications/return', [ApplicationController::class, 'returnApplication'])
    ->middleware(['auth', 'verified'])
    ->name('applications.return');

Route::get('/permit/{id}/preview', [PDFController::class, 'generateTable'])
    ->name('permit.preview');


// ===========================================================================================


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
