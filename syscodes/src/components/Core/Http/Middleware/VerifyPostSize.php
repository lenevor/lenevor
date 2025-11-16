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

namespace Syscodes\Components\Core\Http\Middleware;

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
     * @param  \Closure(\Syscodes\Components\Http\Request): (\Syscodes\Components\Http\Response)  $next
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
        
        return match ($metric) {
            'K', 'k' => $postMaxSize * 1024,
            'M', 'm' => $postMaxSize * 1048576,
            'G', 'g' => $postMaxSize * 1073741824,
            default  => $postMaxSize,
        };
    }
}