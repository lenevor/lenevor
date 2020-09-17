<!DOCTYPE html>
<html lang="en">
	<head>
	
		<meta charset="utf-8">
		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1">
		
		<title><@give('title')</title>
		
		<!-- Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
		<link href="https://fonts.googleapis.com/css?family=Heebo:100,200,300,400,600,700,800,900" rel="stylesheet">
		
		<!-- Styles -->
		<style type="text/css">
			<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(resourcePath('css/app.css'))) ?>
		</style>

	</head>
	<body>

		<@give('content')

	</body>
</html>