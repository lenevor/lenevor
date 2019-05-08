<?php foreach ($frames as $index => $frame) : ?>	
<div class="code-source <?= ($index == 0 ? 'active' : '') ?> clearfix" data-frame=<?= $index ?>>
    <div class="title">
        <h4><?= $template->cleanPath($frame->getFile(), $frame->getLine()) ?></h4>
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
            <div class="icon-holder icon-edit">
                <div class="tooltip tooltip-edit">
                    <?= e(__('exception.openCodeEditor', ['editor' => null]))?>
                </div>
                <i class="icofont-edit"></i>
            </div>          
        </div>
    </div>
    <div class="source">
        <?= $template->highlightFile($frame->getFile(), $frame->getLine(), 11); ?>
    </div>
    <?php $frameArgs = $template->dumpArgs($frame); ?>
    <?php if ($frameArgs): ?>
    <div id="frame-code-args-<?=$index?>" class="code-block frame-args">
        <div class="frame-file">
            <?= e(__('exception.arguments')) ?>
        </div>
        <?php echo $frameArgs; ?>
    </div>
    <?php endif ?>
</div>
<?php endforeach; ?>