<?php

use Syscodes\Components\Http\Request;
use Syscodes\Components\Support\Facades\Route;

/*
|------------------------------------------------------------------------- 
| Api Routes
|-------------------------------------------------------------------------
|
| Here is where the routes for your application are registered. These routes 
| are loaded from the Router class into a file called "api" from the "routes" 
| folder of the system's core. Do something great!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');