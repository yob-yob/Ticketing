<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegistrationController::class);
Route::post('/login', AuthenticationController::class);


