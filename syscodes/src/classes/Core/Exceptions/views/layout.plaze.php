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
		   <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(basePath('syscodes/src/classes/Core/Exceptions/views/css/main.css'))) ?>
		</style>

	</head>
	<body>

		<div class="container">
		
			<div class="flex items-center justify-center w-screen h-screen bg-gradient animated">

				<div class="flex flex-row items-center justify-center sm:flex-col md:flex-row">
					<h1 class="bg-color border-solid border-color border-width border-shadow text-color-title text-size-title text-shadow font-bold m-right p-full round sm:text-size-title md:text-size-title"><@give('code')</h1>
					<p class="text-color-subtitle text-size-subtitle text-shadow font-bold uppercase letter-spacing sm:text-size-subtitle md:text-size-subtitle sm:m-top md:m-top md:m-left"><@give('message')</p>
				</div>

			</div>
			
		</div>

	</body>
</html>