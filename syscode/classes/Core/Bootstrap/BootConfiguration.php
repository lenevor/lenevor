<?php 

namespace Syscode\Core\Bootstrap;

use Exception;
use Syscode\Config\Configure;
use Syscode\Contracts\Core\Application;

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
 * @copyright   Copyright (c) 2018-2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.8.0
 */
class BootConfiguration
{
	/**
	 * The application implementation.
	 * 
	 * @var \Syscode\Contracts\Core\Application $app
	 */
	protected $app;
	
	/**
	 * Set default timezone on the server.
	 * 
	 * @var string $timezone
	 */
	protected $timezone = 'UTC';
	
	/**
	 * Bootstrap the given application.
	 * 
	 * @param  \Syscode\Contracts\Core\Application  $app
	 * 
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$this->app = $app;
		
		$this->timezone();
		
		mb_internal_encoding('UTF-8');
	}
	
	/**
	 * Returns the timezone the application has been set to display
	 * dates in. This might be different than the timezone set
	 * at the server level, as you often want to stores dates in UTC
	 * and convert them on the fly for the user.
	 *
	 * @return void
	 * 
	 * @uses   \Syscode\Config\Configure::get()
	 */
	protected function timezone()
	{
		$this->app->instance('config', $config = new Configure);

		try
		{
			// set a default timezone if one is defined
			$this->timezone = $config->get('app.timezone') ?? date_default_timezone_get();
			date_default_timezone_set($this->timezone);
		}
		catch(Exception $e)
		{
			date_default_timezone_set('UTC');
			throw new Exception($e->getMessage());
		}

		mb_internal_encoding('UTF-8');
	}
}