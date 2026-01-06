<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
	
		<meta charset="UTF-8">
		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title><@give('title')</title>

		<!-- Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
		
		<!-- Styles -->
		<style type="text/css">
		   <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(SYS_PATH.'/src/components/Core/Exceptions/views/css/main.css')) ?>
		</style>

	</head>
	<body>

		<div class="container">
		
			<div class="flex items-center justify-center h-screen bg-gradient">

				<div class="flex items-center justify-center sm:flex-col md:flex-col sm:p-right md:p-right sm:p-full md:p-full">
					<h1 class="text-color-title font-bold sm:text-size-title md:text-size-title md:m-bottom text-gradient"><@give('code')</h1>
					<p class="text-color-subtitle font-bold uppercase letter-spacing sm:text-size-subtitle md:text-size-subtitle"><@give('message')</p>
					<a href="{{ url('/') }}" class="round p-full sm:m-top md:m-top md:p-full sm:text-size-button md:text-size-button"><@give('button')</a>
				</div>

			</div>
			
		</div>

		<script type="text/javascript">
			const background = document.querySelector('.bg-gradient');
			const title      = document.querySelector('.text-color-title');
			const subtitle   = document.querySelector('.text-color-subtitle');
			const button     = document.querySelector('a');

			/* Get the mode in localStorage */
			if (localStorage.getItem('dark-mode') === 'true') {
				background.classList.add('dark');
				title.classList.add('dark');
				subtitle.classList.add('dark');
				button.classList.add('dark');
			} else {
				background.classList.remove('dark');
				title.classList.remove('dark');
				subtitle.classList.remove('dark');
				button.classList.remove('dark');
			}
		</script>

	</body>
</html>