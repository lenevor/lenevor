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
 * @since       0.1.2
 */

namespace Syscodes\Core\Bootstrap;

use Syscodes\Config\Configure;
use Syscodes\Contracts\Core\Application;

/**
 * Initialize boot of setting file.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BootConfiguration
{	
	/**
	 * Bootstrap the given application.
	 * 
	 * @param  \Syscodes\Contracts\Core\Application  $app
	 * 
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$app->instance('config', $config = new Configure);

		// Finally, we will set the application's environment based on the configuration
        // values that were loaded. 
		$app->detectEnvironment(function () use ($config) {
		    return $config->get('app.env', 'production');
		});
		
		// Load environment
		$app->bootEnvironment();

		// Set a default timezone if one is defined
		date_default_timezone_set($config->get('app.timezone', 'UTC'));

		mb_internal_encoding('UTF-8');
	}
}