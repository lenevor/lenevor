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

namespace Syscodes\Components\core\Http\Middleware;

use Closure;
use Syscodes\Components\Http\Exceptions\PostTooLargeHttpException;

/**
 * Verify the server 'post_max_size'.
 */
class VerifyPostSize
{
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Http\Exceptions\PostTooLargeException
     */
    public function handle($request, Closure $next)
    {
        $max = $this->getPostMaxSize();
        
        if ($max > 0 && $request->server('CONTENT_LENGTH') > $max) {
            throw new PostTooLargeHttpException;
        }
        
        return $next($request);
    }
    
    /**
     * Determine the server 'post_max_size' as bytes.
     * 
     * @return int
     */
    protected function getPostMaxSize(): int
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }
        
        $metric      = strtoupper(substr($postMaxSize, -1));
        $postMaxSize = (int) $postMaxSize;
        
        switch ($metric) {
            case 'K':
            case 'k':
                return $postMaxSize * 1024;
            case 'M':
            case 'm':
                return $postMaxSize * 1048576;
            case 'G':
            case 'g':
                return $postMaxSize;
            default:
                return $postMaxSize;
        }
    }
}