<?php

use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();

    // Which admin dashboards the sidebar may link to.
    return [
        ...$user->toArray(),
        'can_view_telescope' => config('telescope.enabled') && Gate::forUser($user)
            ->allows('viewTelescope'),
        'can_view_pulse' => Gate::forUser($user)
            ->allows('viewPulse'),
    ];
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard-stats', [DashboardController::class, 'getStats']);

    Route::resources([
        'users' => UserController::class,
        'invoices' => InvoiceController::class,
        'payments' => PaymentController::class,
        'credit-notes' => CreditNoteController::class,
        'deductions' => DeductionController::class
    ]);

    Route::post('/invoices/{id}/send-email', [InvoiceController::class, 'sendInvoiceEmail']);
    Route::post('/payments/{id}/send-email', [PaymentController::class, 'sendReceiptEmail']);

    Route::patch('/settings/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/settings/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
    Route::put('/settings/password', [SecurityController::class, 'update'])
        ->name('password.update');
});

Route::get('/invoices/{id}/preview', [InvoiceController::class, 'previewPdf']);
Route::get('/payments/{id}/preview', [PaymentController::class, 'previewPdf']);
