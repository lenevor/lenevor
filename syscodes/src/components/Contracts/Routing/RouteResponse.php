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

namespace Syscodes\Components\Contracts\Routing;

use Syscodes\Components\Http\Response;

/**
 * This class allows you to control the use of the HTTP response 
 * along with routes redirection.
 */
interface RouteResponse
{
    /**
     * Return a new response from the application.
     *
     * @param  string  $body
     * @param  int  $status
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function make($body = '', $status = 200, array $headers = []): Response;

    /**
     * Creates a new 'no content' response.
     * 
     * @param  int  $status
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function noContent($status = 204, array $headers = []): Response;

    /**
     * Return a new View Response from the application.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  int  $status
     * @param  array  $headers
     * 
     * @return  \Syscodes\Components\Http\Response
     */
    public function view(
        $view,
        array $data = [],
        $status = 200,
        array $headers = []
    ): Response;

    /**
     * Create a new JSON response instance.
     * 
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * 
     * @return \Syscodes\Components\Http\JsonResponse
     */
    public function json(
        $data = [],
        $status = 200,
        array $headers = [],
        $options = 0
    );

    /**
     * Create a new redirect response to the given path.
     * 
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function redirectTo(
        $path, 
        $status = 302, 
        $headers = [], 
        $secure = null
    );
    
    /**
     * Create a new redirect response to a named route.
     * 
     * @param  string  $route
     * @param  mixed  $parameters
     * @param  int  $status
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function redirectToRoute(
        $route,
        $parameters = [],
        $status = 302,
        $headers = []
    );
    
    /**
     * Create a new redirect response to a controller action.
     * 
     * @param  string  $action
     * @param  mixed  $parameters
     * @param  int  $status
     * @param  array  $headers
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function redirectToAction(
        $action,
        $parameters = [],
        $status = 302,
        $headers = []
    );
    
    /**
     * Create a new redirect response, while putting the current URL in the session.
     * 
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function redirectGuest(
        $path,
        $status = 302,
        $headers = [],
        $secure = null
    );
    
    /**
     * Create a new redirect response to the previously intended location.
     * 
     * @param  string  $default
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * 
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    public function redirectToIntended(
        $default = '/',
        $status = 302,
        $headers = [],
        $secure = null
    );
}