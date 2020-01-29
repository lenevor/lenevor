<!DOCTYPE html>
<html lang="en">
	<head>
	
		<meta charset="UTF-8">
		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title><@give('title')</title>

		<!-- Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
		
		<!-- Styles -->
		<style type="text/css">
		   <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(SYS_PATH.'views/errors/css/style.css')) ?>
		</style>

	</head>
	<body>

		<div class="flex-center full-height">
		
			<div class="code">
				<h1><@give('code')</h1>
			</div>
			
			<div class="message" style="padding: 10px;">
				<@give('message')
			</div>
			
		</div>

	</body>
</html>