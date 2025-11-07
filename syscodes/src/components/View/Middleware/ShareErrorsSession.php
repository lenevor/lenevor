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

namespace Syscodes\Components\View\Middleware;

use Closure;
use Syscodes\Components\Http\Response;
use Syscodes\Components\Support\ViewErrorBag;
use Syscodes\Components\Contracts\View\Factory as ViewFactory;

/**
 * Determines an error message when using sessions.
 */
class ShareErrorsSession
{
    /**
     * The view factory implementation.
     * 
     * @var \Syscodes\Components\Contracts\View\Factory $view
     */
    protected $view;
    
    /**
     * Constructor. Create a new error binder instance.
     * 
     * @param  \Syscodes\Components\Contracts\View\Factory  $view
     * 
     * @return void
     */
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Response)  $next
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function handle($request, Closure $next): Response
    {
        // If the current session has an "errors" variable bound to it, its value 
        // is shared with all the view instances so that error messages can be 
        // easily accessed. An empty bag is set when there aren't errors.
        $this->view->share(
            'errors', $request->session()->get('errors') ?: new ViewErrorBag
        );
        
        // Including errors in the view allows the developer to assume that errors 
        // will always be present available, which is very convenient since they 
        // don't have to continually run checks to detect the presence of errors.
        return $next($request);    
    }
}