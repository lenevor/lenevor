<?php foreach ($frames as $index => $frame) : ?>
<div class="frame <?= ($index == 0 ? 'active' : '') ?> frame-application" data-index="<?= $index ?>">
	
	<div class="frame-index"><?= (count($frames) - $index - 1) + 1 ?></div>
		
	<div class="frame-class">
		<?php if ($frame->getClass() && class_exists($frame->getClass())) :?>	
		<div class="delimiter"><?= $frame->getClass() ?></div>
		<?php endif; ?>
	</div>
	<div class="frame-file">

		<div class="delimiter">
			<!-- Trace info -->
			<?php if ($frame->getFile() && is_file($frame->getFile())) :?>
				<?php
					if ($frame->getFunction() && in_array($frame->getFunction(), ['include', 'include_once', 'require', 'require_once']))
					{
						echo $frame->getFunction().' '. $template->cleanPath($frame->getFile());
					}												
					else
					{
						echo $template->cleanPath($frame->getFile());
					}
				?>
			<?php else : ?>
				[PHP internal code]
			<?php endif; ?>

			<?php if ( ! $frame->getClass() && $frame->getFunction()) : ?>
				&nbsp;&nbsp;&mdash;&nbsp;&nbsp;	<?= $frame->getFunction() ?>()
			<?php endif; ?>
		</div>
		<?php if ($frame->getLine() && is_numeric($frame->getLine())) :?>			
		<span class="frame-line">Line&nbsp;<?= $frame->getLine() ?></span>
		<?php endif; ?>	
	</div>
	
</div>		
<?php endforeach; ?>	