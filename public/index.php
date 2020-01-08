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
 * @since       0.1.0
 */

/*
|------------------------------------------------------------------------
| REGISTER THE COMPOSER AUTOLOADER
|------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader 
| for our application. We will need it so that we do not have to worry 
| about loading any class of third party "manually".
|
*/

if (is_file(__DIR__.'/../vendor/autoload.php'))
{
    require __DIR__.'/../vendor/autoload.php';
}

/*
|------------------------------------------------------------------------
| LOAD UP APPLICATION
|------------------------------------------------------------------------
|
| This bootstraps the framework and gets it ready for use, then it will 
| load up this application so that we can run. the responses back to the 
| browser and viewed for ours users.
|
*/

chdir(__DIR__);

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|------------------------------------------------------------------------
| Run The Application                                               
|------------------------------------------------------------------------
|
| Now that everything is setup, it's time to actually fire up the engines 
| and make this app do its thing. It is activated from the core.
|
*/

$lenevor = $app->make(Syscode\Contracts\Core\Lenevor::class);

// Initialize services
$response = $lenevor->handle(
    $request = $app->make(Syscode\Http\Request::class)
);

// Sends HTTP headers and content
$response->send(true);