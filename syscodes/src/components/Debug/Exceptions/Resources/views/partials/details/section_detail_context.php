<section class="">
    <section class="section-detail-request">
        <div class="section-info-request">
            <div class="info-request-title">
                <a id="detail-request" class="scroll-target"></a>
                <h2><?= e(__('exception.request')) ?></h2>
            </div>
            <div class="info-url-method">
                <span class="url"><?= url()->current() ?></span>
                <span class="method"><?= request()->method() ?></span>
            </div>
            <div class="info-header-title"> 
            <?php foreach ($servers as $data) : ?>
                <a id="detail-request-header" class="scroll-target"></a>
                <h2><?= e(__('exception.headers')) ?></h2>
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
            <div class="info-routing-title">
            <?php foreach ($routes as $data) : ?>
                <a id="detail-app-routing" class="scroll-target"></a>
                <h2><?= e(__('exception.routing')) ?></h2>
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
            <?php endforeach; ?>  
            </div>
        </div>
    </section>
    <section class="section-detail-context">
        <div class="section-info-context">
            <div class="info-context-title">
                <a id="detail-context-data-version" class="scroll-target"></a>
                <h2><?= e(__('exception.contextData')) ?></h2>
            </div>
            <div class="info-version-title">
            <?php foreach ($contexts as $data) : ?>
                <a id="detail-context-version" class="scroll-target"></a>
                <h2><?= e(__('exception.versions')) ?></h2>
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
                            <td><span><?= e(print_r($value, true)) ?></span></td>
                        </tr>
                        <?php endforeach; ?>	
                    </tbody>
                </table>
            <?php endforeach; ?>  
            </div>
        </div>
    </section>
</section>