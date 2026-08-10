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

namespace Syscodes\Components\Routing;

use LogicException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use UnexpectedValueException;

/**
 * Solve the actions obtained from a route.
 */
class RouteAction
{
    /**
     * Parse the given action into an array.
     * 
     * @param  string|array  $uri
     * @param  mixed  $action 
     * @return array
     */
    public static function parse($uri, $action): array
    {
        // If no action is passed in right away, we assume the user will make use of
        // fluent routing.
        if (is_null($action)) {
            return static::usesAction($uri);
        }

        // If the action is already a Closure instance, we will just set that instance
        // as the "uses" property, because there is nothing else we need to do when
        // it is available.
        if (is_callable($action, true)) {
            return ! is_array($action) ? ['uses' => $action] : [
                'uses' => $action[0].'@'.$action[1],
                'controller' => $action[0].'@'.$action[1],
            ];
        // If no "uses" property has been set, we will dig through the array to find a
        // Closure instance within this list.
        } elseif ( ! isset($action['uses'])) {
            $action['uses'] = static::findClosureAction($action);
        }
        
        if (is_string($action['uses']) && ! Str::contains($action['uses'], '@')) {
            $action['uses'] = static::callInvokable($action['uses']);
        }
        
        return $action;
    }
    
    /**
     * Get an action for a route that has no action.
     * 
     * @param  string  $uri
     * @return array
     * 
     * @throws \LogicException
     */
    protected static function usesAction($uri): array
    {
        return ['uses' => fn () => throw new LogicException(__('route.hasNoAction', ['uri' => $uri]))];
    }
    
    /**
     * Find the callable in an action array.
     * 
     * @param  array  $action 
     * @return \Closure
     */
    protected static function findClosureAction(array $action)
    {
        return Arr::first($action, fn ($value, $key) => is_callable($value) && is_numeric($key));
    }
    
    /**
     * Call an action for an invokable controller.
     * 
     * @param  string  $action 
     * @return string
     * 
     * @throws \UnexpectedValueException
     */
    protected static function callInvokable($action): string
    {
        if ( ! method_exists($action, '__invoke')) {
            throw new UnexpectedValueException("Invalid route action: [{$action}].");
        }
        
        return $action.'@__invoke';
    }
} 