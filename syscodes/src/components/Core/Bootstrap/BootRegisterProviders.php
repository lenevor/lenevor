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

namespace Syscodes\Components\Core\Bootstrap;

use Syscodes\Components\Support\ServiceProvider;
use Syscodes\Components\Contracts\Core\Application;

/**
 * Initialize boot of register providers.
 */
class BootRegisterProviders
{
	/**
	 * The service providers that should be merged before registration.
	 * 
	 * @var array $merge
	 */
	protected static $merge = [];
	
	/**
	 * The path to the bootstrap provider configuration file.
	 * 
	 * @var string|null $bootstrapProviderPath
	 */
	protected static $bootstrapProviderPath;

	/**
	 * Bootstrap the given application.
	 * 
	 * @param  \Syscodes\Components\Contracts\Core\Application  $app
	 * 
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		if ( ! $app->bound('config_loaded_from_cache') || $app->make('config_loaded_from_cache') === false) {
			$this->mergeAdditionalProviders($app);
		}

		$app->registerConfiguredProviders();
	}
	
	/**
	 * Merge the additional configured providers into the configuration.
	 * 
	 * @param  \Syscodes\Components\Contracts\Core\Application  $app
	 * 
	 * @return mixed
	 */
	protected function mergeAdditionalProviders(Application $app)
	{
		if (static::$bootstrapProviderPath && file_exists(static::$bootstrapProviderPath)) {
			$packageProviders = require static::$bootstrapProviderPath;
			
			foreach ($packageProviders as $index => $provider) {
				if ( ! class_exists($provider)) {
					unset($packageProviders[$index]);
				}
			}
		}

		$app->make('config')->set(
			'services.providers',
			array_merge(
				$app->make('config')->get('services.providers') ?? ServiceProvider::defaultCoreProviders()->toArray(),
				static::$merge,
				array_values($packageProviders)
			)
		);
	}
	
	/**
	 * Merge the given providers into the provider configuration before registration.
	 * 
	 * @param  array  $providers
	 * @param  string|null  $bootstrapProviderPath
	 * 
	 * @return void
	 */
	public static function merge(array $providers, ?string $bootstrapProviderPath = null): void
	{
		static::$bootstrapProviderPath = $bootstrapProviderPath;
		
		static::$merge = array_values(array_filter(array_unique(
			array_merge(static::$merge, $providers)
		)));
	}
	
	/**
	 * Flush the bootstrapper's global state.
	 * 
	 * @return void
	 */
	public static function flushState(): void
	{
		static::$bootstrapProviderPath = null;
		
		static::$merge = [];
	}
}