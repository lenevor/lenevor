<div class="frame-description frame-description-application">

	<span href="#"><?= e(__('exception.stackFrames')) ?> (<?=count($frames)?>)</span>

	<div class="iconlist">
		<div class="icon-holder icon-print" onclick="javascript:window.print()">
			<div class="tooltip tooltip-print">
				<?= e(__('exception.print'))?>
			</div>
			<i class="icofont-print"></i>
		</div>      
		<div class="icon-holder icon-pdf">
			<div class="tooltip tooltip-pdf">
				<?= e(__('exception.openReaderPDF'))?>
			</div>
			<i class="icofont-file-pdf"></i>
		</div>  
	</div>

</div>