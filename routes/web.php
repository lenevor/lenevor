<?php

/*
|------------------------------------------------------------------------- 
| Web Routes
|-------------------------------------------------------------------------
|
| Here is where the routes for your application are registered. These routes 
| are loaded from the Router class into a file called "web" from the "routes" 
| folder of the system's raiza. Do something great!
|
*/

Route::get('/', function () {
	return view('welcome');
});

Route::get('/home', 'welcome@index');