<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExpenseRateController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\VehicleController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request): RedirectResponse {
    return to_route($request->user() ? 'dashboard' : 'login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('invitation', [InvitationController::class, 'store'])->middleware('throttle:6,1')->name('invitation.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('can:manage catalogs')->group(function () {
        Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('business-units', BusinessUnitController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('projects', ProjectController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('vehicles', VehicleController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('expense-types', ExpenseTypeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('expense-rates', ExpenseRateController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('bank-accounts', BankAccountController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});

require __DIR__.'/settings.php';
