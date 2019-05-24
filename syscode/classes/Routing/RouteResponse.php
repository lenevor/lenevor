<?php 

namespace Syscode\Routing;

use Syscode\Http\Response;
use Syscode\Contracts\View\View as ViewFactory;

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
class RouteResponse
{
    /**
     * The View class instance.
     * 
     * @var \Syscode\Contracts\View\Factory $view
     */
    protected $view;

    /**
     * Constructor. Create a new RouteResponse instance.
     * 
     * @param  \Syscode\Contracts\View\View|string  $view
     * 
     * @return void  
     */
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    /**
     * Return a new response from the application.
     *
     * @param  string  $body
     * @param  int     $status  The default 200
     * @param  array   $headers
     * 
     * @return \Syscode\Http\Response
     */
    public function make($body = '', $status = 200, array $headers = [])
    {
        return new Response($body, $status, $headers);
    }

    /**
     * Return a new view Response from the application.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  int     $status  The default 200
     * @param  array   $headers
     * 
     * @return  \Syscode\Http\Response
     */
    public function view($view, array $data = [], $status = 200, array $headers = [])
    {
        return static::make($this->view->make($view, $this->view->getData($data)), $status, $headers);
    }
}