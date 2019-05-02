<header> 
<?php foreach ($class as $i => $name) : ?>
	<?php if ($i == count($class) - 1): ?>
	<h1><?= htmlspecialchars($name, ENT_SUBSTITUTE, 'UTF-8') ?></h1>
	<?php else: ?>
	<?= e($name).' \\' ?>
	<?php endif; ?>
<?php endforeach; ?>
	<h2><?= ucfirst(htmlspecialchars($message, ENT_SUBSTITUTE)) ?></h2>

</header>