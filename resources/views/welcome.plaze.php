<@extends('layouts::app')

<@section('title', 'Welcome to '.config('app.name'))

<@section('content')
        <div class="wrapper">

			<div class="container">
				
				<div class="info">
					<div class="logo">
						<svg xmlns="http://www.w3.org/2000/svg" width="190" height="210" viewBox="35 50 130 80">
							<path style="opacity:1;fill:#ffffff;fill-opacity:0.97942349;stroke:#383838;stroke-width:0.59641558;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" d="m 52.580871,83.956791 45.114002,21.640019 -1e-5,49.57029 -45.105642,-22.80794 z" />
							<path style="opacity:1;fill:#ffffff;fill-opacity:0.97942349;stroke:#383838;stroke-width:0.59641558;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" d="M 97.920103,63.506489 52.992317,83.494045 97.799742,105.09026 111.99424,98.666359 81.824935,82.718586 96.464872,75.853105 127.38134,90.928197 142.09628,84.301121 Z" />
							<path d="m 142.50325,84.767449 -14.83261,6.675298 0.0774,20.013973 -15.56282,7.21083 0.18247,-19.526515 -14.284266,6.465235 0.01991,49.54303 43.909766,-23.35561 z" style="opacity:1;fill:#ffffff;fill-opacity:0.97942349;stroke:#383838;stroke-width:0.59641558;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
						</svg>
					</div>	
					<h1><small>{{ __('view.welcomeTo') }}</small><span>{{ config('app.name') }}</span></h1>
					<div class="status">
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

				</div>
				<svg xmlns="http://www.w3.org/2000/svg" class="wave" viewBox="0 0 1420 270">
					<path d="M0,128L13.3,138.7C26.7,149,53,171,80,149.3C106.7,128,133,64,160,64C186.7,64,213,128,240,149.3C266.7,171,293,149,320,165.3C346.7,181,373,235,400,245.3C426.7,256,453,224,480,224C506.7,224,533,256,560,234.7C586.7,213,613,139,640,138.7C666.7,139,693,213,720,224C746.7,235,773,181,800,186.7C826.7,192,853,256,880,261.3C906.7,267,933,213,960,192C986.7,171,1013,181,1040,181.3C1066.7,181,1093,171,1120,176C1146.7,181,1173,203,1200,197.3C1226.7,192,1253,160,1280,154.7C1306.7,149,1333,171,1360,181.3C1386.7,192,1413,192,1427,192L1440,192L1440,320L1426.7,320C1413.3,320,1387,320,1360,320C1333.3,320,1307,320,1280,320C1253.3,320,1227,320,1200,320C1173.3,320,1147,320,1120,320C1093.3,320,1067,320,1040,320C1013.3,320,987,320,960,320C933.3,320,907,320,880,320C853.3,320,827,320,800,320C773.3,320,747,320,720,320C693.3,320,667,320,640,320C613.3,320,587,320,560,320C533.3,320,507,320,480,320C453.3,320,427,320,400,320C373.3,320,347,320,320,320C293.3,320,267,320,240,320C213.3,320,187,320,160,320C133.3,320,107,320,80,320C53.3,320,27,320,13,320L0,320Z"></path>
				</svg>

			</div>	
			<div class="resources">

				<div class="row">
					<div class="resource">
						<h2><i class="icon-books"></i><a href="#">{{ __('view.documentation') }}</a></h2>
						<span>
							{{ __('view.especification-documentation') }}
						</span>
					</div>
					<div class="resource">
						<h2><i class="icon-embed"></i><a href="">{{ __('view.tutorials') }}</a></h2>
						<span>
							{{ __('view.video-tutorials') }}
						</span>
					</div>
					<div class="resource">
						<h2><i class="icon-users"></i><a href="">{{ __('view.community') }}</a></h2>
						<span>
							{{ __('view.community-diffusion') }}
						</span>
					</div>					
				</div>
				<div class="version">
					Build {{ version() }}
				</div>	

			</div>		
		
		</div>
<@stop
