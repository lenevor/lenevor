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

use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Route;
use Syscodes\Components\Routing\Controller;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Http\RedirectResponse;
use Syscodes\Components\Routing\Generators\UrlGenerator;

/**
 * Returns redirect using a controller for the routes defined 
 * by the user.
 */
class RedirectController extends Controller
{
    /**
     * Invoke the controller method.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Routing\Generators\UrlGenerator  $url
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function __invoke(Request $request, UrlGenerator $url)
    {
        $parameters = new Collection($request->route()->parameters());

        $status = $parameters->get('status');

        $destination = $parameters->get('destination');

        $parameters->erase('status')->erase('destiantion');

        $route = (new Route('GET', $destination, [
            'as' => 'lenevor_redirect_destination',
        ]))->bind($request);
        
        $parameters = $parameters->only(
            $route->getCompiled()->getPathVariables()
        )->all();

        $url = $url->toRoute($route, $parameters, false);
        
        if ( ! Str::startsWith($destination, '/') && Str::startsWith($url, '/')) {
            $url = Str::after($url, '/');
        }

        return new RedirectResponse($url, $status);
    }
}