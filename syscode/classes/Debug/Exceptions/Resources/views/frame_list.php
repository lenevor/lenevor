<?php foreach ($frames as $index => $frame) : ?>
<div class="frame frame-application <?= ($index == 0) ? 'active' : '' ?>" data-index="<?= $index ?>">
	
	<div class="frame-index"><?= (count($frames) - $index - 1) + 1 ?></div>
		
	<div class="frame-method-info">
		<span class="frame-class"><?= e($frame->getClass()) ?: '' ?></span>
		<span class="frame-function"><?= e($frame->getFunction()) ?: '' ?></span>
	</div>
	<div class="frame-file">

		<div class="delimiter">
			<?= $frame->getFile() ? $template->cleanPath($frame->getFile()) : '<#unknown>'?>
		</div>		
		<span class="frame-line">Line&nbsp;<?= (int) $frame->getLine() ?></span>

	</div>
	
</div>		
<?php endforeach; ?>	