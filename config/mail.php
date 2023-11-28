<?php

return [
    
    /*
    |------------------------------------------------------------------------
    | Default Mailer
    |------------------------------------------------------------------------
    |
    | This option assigns the default mail to use the sent from email message 
    | of your application.
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),
    
    /*
    |------------------------------------------------------------------------
    | Mailer Configurations
    |------------------------------------------------------------------------
    |
    | This option allows you to configure all the mailers used by your 
    | application and with their respective configurations. Several examples 
    | have been included for you and you may add your own as your application 
    | requires.
    |
    | Lenevor supports a variety of mail "transport" drivers that are used 
    | when sending an email. You are free to add additional mailers as 
    | required.
    |
    | Supported: "smtp", "sendmail", "mailgun", "log", "array"
    |
    */

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'mailgun' => [
            'transport' => 'mailgun',
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        
        'array' => [
            'transport' => 'array',
        ],
    ],
    
];