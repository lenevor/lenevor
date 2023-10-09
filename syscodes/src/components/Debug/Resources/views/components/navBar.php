<div class="sidebar">
    <nav>
        <ul>
            <li>
                <h2>
                    <a href="#detail-request"><?= e(__('exception.request')) ?></a>
                </h2>
            </li>
            <li>
                <a href="#detail-request-header">
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
                    <?= e(__('exception.headers')) ?>
                </a>
            </li>
            <li>
                <a href="#detail-request-body">                    
                    <svg viewBox="0 0 330 220" xmlns="http://www.w3.org/2000/svg">
                        <path d="M69.12158,94.14551,28.49658,128l40.625,33.85449a7.99987,7.99987,0,1,1-10.24316,12.291l-48-40a7.99963,7.99963,0,0,1,0-12.291l48-40a7.99987,7.99987,0,1,1,10.24316,12.291Zm176,27.709-48-40a7.99987,7.99987,0,1,0-10.24316,12.291L227.50342,128l-40.625,33.85449a7.99987,7.99987,0,1,0,10.24316,12.291l48-40a7.99963,7.99963,0,0,0,0-12.291Zm-82.38769-89.373a8.005,8.005,0,0,0-10.25244,4.78418l-64,176a8.00034,8.00034,0,1,0,15.0371,5.46875l64-176A8.0008,8.0008,0,0,0,162.73389,32.48145Z"/>
                    </svg>                 
                    <?= e(__('exception.body')) ?>
                </a>
            </li>
            <li>
                <h2>
                    <a href="#detail-app"><?= e(__('exception.app')) ?></a>
                </h2>
            </li>
            <li>
                <a href="#detail-app-routing">
                    <svg viewBox="0 -2 20 20" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12.5" cy="12.5" r="1.75"/>
                        <circle cx="3.5" cy="12.5" r="1.75"/>
                        <circle cx="3.5" cy="3.5" r="1.75"/>
                        <path d="m9.25 1.75-1.5 2 1.5 2m3 4.5v-5c0-1-.5-1.5-1.5-1.5h-2m-5 2v4.5"/>
                    </svg>
                    <?= e(__('exception.routing')) ?>
                </a>
            </li>
            <!-- <li>
                <a href="#detail-app-database">
                    <svg viewBox="1 -1 28 28" xmlns="http://www.w3.org/2000/svg">
                        <g id="b454ce3c-30f4-4856-be86-7f37e0bf5d4b" data-name="database">
                            <path d="M12,1C7,1,3,2.34,3,4V6c0,1.66,4,3,9,3s9-1.34,9-3V4C21,2.34,17,1,12,1Z"/>
                            <path d="M12,11C7,11,3,9.66,3,8v5c0,1.66,4,3,9,3s9-1.34,9-3V8C21,9.66,17,11,12,11Z"/>
                            <path d="M12,18c-5,0-9-1.34-9-3v5c0,1.66,4,3,9,3s9-1.34,9-3V15C21,16.66,17,18,12,18Z"/>
                        </g>
                    </svg>      
                    <?php // e(__('exception.database')) ?>
                </a>
            </li> -->
            <li>
                <h2>
                    <a href="#detail-context-data"><?= e(__('exception.contextData')) ?></a>
                </h2>
            </li>
            <li>
                <a href="#detail-context-version">
                    <svg viewBox="8 0 24 24" xmlns="http://www.w3.org/2000/svg"> 
                        <path d="M12 14C10.8954 14 10 13.1046 10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12C14 13.1046 13.1046 14 12 14Z" stroke-width="1"/>
                        <path d="M14 6C12.8954 6 12 5.10457 12 4C12 2.89543 12.8954 2 14 2C15.1046 2 16 2.89543 16 4C16 5.10457 15.1046 6 14 6Z" stroke-width="1"/>
                        <path d="M10 22C11.1046 22 12 21.1046 12 20C12 18.8954 11.1046 18 10 18C8.89543 18 8 18.8954 8 20C8 21.1046 8.89543 22 10 22Z" stroke-width="1"/>
                        <path d="M17.5 20L19 20M12 20L14.75 20" stroke-width="1" stroke-linecap="round"/>
                        <path d="M6.5 4L5 4M12 4L9.25 4" stroke-width="1" stroke-linecap="round"/>
                        <path d="M19 12H14" stroke-width="1" stroke-linecap="round"/>
                        <path d="M19 4L16 4" stroke-width="1" stroke-linecap="round"/>
                        <path d="M5 20L7.66667 20" stroke-width="1" stroke-linecap="round"/>
                        <path d="M10 12L7.5 12M5.5 12L5 12" stroke-width="1" stroke-linecap="round"/>
                    </svg>
                    <?= e(__('exception.versions')) ?>
                </a>
            </li>
        </ul>
    </nav>
</div>