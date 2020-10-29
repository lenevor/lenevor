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
 * @since       0.7.3
 */

namespace Syscodes\Core\Http;

use Closure;
use Exception;
use Throwable;
use Syscodes\Http\Http; 
use Syscodes\Routing\Router;
use Syscodes\Pipeline\Pipeline;
use Syscodes\Support\Facades\Facade;
use Syscodes\Contracts\Core\Application;
use Syscodes\Contracts\Debug\ExceptionHandler;
use Syscodes\Contracts\Core\Lenevor as LenevorContract;
use Syscodes\Debug\FatalExceptions\FatalThrowableError;

/**
 * The Lenevor class is the heart of the system framework.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Lenevor implements LenevorContract
{
	/**
	 * The application implementation.
	 * 
	 * @var \Syscodes\Contracts\Core\Application $app
	 */
	protected $app;
	
	/**
	 * The bootstrap classes for the application.
	 * 
	 * @var array $bootstrappers
	 */
	protected $bootstrappers = [
		\Syscodes\Core\Bootstrap\BootDetectEnvironment::class,
		\Syscodes\Core\Bootstrap\BootConfiguration::class,
		\Syscodes\Core\Bootstrap\BootHandleExceptions::class,
		\Syscodes\Core\Bootstrap\BootRegisterFacades::class,
		\Syscodes\Core\Bootstrap\BootRegisterProviders::class,
		\Syscodes\Core\Bootstrap\BootProviders::class,
	];
	
	/**
	 * Activate the console mode.
	 * 
	 * @var bool $isCli
	 */
	protected $isCli = false;

	/**
	 * The router instance.
	 * 
	 * @var \Syscodes\Routing\Router $router
	 */
	protected $router;

	/**
	 * Total app execution time.
	 * 
	 * @var float $totalTime
	 */
	protected $totalTime;

	/**
	 * Constructor. Lenevor class instance.
	 * 
	 * @param  \Syscodes\Contracts\Core\Application  $app
	 * 
	 * @return void
	 */
	public function __construct(Application $app, Router $router)
	{
		$this->app    = $app;
		$this->router = $router;
	}

	/**
	 * Load any custom boot files based upon the current environment.
	 *
	 * @return void
	 */
	protected function bootEnvironment()
	{
		if (file_exists(SYS_PATH.'src'.DIRECTORY_SEPARATOR.'environment'.DIRECTORY_SEPARATOR.ENVIRONMENT.'.php'))
		{
			require_once SYS_PATH.'src'.DIRECTORY_SEPARATOR.'environment'.DIRECTORY_SEPARATOR.ENVIRONMENT.'.php';
		}
		else
		{
			header('HTTP/1.1 503 Service Unavailable.', true, 503);
			print('The application environment is not set correctly.');
			exit(0); // EXIT_ERROR
		}
	}

	/**
	 * Bootstrap the application for HTTP requests.
	 * 
	 * @return void
	 */
	protected function bootstrap()
	{
		if ( ! $this->app->hasBeenBootstrapped())
		{
			$this->app->bootstrapWith($this->bootstrappers());
		}
	}

	/**
	 * Get the bootstrap classes for the application.
	 * 
	 * @return array
	 */
	protected function bootstrappers()
	{
		return $this->bootstrappers;
	}

	/**
	 * You can load different configurations depending on your
	 * current environment. Setting the environment also influences
	 * things like logging and error reporting.
	 *
	 * This can be set to anything, but default usage is:
	 *
	 *     local (development)
	 *     testing
	 *     production
	 *
	 * @return string
	 */
	protected function loadEnvironment()
	{
		define('ENVIRONMENT', $this->app['config']['app.env']);
	}
	
	/** 
	 * Initialize CLI command.
	 * 
	 * @return bool
	 */
	public function initCli()
	{
		return $this->isCli = (new Http)->isCli();
	}
	 
	/**
	 * Initializes the framework, this can only be called once.
	 * Launch the application.
	 * 
	 * @param  \Syscodes\http\Request  $request
	 *
	 * @return \Syscodes\Http\Response
	 */
	public function handle($request)
	{
		try
		{
			$response = $this->sendRequestThroughRouter($request);
		}
		catch (Exception $e)
		{
			$this->reportException($e);

			$response = $this->renderException($request, $e);
		}
		catch (Throwable $e)
		{
			$this->reportException($e = new FatalThrowableError($e));

			$response = $this->renderException($request, $e);
		}		

		return $response;
	}

	/**
	 * Send the given request through the router.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * 
	 * @return \Syscodes\Http\Response
	 */
	protected function sendRequestThroughRouter($request)
	{
		$this->app->instance('request', $request);  

		Facade::clearResolvedInstance('request');
		
		// Initialize environment
		$this->loadEnvironment();	
				
		// Activate environment
		$this->bootEnvironment();

		// Load configuration system
		$this->bootstrap();

		return (new Pipeline($this->app))
				->send($request)
				->then($this->dispatchToRouter());
	}

	/**
	 * Get the dispatcher of routes.
	 * 	  
	 * @return \Closure
 	 */
	protected function dispatchToRouter()
	{
		return function ($request) {
			$this->app->instance('request', $request);

			return $this->router->dispatch($request);
		};
	}

	/**
	 * Report the exception to the exception handler.
	 * 
	 * @param  \Exception  $e
	 * 
	 * @return void
	 */
	protected function reportException(Exception $e)
	{
		return $this->app[ExceptionHandler::class]->report($e);
	}
	
	/**
	 * Render the exception to a response.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * @param  \Exception  $e
	 * 
	 * @return \Syscodes\Http\Response
	 */
	protected function renderException($request, Exception $e)
	{
		return $this->app[ExceptionHandler::class]->render($request, $e);
	}
 }