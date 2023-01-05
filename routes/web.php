<?php

use Syscodes\Components\Support\Facades\Route;

/*
|------------------------------------------------------------------------- 
| Web Routes
|-------------------------------------------------------------------------
|
| Here is where the routes for your application are registered. These routes 
| are loaded from the Router class into a file called "web" from the "routes" 
| folder of the system's core. Do something great!
|
*/

Route::get('/', fn () => view('welcome'));