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

namespace Syscodes\Components\Routing\Concerns;

/**
 * Comparison the request to uri matches given route.
 */
trait RouteRequestMatchesGiven
{
    /**
     * Check if given request uri matches given uri method.
     * 
     * @param  string  $route
     * @param  string  $uri
     * @param  string[]  $patterns
     * @param  string[]  $parameters
     * 
     * @return bool
     */
    public function compareUri(
        string $route, 
        string $uri, 
        array $patterns,
        array &$parameters = []
    ): bool {
        $regex = '~^'.$this->regexUri($route, $patterns).'$~';

        return @preg_match($regex, $uri, $parameters, PREG_OFFSET_CAPTURE);
    }
    
    /**
     * Convert route to regex.
     * 
     * @param  string  $route
     * @param  array  $patterns
     * 
     * @return string
     */
    private function regexUri(string $route, array $patterns): string
    {
        return preg_replace_callback(
                    '~/\{([^}]+)\}~', 
                    fn (array $match) => $this->regexParameter($match[1], $patterns), 
                    $route
                );
    }
    
    /**
     * Convert route parameter to regex.
     * 
     * @param  string  $name
     * @param  array  $patterns
     * 
     * @return string
     */
    private function regexParameter(string $name, array $patterns): string
    {
        if ($name[-1] == '?') {
            $name = substr($name, 0, -1);
            $suffix = '?';
        } else {
            $suffix = '';
        }

        $pattern = $patterns[$name] ?? '[^/]+';
        
        return '/(?P<'.$name.'>'.$pattern.')'.$suffix;
    }
}