<?php 

namespace App\Http\Controllers;

use App\Http\Controller;

class Welcome extends Controller
{
	public function index()
	{
		return view('welcome');
	}
}