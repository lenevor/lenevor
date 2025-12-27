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

namespace Syscodes\Components\Routing\Controllers;

use Syscodes\Components\Contracts\Routing\RouteResponse;
use Syscodes\Components\Routing\Controller;

/**
 * Returns view using a controller for the routes defined 
 * by the user.
 */
class ViewController extends Controller
{
    /**
     * The response factory implementation.
     * 
     * @var \Syscodes\Components\Routing\Supported\RouteResponse $response
     */
    protected $response;

    /**
     * Constructor. Create a new ViewController class instance.
     * 
     * @param  \Syscodes\Components\Routing\Supported\RouteResponse  $response
     * 
     * @return void
     */
    public function __construct(RouteResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Invoke the controller method.
     * 
     * @param  mixed  ...$args
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function __invoke(...$args)
    {
        $parameters = array_filter($args, function($key) {
            return ! in_array($key, ['view', 'data', 'status', 'headers']);
        }, ARRAY_FILTER_USE_KEY);

        $args['data'] = array_merge($args['data'], $parameters);

        return $this->response->view(
            $args['view'],
            $args['data'],
            $args['status'],
            $args['headers']
        );
    }

    /**
     * Execute an action on the controller.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function callAction($method, $parameters): mixed
    {
        return $this->{$method}(...$parameters);
    }
}