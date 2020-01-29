<@extends('layouts::app')

<@section('title', config('app.name'))

<@section('content')

		<div class="content">

			<div id="js-menu">
				<div class="menu"></div>
			</div> 
			
			<div class="container">

				<div class="info">
					<h1>Welcome to <?= config('app.name') ?></h1>
					<img src="<?= asset('svg/logo.svg') ?>">
					<p class="message">
						You have successfully installed your Lenevor application. 
					</p>
					<p class="message">
						If you want to know more about Lenevor and exploring many of its features, I recommend you start by reading the <a href="#">User Guide</a>.
					</p>

					<@include('partials::footer')
					
				</div>
			</div>
		  
		</div>	

<@stop
