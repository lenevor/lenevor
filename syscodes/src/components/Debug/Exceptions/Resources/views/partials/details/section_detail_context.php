<section class="">
    <a id="details" class="scroll_detail"></a>
    <section class="section-detail-request">
        <div class="section-info-request">
            <div class="info-request-title">
                <a id="detail-request" class="scroll-target"></a>
                <h2><?= e(__('exception.request')) ?></h2>
            </div>
            <div class="info-url-method">
                <span class="url"><?= url('/') ?></span>
                <span class="method"><?= request()->method() ?></span>
            </div>
            <div class="info-header-title"> 
            <?php foreach ($tables as $data) : ?>
                <a id="detail-request-header" class="scroll-target"></a>
                <h2><?= e(__('exception.headers')) ?></h2>
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
            <div class="info-body-title">
                <a id="detail-request-body" class="scroll-target"></a>
                <h2><?= e(__('exception.body')) ?></h2>
                <pre>
                    <span>[]</span>
                </pre>
            </div>
        </div>
    </section>
    <section class="section-detail-app">
        <div class="section-info-app">
            <div class="info-app-title">
                <a id="detail-app" class="scroll-target"></a>
                <h2><?= e(__('exception.app')) ?></h2>
            </div>
        </div>
    </section>
</section>