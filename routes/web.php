<?php

use Syscode\Routing\Router;

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

Router::get('/', function () {
	return view('welcome');
});

Router::get('/home', 'welcome:index');