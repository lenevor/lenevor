<?php foreach ($frames as $index => $frame) : ?>	
<div class="code-source <?= ($index == 0 ? 'active' : '') ?> clearfix" data-frame=<?= $index ?>>
    <div class="title">
        <h4 title="<?= $template->cleanPath($frame->getFile(), $frame->getLine()) ?>"><?= $template->cleanPath($frame->getFile(), $frame->getLine()) ?></h4>
        <div class="iconlist">                
            <div class="icon-holder icon-edit">
                <div class="tooltip tooltip-edit">
                    <?= e(__('exception.openCodeEditor', ['editor' => null]))?>
                </div>
                <svg class="ico-file-edit" style="enable-background:new 0 0 128 128;" viewBox="0 0 128 128"xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <g>
                        <polygon points="84.1,67.165 84.1,105.854 18.536,105.854 18.536,22.854 84.1,22.854 84.1,28.111 96.1,16.111 96.1,10.854 6.536,10.854 6.536,117.854 96.1,117.854 96.1,111.854 96.1,55.165" />
                        <g>
                            <path d="M68.232,75.635l46.455-46.456l-12.256-12.255L55.976,63.381c-0.25,0.199-0.442,0.471-0.529,0.805    l-3.611,13.721c-0.008,0.025-0.008,0.055-0.013,0.08c-0.012,0.053-0.02,0.107-0.026,0.162c-0.004,0.051-0.006,0.098-0.006,0.146    c0,0.051,0.002,0.098,0.006,0.148c0.007,0.055,0.015,0.109,0.026,0.162c0.005,0.027,0.005,0.055,0.013,0.08    c0.004,0.02,0.014,0.035,0.02,0.055c0.016,0.051,0.035,0.102,0.058,0.152c0.019,0.045,0.037,0.088,0.06,0.129    c0.023,0.043,0.051,0.084,0.079,0.125c0.026,0.041,0.056,0.084,0.086,0.121c0.031,0.039,0.065,0.072,0.101,0.107    s0.068,0.068,0.107,0.1c0.037,0.031,0.078,0.061,0.12,0.086c0.041,0.029,0.081,0.057,0.126,0.08    c0.041,0.021,0.085,0.041,0.129,0.059c0.051,0.023,0.1,0.043,0.152,0.059c0.019,0.006,0.035,0.016,0.055,0.02    c0.026,0.006,0.054,0.006,0.08,0.014c0.053,0.01,0.107,0.018,0.162,0.023c0.051,0.006,0.098,0.008,0.147,0.008    c0.049,0,0.097-0.002,0.147-0.006c0.055-0.008,0.109-0.016,0.162-0.025c0.025-0.008,0.054-0.008,0.08-0.014l13.721-3.611    C67.76,76.08,68.033,75.887,68.232,75.635z M57.377,74.234c-0.353-0.354-0.835-0.492-1.295-0.428l2.099-7.977l7.602,7.602    l-7.977,2.098C57.871,75.07,57.732,74.588,57.377,74.234z"/>
                            <path d="M116.77,27.096l-12.254-12.255l4.693-4.694c0,0,8.348,2.978,12.256,12.256L116.77,27.096z"/>
                        </g>
                    </g>
                </svg>
            </div>          
        </div>
    </div>
    <div class="source">
        <code>
            <?= $template->highlightFile($frame->getFile(), $frame->getLine(), 11); ?>
        </code>
    </div>
    <?php $frameArgs = $template->dumpArgs($frame); ?>
    <?php if ($frameArgs): ?>
    <div class="frame-file">
        <?= e(__('exception.arguments')) ?>
    </div>
    <div id="frame-code-args-<?=$index?>" class="code-block frame-args">
        <?php echo $frameArgs; ?>
    </div>
    <?php endif ?>
</div>
<?php endforeach; ?>