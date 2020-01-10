<div class="frame-container-scroll scrollbar">
<?php foreach ($frames as $index => $frame) : ?>
	<div class="frame frame-application <?= ($index == 0) ? 'active' : '' ?>" data-index="<?= $index ?>">
		
		<div class="frame-main-index">
			<div class="frame-index"><?= (count($frames) - $index - 1) + 1 ?></div>
		</div>
			
		<div class="frame-method-info">
			<span class="frame-file"><?= $frame->getFile() ? $frame->getFile() : '<#unknown>'?></span>
			
			<div class="frame-info-class">
				<span class="frame-class"><?= e($frame->getClass()) ?: '' ?></span>
				<span class="frame-function"><?= e($frame->getFunction()) ?: '' ?></span>
			</div>
		</div>

		<div class="frame-line-number">
			<span class="frame-line"><?= (int) $frame->getLine() ?></span>
		</div>		
		
	</div>		
<?php endforeach; ?>	
</div>