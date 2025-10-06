<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/generate-speech', App\Http\Controllers\SpeechController::class)->name('generate.speech');
