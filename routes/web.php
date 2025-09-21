<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('verify.account')->get('/test-account/{accountId}', [TestController::class, 'testAccount']);
