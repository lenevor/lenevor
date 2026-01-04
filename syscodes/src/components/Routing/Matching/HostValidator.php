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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Matching;

use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Route;

/**
 * Allows validate the host with given rules.
 */
class HostValidator implements ValidatorInterface
{
    /**
     * Validate a given rule against a route and request.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return bool
     */
    public function matches(Route $route, Request $request): bool
    {
        $hostRegex = '~[^/]+~';
        
        if (is_null($hostRegex)) {
            return true;
        }
        
        return preg_match($hostRegex, $request->getHost());
    }
}