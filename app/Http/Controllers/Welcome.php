<?php 

namespace App\Http\Controllers;

use App\Http\BaseController;

class Welcome extends BaseController
{
	public function index()
	{
		return view('welcome');
	}
}