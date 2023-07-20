<section class="">
    <a id="details" class="scroll_detail"></a>
    <section class="section-detail-context">
        <div class="section-info-request">
            <div class="info-request-title">
                <h2><?= e(__('exception.request')) ?></h2>
            </div>
            <div class="info-url-method">
                <span class="url"><?= url('/') ?></span>
                <span class="method"><?= request()->method() ?></span>
            </div>
            <div class="info-header-title">
                <h2><?= e(__('exception.headers')) ?></h2>
                <div class="data-table-container">
                <?php foreach ($tables as $label => $data) : ?>
                    <div class="data-table">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <td>Key</td>
                                    <td>Value</td>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data as $key => $value) : ?>
                                <tr>
                                    <td><?= e($key) ?></td>
                                    <td><?= e(print_r($value, true)) ?></td>
                                </tr>
                            <?php endforeach; ?>	
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
	            </div>      
            </div>
        </div>
    </section>
</section>