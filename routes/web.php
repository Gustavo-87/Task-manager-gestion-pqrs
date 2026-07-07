<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PqrController;

Route::get('/', function () {
    return redirect()->route('pqrs.index');
});

Route::resource('pqrs', PqrController::class);