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
 * @since       0.7.2
 */

namespace Syscodes\Routing;

use Syscodes\Http\RedirectResponse;

/**
 * Returns redirect of the routes defined by the user.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Redirector
{
    /**
     * The URL generator instance.
     * 
     * @var string $generator
     */
    protected $generator;

    /**
     * Constructor. The Redirector class instance.
     * 
     * @param  \Syscodes\Routing\UrlGenerator  $generator
     * 
     * @return void
     */
    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Create a new redirect response to the "home" route.
     * 
     * @param  int  $status  (302 by default)
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function home($status = 302)
    {
        return $this->to($this->generator->route('home'), $status);
    }

    /**
     * Create a new redirect response to the previous location.
     * 
     * @param  int  $status  (302 by default)
     * @param  array  $headers
     * @param  mixed  $fallback  (false by default)
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function back($status = 302, $headers = [], $fallback = false)
    {
        return $this->createRedirect($this->generator->previous($fallback), $status, $headers);
    }

    /**
     * Create a new redirect response to the current URI.
     * 
     * @param  int  $status  (302 by default)
     * @param  array  $headers
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function refresh($status = 302, $headers = [])
    {
        return $this->to($this->generator->getRequest()->path(), $status, $headers);
    }

    /**
     * Create a new redirect response to the given path.
     * 
     * @param  string  $path
     * @param  int  $status  (302 by default)
     * @param  array  $headers
     * @param  bool|null  $secure  (null by default)
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        return $this->createRedirect($this->generator->to($path, [], $secure), $status, $headers);
    }

    /**
     * Create a new redirect response to an external URL (no validation).
     * 
     * @param  string  $path
     * @param  int  $status  (302 by default)
     * @param  array  $headers
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function away($path, $status = 302, $headers = [])
    {
        return $this->createRedirect($path, $status, $headers);
    }

    /**
     * Create a new redirect response to the given HTTPS path.
     * 
     * @param  string  $path
     * @param  int  $status  (302 by default)
     * @param  array  $headers
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function secure($path, $status = 302, $headers = [])
    {
        return $this->to($path, $status, $headers, true);
    }

    /**
     * Create a new redirect response to a named route.
     * 
     * @param  string  $route
     * @param  array  $parameters
     * @param  int  $status  (302 by default)
     * @param  array  $headers
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function route($route, $parameters = [], $status = 302, $headers = [])
    {
        $path = $this->generator->route($route, $parameters);

        return $this->to($path, $status, $headers);
    }

    /**
     * Create a new redirect response to a controller action.
     * 
     * @param  string|array  $route
     * @param  array  $parameters
     * @param  int  $status  (302 by default)
     * @param  array  $headers
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    public function action($route, $parameters = [], $status = 302, $headers = [])
    {
        $path = $this->generator->action($route, $parameters);

        return $this->to($path, $status, $headers);
    }

    /**
     * Creates a new redirect response.
     * 
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * 
     * @return \Syscodes\Http\RedirectResponse
     */
    protected function createRedirect($path, $status, $headers)
    {
        return take(new RedirectResponse($path, $status, $headers), function($redirect) {
            $redirect->setRequest($this->generator->getRequest());
        });
    }
}