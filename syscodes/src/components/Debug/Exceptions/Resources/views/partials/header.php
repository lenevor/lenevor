<section class="exception"> 
	<div class="exception-title">
		<?php foreach ($class as $i => $name) : ?>
			<?php if ($i == count($class) - 1): ?>
		<h1><?= $template->escape($name) ?></h1> 
			<?php else: ?>
		<h1><?= $template->escape($name).'&nbsp;\\' ?></h1>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ($code): ?>
		<span class="subtitle" title="Exception Code"><?= $template->escape($code) ?></span>
		<?php endif ?>
	</div>
	<div class="exception-message">
		<h2><?= ucfirst($template->escape($message)) ?></h2>
	</div>
	<span class="plain-exception"><pre><?= $template->escape($plain_exception) ?></pre></span>
</section>