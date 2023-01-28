<@extends('layouts.app')

<@section('title', __('view.welcomeTo').config('app.name'))

<@section('content')
	<div class="content">
		<div class="container">
			<div class="status">
				<h2 class="text-gradient">{{ __('view.welcomeTo') }} <span>{{ config('app.name') }}</span></h2>
				<code>
					<span class="check">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
							<path d="M0 0h24v24H0z" fill="none"></path>
							<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path>
						</svg>
					</span>
					<span>{{ basePath() }}</span>
				</code>
				<p class="status-message">
					{{ __('view.successfullyInstalled') }}
				</p>
			</div>
			<div class="resources">
				<div class="learn">
					<h3><i class="icon-books"></i><a href="https://github.com/lenevor/docs" target="_self">{{ __('view.learn-title') }}</a></h3>
					<p>{!! __('view.userGuide') !!}</p>
				</div>
				<div class="discuss">
					<h3><i class="icon-embed"></i>{{ __('view.discuss-title') }}</h3>
					<p>{!! __('view.discuss') !!}</p>
				</div>
				<div class="contribute">
					<h3><i class="icon-users"></i>{{ __('view.contribute-title') }}</h3>
					<p>{!! __('view.contribute') !!}</p>
				</div>
			</div>
			<div class="footer">
				<div class="btnTheme">
					<i class="fas fa-sun"></i>
					<i class="indicator"></i>
					<i class="fas fa-moon"></i>
				</div>
				<p>Lenevor {{ version() }} (PHP {{ PHP_VERSION }})</p>
			</div>
		</div>
	</div>
<@stop
