<@extends('layouts::app')

<@section('title', config('app.name'))

<@section('content')

		<div class="container">
			
			<div class="content">

				<div class="info">
					<img src="{!! asset('svg/logo.svg') !!}" />
					<h1>Welcome to <b>{{ config('app.name') }}</b> </h1>
					<div class="location">
						<span><i class="icon-checkmark"></i>{{ basePath() }}</span>
					</div>
					<p class="message">
						Your have successfully installed this application, you can use it now.
					</p>					
					<div class="box">
						<div class="documents">
							<span><i class="icon-books"></i></span>
							<h2>Documents</h2>
							<span>
								<a href="#">Guides</a>
								<a href="#">api</a>
								<a href="#">references</a>
							</span>
						</div>
						<div class="tutorials">
							<span><i class="icon-embed"></i></span>
							<h2>Tutorials</h2>
							<span>
								<a href="#">Create your first page</a>
							</span>
						</div>
						<div class="community">
							<span><i class="icon-users"></i></span>
							<h2>Community</h2>
							<span>
								<a href="#">Blog</a>
								<a href="#">github</a>
							</span>
						</div>
					</div>
				</div>

			</div>
		  	
		</div>	

<@stop
