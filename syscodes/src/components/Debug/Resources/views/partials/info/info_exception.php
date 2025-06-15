<section class="exception"> 
	<div class="exception-title">
		<div class="exception-background-title">
		<?php foreach ($class as $i => $name) : ?>
			<?php if ($i == count($class) - 1): ?>		
			<span><?= $template->escape($name) ?></span> 
			<?php else: ?>
			<span><?= $template->escape($name).'&nbsp;\\&nbsp;' ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
		</div>
		<?php if ($code): ?>
		<span class="subtitle" title="Exception Code"><?= $template->escape($code) ?></span>
		<?php endif ?>
	</div>
	<div class="exception-message">
		<h2><?= ucfirst($template->escape($message)) ?></h2>
	</div>
	<span class="plain-exception"><pre><?= $template->escape($plain_exception) ?></pre></span>
</section>