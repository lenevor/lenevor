<?php 

return [

    /*
    |------------------------------------------------------------------------
    | Application Name                                                        
    |------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when you
    | must specify the name of the application in a notification or use it 
    | from another part of the application or packages.
    |
    */
    
    'name' => env('APP_NAME', 'Lenevor'),

    /*
    |------------------------------------------------------------------------
    | Base Site URL
    |------------------------------------------------------------------------
    |
    | baseUrl - The base URL of an application. MUST contain a trailing 
    | slash (/).
    |
    | You can set this to a full or relative URL:
    |
    |     'baseUrl' => 'http://localhost'
    |
    |        or 
    |
    |     'baseUrl' => 'https://bar.com'
    |
    | Set this to null to have it automatically detected.
    |
    */
	
    'baseUrl' => env('APP_BASE_URL', 'http://localhost'),

    /*
    |------------------------------------------------------------------------
    | Index File                                                  
    |------------------------------------------------------------------------
    |
    | Typically this will be your index.php file. If you are using 
    | mod_rewrite to remove the page set this variable so that it is blank.
    |
    | Example:  
    |         'indexPage' => 'index.php';
    |
    */

    'indexPage' => '',
    
    /*
    |------------------------------------------------------------------------
    | Application Environment                                                       
    |------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various 
    | services your application utilizes. Are as follows:
    |
    | 1. local (development)
    | 2. testing
    | 3. production
    |
    */
    
    'env' => env('APP_ENV', 'production'),

    /*
    |------------------------------------------------------------------------
    | Application Debug Mode                                                       
    |------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with 
    | stack traces will be shown on every error that occurs within your 
    | application. If it is disabled, it will show a simple generic error 
    | page.
    |
    */
    
    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |-------------------------------------------------------------------------
    | Admin E-mail address
    |-------------------------------------------------------------------------
    |
    | The Administrator's E-mail Address.
    | 
    | Example:
    |         'email' => 'admin@lenevor.com'
    */
    
    'email' => null,
    
    /*
    |-------------------------------------------------------------------------
    | Errors Identificated
    |-------------------------------------------------------------------------
    |
    | Actived the throttle, notices and show non-fatal error for knowing what types 
    | of errors happens.
    |
    */
    
    'errors' => [

        //Which errors should we show, but continue execution? You can add the following:
        // E_NOTICE, E_WARNING, E_DEPRECATED, E_STRICT to mimic PHP's default behaviour
        // (which is to continue on non-fatal errors). We consider this bad practice.
        'continueOn'  => [],
        // How many errors should we show before we stop showing them? (prevents out-of-memory errors)
        'throttle' => 10,
        // Should notices from Error::notice() be shown?
        'notices' => true

    ],

    /*
    |-----------------------------------------------------------------------
    | Application Locale Configuration                                                     
    |-----------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value to 
    | any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |-----------------------------------------------------------------------
    | Application Fallback Locale                                                    
    |-----------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of the 
    | language folders that are provided through your application.
    |
    */

    'fallbackLocale' => 'en',

    /*
	|-----------------------------------------------------------------------
	| Application Supported Locales
	|-----------------------------------------------------------------------
	|
	| If 'supportedLocale' is true, this array lists the locales supported
	| by the application in descending order of priority. If no match is
	| found, the first locale will be used.
	|
    */
    
	'supportedLocales' => ['en', 'es', 'pr'],

    /*
    |-----------------------------------------------------------------------    
    | Application TimeZone
    |-----------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which 
    | will be used by the PHP. 
    |
    | Example: 'timezone' => 'America/Bogota'
    |
    */

    'timezone' => 'UTC',

    /*
    |------------------------------------------------------------------------
    | Default String Encoding Set                                                    
    |------------------------------------------------------------------------
    |
    | This determines which encoding set is used by default in various methods
    | that require a character set to be provided.
    |
    */

    'encoding' => 'UTF-8',
            
];