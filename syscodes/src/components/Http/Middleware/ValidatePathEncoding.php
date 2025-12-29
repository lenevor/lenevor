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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Middleware;

use Closure;
use Syscodes\Components\Http\Exceptions\MalformedUrlException;
use Syscodes\Components\Http\Request;

/**
 * Check if the string is valid for the specified encoding.
 */
class ValidatePathEncoding
{
    /**
     * Validate that the incoming request has a valid UTF-8 encoded path.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $decodedPath = rawurldecode($request->path());

        if ( ! \mb_check_encoding($decodedPath, 'UTF-8')) {
            throw new MalformedUrlException;
        }

        return $next($request);
    }
}