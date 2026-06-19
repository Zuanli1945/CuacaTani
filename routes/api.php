<?php

use App\Http\Controllers\Api\WeatherController;
use Illuminate\Support\Facades\Route;

Route::post('/cuaca', [WeatherController::class, 'forecast']);
