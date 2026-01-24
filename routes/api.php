<?php

use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    // return
    return $request->user();
});

Route::resources([
    'users' => UserController::class,
    'invoices' => InvoiceController::class,
    'payments' => PaymentController::class,
    'credit-notes' => CreditNoteController::class,
    'deductions' => DeductionController::class
]);
