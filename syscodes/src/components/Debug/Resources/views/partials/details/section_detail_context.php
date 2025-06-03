<section class="">
    <section class="section-detail-info">
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
                <div class="group-title">
                    <h2><?= e(__('exception.headers')) ?></h2>
                    <svg viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                        <g id="Group_132" data-name="Group 132" transform="translate(-380.703 -318.103)">
                            <path id="Path_478" data-name="Path 478" d="M415.106,361.119a1.989,1.989,0,0,0-1.99,1.99v6.7l8.689-8.688Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.592"/>
                            <path id="Path_479" data-name="Path 479" d="M415.106,361.119h6.7v-39.73a1.99,1.99,0,0,0-1.99-1.99H383.99a1.99,1.99,0,0,0-1.991,1.99v46.428a1.99,1.99,0,0,0,1.991,1.99h29.126v-6.7A1.989,1.989,0,0,1,415.106,361.119Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.592"/>
                            <line id="Line_80" data-name="Line 80" x2="17.213" transform="translate(389.184 329.898)" stroke-miterlimit="10" stroke-width="2.592"/>
                            <line id="Line_81" data-name="Line 81" x2="17.213" transform="translate(389.184 337.063)" stroke-miterlimit="10" stroke-width="2.592"/>
                            <g id="Group_130" data-name="Group 130">
                                <line id="Line_82" data-name="Line 82" x2="17.213" transform="translate(389.184 344.231)" stroke-miterlimit="10" stroke-width="2.592"/>
                            </g>
                            <path id="Path_480" data-name="Path 480" d="M403.535,325.115" fill="#fff" stroke-miterlimit="10" stroke-width="2.592"/>
                            <path id="Path_481" data-name="Path 481" d="M403.535,325.115" fill="#fff" stroke-miterlimit="10" stroke-width="2.592"/>
                            <path id="Path_482" data-name="Path 482" d="M414.62,327.652,411.272,331l-1.7-1.7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.555"/>
                            <path id="Path_483" data-name="Path 483" d="M414.62,335l-3.348,3.349-1.7-1.7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.555"/>
                            <path id="Path_484" data-name="Path 484" d="M414.62,342.345l-3.348,3.349-1.7-1.7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.555"/>
                            <g id="Group_131" data-name="Group 131">
                            <line id="Line_83" data-name="Line 83" x2="17.213" transform="translate(389.184 351.282)" stroke-miterlimit="10" stroke-width="2.592"/>
                            </g>
                            <path id="Path_485" data-name="Path 485" d="M414.62,349.4l-3.348,3.349-1.7-1.7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.555"/>
                        </g>
                    </svg>
                </div>
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
                            <td><p><?= e($key) ?></p></td>
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
        <div class="section-info-context">
            <div class="info-context-title">
                <a id="detail-context-data" class="scroll-target"></a>
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