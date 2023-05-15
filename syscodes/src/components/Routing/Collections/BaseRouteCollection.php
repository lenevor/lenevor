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

namespace Syscodes\Components\Routing\Collections;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException;

/**
 * Allows the route collection of base. 
 */
final class BaseRouteCollection implements Countable, IteratorAggregate
{
    /**
     * Handle the matched route.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Routing\Route|null  $route
     * 
     * @return \Syscodes\Components\Routing\Route
     * 
     * @throws \Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException
     */
    public function handleMatchedRoute(Request $request, $route)
    {
        if ( ! is_null($route)) {
            return $route->bind($request);
        }

        throw new NotFoundHttpException(sprintf(
            'The route "%s" could not be found', $request->path()
            ));
    }

    /**
     * Get an iterator for the items.
     * 
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator([]);
    }
    
    /**
     * Count the number of items in the collection.
     * 
     * @return int
     */
    public function count(): int
    {
        return count([]);
    }
}