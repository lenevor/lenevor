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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscode\Routing;

/**
 * Groups attributes according at called for route prefixes or middleware.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class RouteGroup
{
 	/**
	 * Middleware for function of filters
	 *  
	 * @var string[] $middleware
	 */
	public $middleware = [];

 	/**
	 * Variable of prefix route
	 *  
	 * @var string $prefix
	 */
	public $prefix;

	/**
 	 * Definer the middleware's and prefix. Execute the parameters  if a match was found
 	 *
 	 * @param  array|string  $params  The value to middleware/prefix the routes
 	 *
 	 * @return mixed
 	 */
	public function attributes($params)
	{
		if (is_string($params))
		{
			$this->prefix = $params;
		}
		elseif (is_array($params))
		{
			if (isset($params['middleware']))
			{
				if ( ! is_array($params['middleware']))
				{
					$params['middleware'] = [$params['middleware']];
				}
			}

			$this->middleware = ( ! isset($params['middleware']) || ! is_array($option  = $params['middleware'])) ? [] : $option;
			$this->prefix     = ( ! isset($params['prefix']) || ! is_string($option = $params['prefix'])) ? '' : $option;
		}
		else
		{
			if (is_callable($params))
			{
				$prefix               = $params;
				$middleware           = $params;
				$params               = [];
				$params['middleware'] = ['middleware'];
			}

			$this->middleware = $middleware;
			$this->prefix     = $prefix;
		}

		return $this;
	}

 	/**
 	 * Group a series of routes under a single URL segment
 	 *
 	 * @param  string           $params  The value to group/prefix the routes
 	 * @param  \Closure|string  $callback 
 	 *
 	 * @return void
 	 */
 	public function group($params, $callback)
 	{
 		$this->attributes($params);

 		if (is_callable($callback))
 		{
			call_user_func($callback);
 		}

 		$this->prefix     = '';
		$this->middleware = [];

		include $callback;
 	}
}