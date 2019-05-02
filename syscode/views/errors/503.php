<!DOCTYPE html>
<html lang="en">
	<head>
	
		<meta charset="utf-8">
		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Service Unavailable</title>

		<!-- Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
		
		<!-- Styles -->
		<style type="text/css">
		   <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'style.css')) ?>
		</style>

	</head>
	<body>

		<div class="flex-center full-height">
		
			<div class="code">
				<h1>503</h1>
			</div>
			
			<div class="message" style="padding: 10px;">
				<?= __( ! empty($message) && $message != '(null)' ? $message : 'Service Unavailable') ?>
			</div>
			
		</div>

	</body>
</html>