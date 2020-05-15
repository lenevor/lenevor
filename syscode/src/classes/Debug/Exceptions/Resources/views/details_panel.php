<section class="panel details-panel">
	<?= $template->render($code_source) ?>
	<div class="details-description details-description-application">

		<span><?= e(__('exception.environment'))?></span>

	</div>

	<div class="content">
		
		<?= $template->render($details_content) ?>
		
	</div> 

</section>