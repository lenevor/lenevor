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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.5.0
 */

namespace Syscodes\Routing;

use LogicException;
use UnexpectedValueException;
use Syscodes\Support\Arr;
use Syscodes\Support\Str;

/**
 * Solve the actions obtained from a route.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
        if (is_null($action))
        {
            return static::usesAction($uri);
        }

        if (is_callable($action, true))
        {
            return ! is_array($action) ? ['uses' => $action] : [
                    'uses' => $action[0].'@'.$action[1],
                    'controller' => $action[0].'@'.$action[1],
            ];
        }
        elseif ( ! isset($action['uses']))
        {
            $action['uses'] = static::findClosureAction($action);
        }
        
        if (is_string($action['uses']) && ! Str::contains($action['uses'], '@'))
        {
            $action['uses'] = static::makeInvokable($action['uses']);
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
    
    /**
     * Make an action for an invokable controller.
     * 
     * @param  string  $action
     * 
     * @return string
     * 
     * @throws \UnexpectedValueException
     */
    protected static function makeInvokable($action)
    {
        if ( ! method_exists($action, '__invoke'))
        {
            throw new UnexpectedValueException(__('route.invalidAction', ['action' => $action]));
        }
        
        return $action.'@__invoke';
    }
} 