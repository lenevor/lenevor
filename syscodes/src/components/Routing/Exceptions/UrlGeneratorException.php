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

namespace Syscodes\Components\Routing\Exceptions;

use Exception;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Routing\Route;

/**
 * UrlGeneratorException.
 */
class UrlGeneratorException extends Exception
{
    /**
     * Create a new exception for missing route parameters.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array  $parameters
     * 
     * @return static
     */
    public static function missingParameters(Route $route, array $parameters = []): static
    {
        $parameter = Str::plural('parameter', count($parameters));
        
        $message = sprintf('Missing required %s for [Route: %s] [URI: %s]',
            $parameter,
            $route->getName(),
            $route->getUri()
        );
        
        if (count($parameters) > 0) {
            $message .= sprintf(' [Missing %s: %s]', $parameter, implode(', ', $parameters));
        }
        
        $message .= '.';
        
        return new static($message);
    }
}