<section class="exception"> 
	<div class="exception-title">
		<div class="exception-background-title">
		<?php foreach ($class as $i => $name) : ?>
			<?php if ($i == count($class) - 1): ?>		
			<h2><?= $template->escape($name) ?></h2> 
			<?php else: ?>
			<h2><?= $template->escape($name).'&nbsp;\\&nbsp;' ?></h2>
			<?php endif; ?>
		<?php endforeach; ?>
		</div>
		<?php if ($code): ?>
		<span class="subtitle" title="Exception Code"><?= $template->escape($code) ?></span>
		<?php endif ?>
	</div>
	<div class="exception-message">
		<h3><?= ucfirst($template->escape($message)) ?></h3>
	</div>
	<span class="plain-exception"><pre><?= $template->escape($plain_exception) ?></pre></span>
</section>