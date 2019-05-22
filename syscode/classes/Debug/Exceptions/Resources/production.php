<!DOCTYPE html>
<html lang="en">
	<head>

		<meta charset="UTF-8">

		<meta name="robots" content="noindex">

		<title>Whoops!</title>

		<style type="text/css">
			<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'style.css')) ?>
		</style>

	</head>
	<body>

		<div class="flex-center flex-column full-height">
			<h1 class="headline">Whoops!</h1>
			<p class="lead">There seems to be a problem viewing this page. Please try again later...</p>
		</div>
		
	</body>
</html>