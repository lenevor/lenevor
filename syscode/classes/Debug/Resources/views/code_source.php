<?php foreach ($frames as $index => $frame) : ?>	
<div class="code-source <?= ($index == 0 ? 'active' : '') ?> clearfix" data-frame=<?= $index ?>>
    <div class="title">
        <h4><?= e(__('exception.line'))?> <?= $frame->getLine() ?> <?= e(__('exception.inFile'))?> <?= $template->cleanPath($frame->getFile(), $frame->getLine()) ?></h4>
        <div class="iconlist">            
            <div class="icon-holder icon-print" onclick="javascript:window.print()">
                <div class="tooltip tooltip-print">
                    Generate print
                </div>
                <i class="icofont-print"></i>
            </div>      
            <div class="icon-holder icon-pdf">
                <div class="tooltip tooltip-pdf">
                    Open the reader pdf
                </div>
                <i class="icofont-file-pdf"></i>
            </div>      
            <div class="icon-holder icon-edit">
                <div class="tooltip tooltip-edit">
                    Open the code editor
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