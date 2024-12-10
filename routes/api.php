<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/event/create', [EventController::class, 'create']);
    Route::post('/event/{event}/reserve', TicketReservationController::class);
});
