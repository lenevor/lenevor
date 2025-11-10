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

namespace Syscodes\Components\Routing;

use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * Compiler the routes in the uri.
 */
class RouteCompiler
{
    /**
     * The route instance.
     *
     * @var \Syscodes\component\Routing\Route
     */
    protected $route;

    /**
     * Constructor. Create a new Route compiler instance.
     *
     * @param  \Syscodes\Component\Routing\Route  $route
     * 
     * @return void
     */
    public function __construct($route)
    {
        $this->route = $route;
    }

    /**
     * Compile the route.
     *
     * @return \Symfony\Component\Routing\CompiledRoute
     */
    public function compile()
    {
        $optionals = $this->getOptionalParameters();

        $uri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->route->getUri());

        return (
            new SymfonyRoute($uri, $optionals, $this->route->wheres, [], $this->route->getDomain() ?: '')
        )->compile();
    }

    /**
     * Get the optional parameters for the route.
     *
     * @return array
     */
    protected function getOptionalParameters(): array
    {
        preg_match_all('/\{(\w+?)\?\}/', $this->route->getUri(), $matches);

        return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
    }
}