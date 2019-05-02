<?php 

namespace App\Http\Controllers;

use Syscode\Controller\Controller;

class Welcome extends Controller
{
	public function index()
	{
		return view('welcome');
	}
}