<?php

use App\Http\Controllers\InvoiceController;
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
]);