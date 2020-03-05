<@extends('layouts::app')

<@section('title', 'Welcome to '.config('app.name'))

<@section('content')
		<div class="wrapper">

			<div class="container">
				
				<div class="info">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="5 45 210 180" version="1.1" id="svg8">
						<path
							d="m 162.71464,100.77983 c -0.85254,-1.704724 -2.91732,-2.394178 -4.62957,-1.54539 L 42.801568,156.38212 c -0.227279,0.11267 -0.434998,0.24784 -0.625093,0.39939 a 24.285598,23.554605 60.224601 0 0 -0.758321,0.36032 24.285598,23.554605 60.224601 0 0 -9.189516,32.48988 24.285598,23.554605 60.224601 0 0 32.221229,10.27222 l 0.203726,-0.10992 a 24.285598,23.554605 60.224601 0 0 1.03392,-0.61299 L 179.78342,142.62203 c 1.71224,-0.84877 2.40428,-2.90425 1.55174,-4.60897 z M 60.728322,174.47086 a 8.7109659,8.54934 58.116343 0 1 -3.326679,11.71421 l -0.07394,0.04 a 8.7109659,8.54934 58.116343 0 1 -11.650013,-3.6381 8.7109659,8.54934 58.116343 0 1 3.383768,-11.69632 8.7109659,8.54934 58.116343 0 1 11.666835,3.58007 z"
							style="opacity:1;fill:#939dac;fill-opacity:1;stroke:#383838;stroke-width:0.32689831;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:0.74509804" />
						<path
							d="m 102.7558,54.381789 c -1.64224,-0.971366 -3.748978,-0.435709 -4.723379,1.201436 L 32.427698,165.80975 c -0.129339,0.21731 -0.230033,0.44295 -0.307035,0.67285 a 24.310026,23.482883 28.416279 0 0 -0.440018,0.71197 24.310026,23.482883 28.416279 0 0 9.964554,32.24544 24.310026,23.482883 28.416279 0 0 32.611203,-8.76784 l 0.111072,-0.2023 a 24.310026,23.482883 28.416279 0 0 0.533732,-1.07293 L 139.8301,80.30591 c 0.97439,-1.637146 0.43679,-3.736891 -1.20545,-4.708254 z M 57.301653,171.32284 a 8.7194087,8.5236199 27.224388 0 1 3.581471,11.63332 l -0.04027,0.0736 a 8.7194087,8.5236199 27.224388 0 1 -11.749678,3.23396 8.7194087,8.5236199 27.224388 0 1 -3.52386,-11.64911 8.7194087,8.5236199 27.224388 0 1 11.732237,-3.29175 z"
						style="opacity:1;fill:#b7bec8;fill-opacity:1;stroke:#383838;stroke-width:0.32689831;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:0.74509804" />
						<path
						style="opacity:1;fill:#dbdee3;fill-opacity:1;stroke:#383838;stroke-width:0.32689831;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:0.74509804"
						d="m 32.159618,46.457926 c -1.909563,0 -3.447203,1.532339 -3.447203,3.435966 V 178.06229 c 0,0.25268 0.0289,0.49796 0.08035,0.73484 a 24.328435,23.465114 0 0 0 -0.01399,0.83613 24.328435,23.465114 0 0 0 25.074913,22.66269 24.328435,23.465114 0 0 0 23.558136,-24.12927 l -0.008,-0.23048 a 24.328435,23.465114 0 0 0 -0.09018,-1.19424 V 49.893892 c 0,-1.903627 -1.53713,-3.435966 -3.446689,-3.435966 z M 52.926299,170.15063 a 8.7257627,8.5174131 0 0 1 9.034779,8.18296 l 0.003,0.0837 a 8.7257627,8.5174131 0 0 1 -8.449538,8.75812 8.7257627,8.5174131 0 0 1 -8.993319,-8.22585 8.7257627,8.5174131 0 0 1 8.404959,-8.79895 z"
						id="path4493"/>
					</svg>
					<h1>Welcome to <span>{{ config('app.name') }}</span> </h1>
					<div class="status">
						<code>
							<span class="check">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="-5 0 24 24">
									<path d="M0 0h24v24H0z" fill="none"></path>
									<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path>
								</svg>
							</span>
							<span class="location">{{ basePath() }}</span>
						</code>
						<p class="status-message">
							Your have successfully installed this application, you can use it now.
						</p>
					</div>					
				</div>
				<div class="resources">

					<div class="row">
						<div class="resource">
							<h2><i class="icon-books"></i>Documentation</h2>
							<span>
								<a href="#">Guides</a>
								<a href="#">api</a>
								<a href="#">references</a>
							</span>
						</div>
						<div class="resource">
							<h2><i class="icon-embed"></i>Tutorials</h2>
							<span>
								<a href="#">Create your first page</a>
							</span>
						</div>
						<div class="resource">
							<h2><i class="icon-users"></i>Community</h2>
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
