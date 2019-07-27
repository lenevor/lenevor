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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscode\Core\Http;

use Closure;
use Exception;
use Syscode\Http\{ 
	Request, 
	Response 
};
use Syscode\Debug\Benchmark;
use Syscode\Support\Facades\Http;
use Syscode\Contracts\Core\Application;
use Syscode\Contracts\Core\Lenevor as LenevorContract;

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
	 * @var \Syscode\Contracts\Core\Application $app
	 */
	protected $app;

	/**
	 * Benchmark instance.
	 * 
	 * @var string $benchmark
	 */
	protected $benchmark;
	
	/**
	 * The bootstrap classes for the application.
	 * 
	 * @var array $bootstrappers
	 */
	protected $bootstrappers = [
		\Syscode\Core\Bootstrap\BootDetectEnvironment::class,
		\Syscode\Core\Bootstrap\BootConfiguration::class,
		\Syscode\Core\Bootstrap\BootHandleExceptions::class,
		\Syscode\Core\Bootstrap\BootRegisterFacades::class,
	];
	
	/**
	 * Activate the console mode.
	 * 
	 * @var bool $isCli
	 */
	protected $isCli = false;

	/**
	 * The request implementation.
	 * 
	 * @var string $request
	 */
	protected $request;
	
	/**
	 * Verify if response is activate.
	 * 
	 * @var bool $response
	 */
	protected $response = false;

	/**
	 * Total app execution time.
	 * 
	 * @var float $totalTime
	 */
	protected $totalTime;

	/**
	 * Constructor. Lenevor class instance.
	 * 
	 * @param  \Syscode\Contracts\Core\Application  $app
	 * @param  \Syscode\Http\Request                $request
	 * @param  \Syscode\Debug\Benchmark             $benchmark
	 * 
	 * @return void
	 */
	public function __construct(Application $app, Request $request, Benchmark $benchmark)
	{
		$this->app       = $app;
		$this->request   = $request;
		$this->benchmark = $benchmark;
	}

	/**
	 * Load any custom boot files based upon the current environment.
	 *
	 * @return void
	 */
	protected function bootEnvironment()
	{
		if (file_exists(SYS_PATH.'environment'.DIRECTORY_SEPARATOR.ENVIRONMENT.'.php'))
		{
			require_once SYS_PATH.'environment'.DIRECTORY_SEPARATOR.ENVIRONMENT.'.php';
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
		define('ENVIRONMENT', app('config')->get('app.env'));
	}

 	/**
 	 * The dispatcher of routes.
 	 *
 	 * @param  \Syscode\Http\Request    $request  The request
 	 * @param  \Syscode\Routing\Router  $router  The router (interface)
 	 * 
 	 * @return void
 	 */
 	protected function dispatcher($request, $router)
 	{
		if ( ! $router->initialized)
		{
 			$router->start();
		}
		
 		return $router->resolve($request->getUri(), $request->method());
	}

	/**
 	 * Generates a base URL.
 	 *
 	 * @return string  The base URL
 	 * 
 	 * @uses   \Syscode\Http\Http
 	 */
	public static function getBaseUrl()
	{
		$baseUrl = '';

		if (Http::server('http_host'))
		{
			$baseUrl .= Http::protocol().'://'.Http::server('http_host');
		}

		if (Http::server('script_name'))
		{
			$common = isGetCommonPath([Http::server('request_uri'), Http::server('script_name')]);

			$baseUrl .= $common;
		}

		return rtrim($baseUrl, '/').'/';
	}
	
	/** 
	 * Initialize CLI command.
	 * 
	 * @return bool
	 */
	public function initCli()
	{
		return $this->isCli = Http::isCli();
	}
	 
	/**
	 * Initializes the framework, this can only be called once.
	 * Launch the application.
	 *
	 * @return void
	 * 
	 * @uses   new \Syscode\Http\Response
	 */
	public function handle()
	{
		// Start the benchmark
		$this->startBenchmark();

		// Load configuration system
		$this->bootstrap();

		// Activate environment
		$this->loadEnvironment();
		$this->bootEnvironment();

		// Initialize variable in empty
		$response = '';
		
		// Activate the base URL, the route for html and desactive the route for CLI
		if ( ! $this->initCli())
		{
			if (app('config')->get('app.baseUrl') === null)
			{
				app('config')->set('app.baseUrl', self::getBaseUrl());
			}
			
			// With Dependency Injection
			$dispatch = $this->dispatcher(
							$this->app['request'], 
							$this->app['router']
			);

			$response = new response;
			$response->setContent($this->displayPerformanceMetrics($dispatch));
		}   
		   
		return $response;
	}

	/**
	 * Start the benchmark.
	 * 
	 * @return void
	 */
	protected function startBenchmark()
	{
		$this->benchmark->start('total_execution', LENEVOR_START);
	}

	/**
	 * Replaces the memory_usage and elapsed_time tags.
	 * 
	 * @param  string  $output
	 * 
	 * @return string
	 */
	public function displayPerformanceMetrics($output)
	{
		$this->totalTime = $this->benchmark->getElapsedTime('total_execution');

		$output = str_replace('{elapsed_time}', $this->totalTime, $output);

		return $output;
	}
	 
 	/**
	 * Takes a value and checks if it is a Closure or not, if it is it
	 * will return the result of the closure, if not, it will simply return the
	 * value.
	 *
	 * @param  mixed  $var  The value to get
	 *
	 * @return mixed
	 * 
	 * @uses   \Closure
	 */
	public static function value($var)
	{
		return $var instanceof Closure ? $var() : $var;
	}
 }