<?php 

namespace App\Providers;

use Syscode\Support\Facades\Route;
use Syscode\Support\Services\ServiceProvider;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2018-2019 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.9.1
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes
     * 
     * @var     $namespace
     * @access  protected
     * @param   string
     */
    protected $namespace = 'App\Http\Controllers';
    
    /**
     * Define your route model bindings, namespaces, etc
     * 
     * @access  public
     * 
     * @return  void
     */
    public function register()
    {
        Route::setNamespace($this->namespace);
    }

    /**
     * Loaded file of route
     * 
     * @access  public
     * 
     * @return  void
     */
    public function loadMap() {}
}