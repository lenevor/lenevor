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

namespace Syscodes\Routing;

use LogicException;
use Syscodes\Support\Str;
use UnexpectedValueException;
use Syscodes\Collections\Arr;

/**
 * Solve the actions obtained from a route.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class RouteAction
{
    /**
     * Parse the given action into an array.
     * 
     * @param  string  $uri
     * @param  mixed  $action
     * 
     * @return array
     */
    public static function parse($uri, $action)
    {
        if (is_null($action)) {
            return static::usesAction($uri);
        }

        if (is_callable($action, true)) {
            return ! is_array($action) ? ['uses' => $action] : [
                    'uses' => $action[0].'@'.$action[1],
                    'controller' => $action[0].'@'.$action[1],
            ];
        } elseif ( ! isset($action['uses'])) {
            $action['uses'] = static::findClosureAction($action);
        }
        
        return $action;
    }
    
    /**
     * Get an action for a route that has no action.
     * 
     * @param  string  $uri
     *
     * @return array
     * 
     * @throws \LogicException
     */
    protected static function usesAction($uri)
    {
        return ['uses' => function () use ($uri) {
            throw new LogicException(__('route.hasNoAction', ['uri' => $uri]));
        }];
    }
    
    /**
     * Find the callable in an action array.
     * 
     * @param  array  $action
     * 
     * @return \Closure
     */
    protected static function findClosureAction(array $action)
    {
        return Arr::first($action, function ($value, $key) {
            return is_callable($value) && is_numeric($key);
        });
    }
} 