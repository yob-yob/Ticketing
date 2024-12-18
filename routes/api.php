<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventReviewController;
use App\Http\Controllers\TicketReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:sanctum'], function () {
    
    Route::get('/event/index', [EventController::class, 'index'])->name('event.index');
    Route::post('/event/create', [EventController::class, 'create'])->name('event.create');

    Route::post('/event/{event}/reserve', TicketReservationController::class)->name('event.reserve');
    
    Route::get('/event/{event}/review/index', [EventReviewController::class, 'index'])->name('event.review.index');
    Route::post('/event/{event}/review', [EventReviewController::class, 'create'])->name('event.review.create');
});
