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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Http\Exceptions;

use Syscodes\Core\Http\Exceptions\LenevorException;

/**
 * HttpURIException.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class HttpURIException extends LenevorException 
{
    /**
     * Show a message of unable to parse URI.
     * 
     * @param  string  $uri
     * 
     * @return \Syscodes\Http\Exceptions\HttpURIException
     */
    public static function UnableToParseURI(string $uri)
    {
        return new static(__('http.cannotParseURI', [$uri]));
    }

    /**
     * Show a message of invalid port.
     * 
     * @param  int  $port
     * 
     * @return \Syscodes\Http\Exceptions\HttpURIException
     */
    public static function InvalidPort(int $port)
    {
        return new static(__('http.invalidPort', [$port]));
    }
}