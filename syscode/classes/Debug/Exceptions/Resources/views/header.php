<header> 

	<div class="exception-title">
		<?php foreach ($class as $i => $name) : ?>
			<?php if ($i == count($class) - 1): ?>
		<h1><?= htmlspecialchars($name, ENT_SUBSTITUTE, 'UTF-8') ?></h1> 
			<?php else: ?>
		<?= $template->escape($name).' \\' ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ($code): ?>
		<span class="subtitle" title="Exception Code">(<?= $template->escape($code) ?>)</span>
		<?php endif ?>
	</div>
	<div class="exception-message">
		<h2><?= ucfirst(htmlspecialchars($message, ENT_SUBSTITUTE)) ?></h2>
	</div>
	<span class="plain-exception"><pre><?= $template->escape($plain_exception) ?></pre></span>

</header>