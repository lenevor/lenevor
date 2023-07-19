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
                
            </div>
        </div>
    </section>
</section>