<?php

use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\TypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::apiResource('transaction', TransactionController::class);
Route::apiResource('item', ItemController::class);
Route::apiResource('type', TypeController::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
