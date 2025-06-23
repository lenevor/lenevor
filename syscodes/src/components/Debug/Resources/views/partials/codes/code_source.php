<?php foreach ($frames as $index => $frame) : ?>	
<div class="code-source clearfix <?= ($index == 0) ? 'active' : '' ?>" data-frame=<?= $index ?>>           
    <?php if ($frame->getFile() && $editorHref = $handler->getEditorAtHref($frame->getFile(), (int) $frame->getLine())): ?>
        <div class="title">       
            <a href="<?= $editorHref ?>"><?= $frame->getFile() ?><span> : <?= $frame->getLine() ?></span></a>
        </div>        
    <?php endif; ?>    
    <div class="source">
        <code>
            <?= $template->highlightFile($frame->getFile(), $frame->getLine(), 32); ?>
        </code>
    </div>
</div>
<?php endforeach; ?>