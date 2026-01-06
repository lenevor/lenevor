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
		   <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(SYS_PATH.'/src/components/Core/Exceptions/views/css/minimal.css')) ?>
		</style>

	</head>
	<body>

		<div class="flex items-center justify-center w-screen h-screen bg-gradient">
					
			<div class="text-size-title">
				<h1 class="text-color-title uppercase"><@give('message')</h1>
			</div>
			
		</div>

	</body>
</html>