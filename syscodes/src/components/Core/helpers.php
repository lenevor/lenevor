<?php 

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

use Syscodes\Components\Core\Application;
use Syscodes\Components\Support\WebString;
use Syscodes\Components\Support\Facades\Date;
use Syscodes\Components\Contracts\View\Factory;
use Syscodes\Components\Routing\Supported\UrlGenerator;
use Syscodes\Components\Contracts\Routing\RouteResponse;
use Syscodes\Bundles\WebResourceBundle\Autoloader\Autoload;
use Syscodes\Bundles\WebResourceBundle\Autoloader\Autoloader;
use Syscodes\Components\Contracts\Auth\Factory as AuthFactory;
use Syscodes\Components\Contracts\Cookie\Factory as CookieFactory;

if ( ! function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  array  $headers
     * 
     * @return void
     *
     * @throws \Syscodes\Components\Core\Http\Exceptions\HttpException
     * @throws \Syscodes\Components\Core\Http\Exceptions\LenevorException
     */
    function abort($code, $message = '', array $headers = [])
    {
        return app()->abort($code, $message, $headers);
    }
}

if ( ! function_exists('app')) {
    /**
     * Get the available Application instance.
     *
     * @param  string  $id  
     * @param  array  $parameters
     * 
     * @return mixed|\Syscodes\Components\Contracts\Core\Application
     */
    function app($id = null, array $parameters = [])
    {
        if (is_null($id)) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($id, $parameters);
    }
}

if ( ! function_exists('autoloader')) {
    /**
     * Get Autoloader class instance with initialized to autoload.
     * 
     * @return \Syscodes\Bundles\WebResourceBundle\Autoloader\Autoloader
     */
    function autoloader()
    {
        return Autoloader::instance()->initialize(new Autoload());
    }
}

if ( ! function_exists('appPath')) {
    /**
     * Get the path to the application folder.
     * 
     * @param  string  $path
     * 
     * @return string
     */
    function appPath($path = '')
    {
        return app()->path($path);
    }
}

if ( ! function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     * 
     * @param  string  $path
     * @param  bool  $secure  
     * 
     * @return string
     */
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}

if ( ! function_exists('auth')) {
    /**
     * Get the available auth instance.
     * 
     * @param  string|null  $guard
     * 
     * @return \Syscodes\Components\Contracts\Auth\Factory|\Syscodes\Components\Contracts\Auth\Guard|\Syscodes\Components\Contracts\Auth\StateGuard
     */
    function auth($guard = null)
    {
        if (is_null($guard)) {
            return app(AuthFactory::class);
        }

        return app(AuthFactory::class)->guard($guard);
    }
}

if ( ! function_exists('back')) {
    /**
     * Create a new redirect response to the previous location.
     * 
     * @param  int  $status    
     * @param  array  $headers
     * @param  mixed  $fallback  
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    function back($status = 302, $headers = [], $fallback = false)
    {
        return app('redirect')->back($status, $headers, $fallback);
    }
}

if ( ! function_exists('basePath')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * 
     * @return string
     */
    function basePath($path = '')
    {
        return app()->basePath($path);
    }
}

if ( ! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  dynamic  key|key,default|data,expiration|null
     * 
     * @return mixed|\Syscodes\Components\Cache\CacheManager
     *
     * @throws \Exception
     */
    function cache()
    {
        $arguments = func_get_args();
        
        if (empty($arguments)) {
            return app('cache');
        }
        
        if (is_string($arguments[0])) {
            return app('cache')->get(...$arguments);
        }
        
        if ( ! is_array($arguments[0])) {
            throw new Exception('When setting a value in the cache, you must pass an array of key / value pairs.');
        }
        
        if ( ! isset($arguments[1])) {
            throw new Exception('You must specify an expiration time when setting a value in the cache.');
        }
        
        return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
    }
}

if ( ! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     * If an array is passed as the key, we will assume you want to set 
     * an array of values.
     *
     * @param   array|string  $key  
     * @param   mixed  $default  
     *
     * @return  mixed|\Syscodes\Components\Config\Configure
     */
    function config($key = null, $value = null)
    {
        if ($key === null) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key, $value);
        }
        
        return app('config')->get($key, $value);
    }
}

if ( ! function_exists('configPath')) {
    /**
     * Get the path to the configuration folder.
     *
     * @param  string  $path
     * 
     * @return string
     */
    function configPath($path = '')
    {
        return app()->configPath($path);
    }
}

if ( ! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @param  string|null  $name
     * @param  string|string[]|null  $value
     * @param  int  $minutes
     * @param  string|null  $path
     * @param  string|null  $domain
     * @param  bool|null  $secure
     * @param  bool  $httpOnly
     * @param  bool  $raw
     * @param  string|null  $sameSite
     * 
     * @return \Syscodes\Components\Cookie\CookieManager|\Syscodes\Components\Http\Cookie
     */
    function cookie(
        string $name = null, 
        $value = null, 
        int $minutes = 0, 
        string $path = null, 
        string $domain = null, 
        bool $secure = null, 
        bool $httpOnly = true, 
        bool $raw = false, 
        string $sameSite = null
    ) {
        $cookie = app(CookieFactory::class);
        
        if (is_null($name)) {
            return $cookie;
        }
        
        return $cookie->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
}

if ( ! function_exists('csrfField')) {
    /**
     * Generate a CSRF token form field.
     * 
     * @return string
     */
    function csrfField()
    {
        return new WebString('<input type="hidden" name="_token" value="'.csrfToken().'">');
    }
}

if ( ! function_exists('csrfToken')) {
    /**
     * Get the CSRF token value.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    function csrfToken()
    {
        $session = app('session');
        
        if (isset($session)) {
            return $session->token();
        }

        throw new RuntimeException('Application session store not set.');
    }
}

if ( ! function_exists('databasePath')) {
    /**
     * Get the path to the database directory.
     * 
     * @param  string  $path
     * 
     * @return string
     */
    function databasePath($path = '')
    {
        return app()->databasePath($path);
    }
}

if ( ! function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     * 
     * @param  mixed  $value
     * @param  bool  $unserialize  
     * 
     * @return string
     */
    function decrypt($value, $unserialize = true)
    {
        return app('encrypter')->decrypt($value, $unserialize);
    }
}

if ( ! function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param  string  $value
     *
     * @return string
     */
    function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if ( ! function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     * 
     * @param  mixed  $value
     * @param  bool  $serialize  
     * 
     * @return string
     */
    function encrypt($value, $serialize = true)
    {
        return app('encrypter')->encrypt($value, $serialize);
    }
}

if ( ! function_exists('event')) {
    /**
     * Dispatch an event and call the listeners.
     * 
     * @param  string|array  $args
     * 
     * @return array|null
     */
    function event($args) 
    {
        return app('events')->dispatch($args);
    }
}

if ( ! function_exists('getClass')) {
    /**
     * Function to crop the full name of the namespace and leave 
     * only the name of the class.
     * 
     * @param  string|object  $classname
     * @param  bool  $bool  
     * 
     * @return array|string
     */
    function getClass($classname, $bool = false)
    {
        $position = explode('\\', get_class($classname));
        
        return ! $bool ? array_pop($position) : get_class($classname);
    }
}

if ( ! function_exists('info')) {
    /**
     * Write some information to the log.
     * 
     * @param  string  $message
     * @param  array  $context
     * 
     * @return void
     */
    function info($message, $context = [])
    {
        app('log')->info($message, $context);
    }
}

if ( ! function_exists('isCli')) {
    /**
     * Determines if this request was made from the command line (CLI).
     * 
     * @return bool
     */
    function isCli()
    {
        return (\PHP_SAPI === 'cli' || defined('STDIN') || \PHP_SAPI === 'phpdbg');
    }
}


if ( ! function_exists('isGetCommonPath')) {
    /**
     * Find the common "root" path of two given paths or FQFN's.
     * 
     * @param  array  $paths  Array with the paths to compare
     * 
     * @return string  The determined common path section
     */
    function isGetCommonPath($paths)
    {
        $lastOffset = 1;
        $common     = '/';
        
        while (($index = strpos($paths[0], '/', $lastOffset)) !== false) {
            $dirLen = $index - $lastOffset + 1; // include
            $dir = substr($paths[0], $lastOffset, $dirLen);
            
            foreach ($paths as $path) {
                if (substr($path, $lastOffset, $dirLen) != $dir) {
                    return $common;
                }
            }
            
            $common    .= $dir;
            $lastOffset = $index + 1;
        }
        
        return $common;
    }
}

if ( ! function_exists('isImport')) {
    /**
     * Loads in a core class and optionally an app class override if it exists.
     * 
     * @param  string  $path
     * @param  string  $folder
     * 
     * @return void
     */
    function isImport($path, $folder = 'classes')
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        
        // load it ffrom the core if it exists
        if (is_file(SYS_PATH.$folder.DIRECTORY_SEPARATOR.$path.'.php')) {
            require_once SYS_PATH.$folder.DIRECTORY_SEPARATOR.$path.'.php';
        }
        
        // if the app has an override (or a non-core file), load that too
        if (is_file(APP_PATH.$folder.DIRECTORY_SEPARATOR.$path.'.php')) {
            require_once APP_PATH.$folder.DIRECTORY_SEPARATOR.$path.'.php';
        }

        require_once __DIR__.DIRECTORY_SEPARATOR.$folder.'/'.$path.'.php';
    }
}

if ( ! function_exists('langPath')) {
    /**
     * Get the path to the language folder.
     *
     * @param  string  $path
     * 
     * @return string
     */
    function langPath($path = '')
    {
        return app('path.lang').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if ( ! function_exists('methodField'))
{
    /**
     * Generate a form field to spoof the HTTP verb used by forms.
     * 
     * @param  string  $method
     * 
     * @return \Syscodes\Components\Support\WebString
     */
    function methodField($method)
    {
        return new WebString('<input type="hidden" name="_method" value="'.$method.'">');
    }
}

if ( ! function_exists('now')) {
    /**
     * Create a new Chronos class instance for the current time.
     * 
     * @param  \DateTimeZone|string|null  $timezone
     * 
     * @return \Syscodes\Components\Support\Chronos
     */
    function now($timezone = null)
    {
        return Date::now($timezone);
    }
}

if ( ! function_exists('redirect')) {
    /**
     * Get an instance of the redirect.
     *
     * @param  string|null  $url  The url  
     * @param  int   $code  The redirect status code  
     * @param  array  $headers  An array of headers
     * @param  bool|null  $secure  Type of protocol (http|https)  
     *
     * @return \Syscodes\Components\Routing\Supported\Redirector
     */
    function redirect($url = null, $code = 302, $headers = [], $secure = null)
    {
        if (null === $url) {
            return app('redirect');
        }
        
        return app('redirect')->to($url, $code, $headers, $secure);
    }
}

if ( ! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     * 
     * @param  array|string|null  $key  
     * @param  mixed  $default  
     * 
     * @return \Syscodes\Components\Http\Request|string|array 
     */
    function request($key = null, $default = null)
    {
        if (null === $key) {
            return app('request');
        }

        $value = app('request')->__get($key);

        return null === $value ? value($default) : $value;
    }
}

if ( ! function_exists('response')) {
    /**
     * Return a new Response from the application.
     *
     * @param  string  $body
     * @param  int  $status  
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\Response|\Syscodes\Components\Routing\Supported\RouteResponse
     */
    function response($body = '', $status = 200, array $headers = [])
    {
        $response = app(RouteResponse::class);

        if (func_num_args() === 0) {
            return $response;
        }

        return $response->make($body, $status, $headers);
    }
}

if ( ! function_exists('resourcePath')) {
    /**
     * Get the path to the resources folder.
     *
     * @param  string  $path
     * 
     * @return string
     */
    function resourcePath($path = '')
    {
        return app()->resourcePath($path);
    }
}

if ( ! function_exists('route')) {
    /**
     * Get the URL to a named route.
     * 
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $forced  
     * @param  \Syscodes\Components\Routing\Route|null  $route  
     * 
     * @return string
     */
    function route($name, $parameters = [], $forced = true, $route = null)
    {
        return app('url')->route($name, $parameters, $forced, $route);
    }
}

if ( ! function_exists('secureAsset')) {
    /**
     * Generate an asset path for the application.
     * 
     * @param  string  $path
     * 
     * @return string
     */
    function secureAsset($path)
    {
        return asset($path, true);
    }
}

if ( ! function_exists('secureUrl')) {
    /**
     * Generate a HTTPS URL for the application.
     * 
     * @param  string  $path
     * @param  array  $parameters
     * 
     * @return string
     */
    function secureUrl($path, $parameters = [])
    {
        return url($path, $parameters, true);
    }
}

if ( ! function_exists('session')) {
    /**
     * Get / set the specified session value.
     * 
     * @param  string  $key  
     * @param  mixed  $default  
     * 
     * @return mixed|\Syscodes\Components\Session\Store|\Syscodes\Components\Session\SessionManager
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }

        if (is_array($key)) {
            return app('session')->put($key, $default);
        }

        return app('session')->get($key, $default);
    }
}

if ( ! function_exists('segment')) {
  /**
     * Returns the desired segment, or $default if it does not exist.
     *
     * @param  int  $segment  
     * @param  mixed  $default  
     *
     * @return string
     */
    function segment($index, $default = null)
    {
        return request()->segment($index, $default);
    }
}

if ( ! function_exists('segments')) {
  /**
     * Returns all segments in an array.
     *
     * @return array
     */
    function segments()
    {
        return request()->segments();
    }
}

if ( ! function_exists('storagePath')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * 
     * @return string
     */
    function storagePath($path = '')
    {
        return app('path.storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if ( ! function_exists('totalSegments')) {
  /**
     * Returns the total number of segment.
     *
     * @return int
     */
    function totalSegments()
    {
        return request()->totalSegments();
    }
}

if ( ! function_exists('__')) {
    /**
     * A convenience method to translate a string and format it
     * with the intl extension's MessageFormatter object.
     * 
     * @param  string  $line
     * @param  array  $replace
     * 
     * @return string
     */
    function __($line, array $replace = [])
    {
        return app('translator')->get($line, $replace);
    }
}

if ( ! function_exists('url')) {
    /**
     * Generate a URL for the application.
     *
     * @param  string|null  $path  
     * @param  array  $parameters
     * @param  bool|null  $secure  
     *
     * @return \Syscodes\Components\Routing\Supported\UrlGenerator
     */
    function url($path = null, $parameters = [], $secure = null)
    {
        if (is_null($path)) {
            return app(UrlGenerator::class);
        }

        return app(UrlGenerator::class)->to($path, $parameters, $secure);
    }
}

if ( ! function_exists('view')) {
    /**
     * Returns a new View object. If you do not define the "file" parameter, 
     * you must call [$this->view].
     *
     * @example $view->make($file, $data);
     *  
     * @param  string|null  $file  View filename
     * @param  array  $data  Array of values
     * 
     * @return \Syscodes\Components\View\View|\Syscodes\Components\Contracts\View\Factory
     */
    function view($file = null, $data = [])
    {
        $view = app(Factory::class);

        if (func_num_args() === 0) {
            return $view;
        }

        return $view->make($file, $data);
    }
}