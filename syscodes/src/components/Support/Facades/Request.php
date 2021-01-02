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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscodes\Support\Facades;

/**
 * Initialize the Request class facade.
 *
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 * 
 * @method static \Syscodes\Http\Request active(\Syscodes\Http\Request|bool $request = false)
 * @method static string segment(int $index, mixed $default = null)
 * @method static array segments()
 * @method static int totalSegments()
 * @method static void detectLocale()
 * @method static string getDefaultLocale()
 * @method static string getLocale()
 * @method static \Syscodes\Http\Request setLocale(string $locale)
 * @method static string getUri()
 * @method static mixed getJSON(bool $assoc = false, int $depth = 512, int $options = 0)
 * @method static bool isXmlHttpRequest()
 * @method static string method(bool $upper = true)
 * @method static $this setMethod(string $method)
 * @method static string getBaseUrl()
 * @method static string getPathInfo()
 * @method static string path()
 * @method static string getScheme()
 * @method static void getHost()
 * @method static int getPort()
 * @method static string getHttpHost()
 * @method static string getSchemeWithHttpHost()
 * @method static string root()
 * @method static string referer(string $default = '')
 * @method static bool secure()
 * @method static userAgent(string $default = null)
 * 
 * @see \Syscodes\Http\Request
 */
class Request extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}