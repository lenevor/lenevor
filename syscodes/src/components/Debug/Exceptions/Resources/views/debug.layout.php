<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="robots" content="noindex">		
		<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1">
		<title><?= e($handler->getPageTitle()) ?></title>	
		<link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i" rel="stylesheet">		
		<!-- Styles -->
		<style type="text/css">
			<?= $stylesheet ?>
		</style>
	</head>
	<body>
		<div class="container">		
			<?= $template->render($header) ?>
			<?= $template->render($section_stack_exception) ?>
		</div>		
		<!-- Script -->
		<script type="text/javascript">
			<?= $javascript ?>
		</script>
	</body>
</html>