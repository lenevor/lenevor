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

namespace Syscodes\Components\View;

use InvalidArgumentException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Contracts\View\ViewFinder;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\View\Engines\EngineResolver;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\View\Factory as FactoryContract;

/**
 * This class allows parser of a view.
 */
class Factory implements FactoryContract
{
	use Extensions,
	    Concerns\ManagesStacks,
	    Concerns\ManagesLayouts,
	    Concerns\ManagesComponents,
	    Concerns\ManagesTranslations;
	
	/**
	 * The IoC container instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Container\Container $container
	 */
	protected $container;

	/**
	 * The engine implementation.
	 * 
	 * @var \Syscodes\Components\View\Engines\EngineResolver $engines
	 */
	protected $engines;

	/**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Events\Dispatcher $events
	 */
	protected $events;

	/**
	 * The view finder implementation.
	 * 
	 * @var \Syscodes\Components\View\FileViewFinder $finder
	 */
	protected $finder;

	/**
	 * The number of active rendering operations.
	 * 
	 * @var int $renderCount
	 */
	protected $renderCount = 0;

	/**
	 * Array of shared data.
	 * 
	 * @var array $shared
	 */
	protected $shared = [];

	/**
	 * Constructor. Create a new Parser class instance.
	 * 
	 * @param  \Syscodes\Components\View\Engines\EngineResolver  $engine
	 * @param  \Syscodes\Components\Contracts\View\ViewFinder  $finder
	 * @param  \Syscodes\Components\Contracts\Events\Dispatcher  $events
	 *
	 * @return void
	 */
	public function __construct(EngineResolver $engines, ViewFinder $finder, Dispatcher $events)
	{
		$this->finder  = $finder;
		$this->engines = $engines;
		$this->events  = $events;

		$this->share('__env', $this);
	}
	
	/**
	 * Check existance view file.
	 * 
	 * @param  string  $view
	 *
	 * @return bool
	 */
	public function viewExists($view): bool
	{
		try {
			$this->finder->find($view);
		} catch(InvalidArgumentException $e) {
			return false;
		}

		return true;
	}
	
	/**
	 * Global and local data are merged and extracted to create local variables within the view file.
	 * Renders the view object to a string.
	 *
	 * @example $output = $view->make();
	 *
	 * @param  string  $view  View filename
	 * @param  array  $data  Array of values
	 * @param  array  $mergeData  Array of merge data
	 *
	 * @return \Syscodes\Components\Contracts\View\View
	 */
	public function make($view, $data = [], $mergeData = [])
	{
		$path = $this->finder->find(
			$view = $this->normalizeName($view)
		);

		$data = array_merge($mergeData, $this->parseData($data));
		
		// Loader class instance.
		return take($this->viewInstance($view, $path, $data), fn ($view) => $this->callCreator($view));
	}

	/**
	 * Normalize a view name.
	 * 
	 * @param  string  $name
	 * 
	 * @return string
	 */
	protected function normalizeName($name): string
	{
		return ViewName::normalize($name);
	}
	
	/**
	 * Parse the given data into a raw array.
	 * 
	 * @param  mixed  $data
	 * 
	 * @return array
	 */
	protected function parseData($data): array
	{
		return $data instanceof Arrayable ? $data->toArray() : $data;
	}

	/**
	 * Create a new view instance from the given arguments.
	 * 
	 * @param  string  $file  View filename
	 * @param  string  $path  Path filename
	 * @param  array  $data  Array of values
	 * 
	 * @return \Syscodes\Components\Contracts\View\View
	 */
	protected function viewInstance($view, $path, $data)
	{
		return new View($this, $this->getEngineFromPath($path), $view, $path, $data);
	}
	
	/**
	 * Get the appropriate view engine for the given path.
	 * 
	 * @param  string  $path
	 * 
	 * @return \Syscodes\Components\Contracts\View\Engine
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function getEngineFromPath($path)
	{
		if ( ! $extension = $this->getExtension($path)) {
			throw new InvalidArgumentException("Unrecognized extension in file: {$path}");
		}
		
		$engine = $this->extensions[$extension];
		
		return $this->engines->resolve($engine);
	}
	
	/**
	 * Get the extension used by the view file.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	protected function getExtension($path)
	{
		$extensions = array_keys($this->extensions);
		
		return Arr::first($extensions, fn ($value) => Str::endsWith($path, '.'.$value));
	}
	
	/**
	 * Call the creator for a given view.
	 * 
	 * @param  \Syscodes\Components\View\View  $view
	 * 
	 * @return void
	 */
	public function callCreator(View $view): void
	{
		$this->events->dispatch('creating: '.$view->getView(), [$view]);
	}
	
	/**
	 * Get the extension to engine bindings.
	 * 
	 * @return array
	 */
	public function getExtensions(): array
	{
		return $this->extensions;
	}
	
	/**
	 * Add a piece of shared data to the environment.
	 * 
	 * @param  array|string  $key
	 * @param  mixed|null  $value  
	 * 
	 * @return mixed
	 */
	public function share($key, $value = null)
	{
		$keys = is_array($key) ? $key : [$key => $value];
		
		foreach ($keys as $key => $value) {
			$this->shared[$key] = $value;
		}
		
		return $value;
	}
	
	/**
	 * Add a new namespace to the loader.
	 * 
	 * @param  string  $namespace
	 * @param  string|array  $hints
	 * 
	 * @return static
	 */
	public function addNamespace($namespace, $hints): static
	{
		$this->finder->addNamespace($namespace, $hints);
		
		return $this;
	}

	/**
	 * Replace the namespace hints for the given namespace.
	 * 
	 * @param  string  $namespace
	 * @param  string|array  $hints
	 * 
	 * @return static
	 */
	public function replaceNamespace($namespace, $hints): static
	{
		$this->finder->replaceNamespace($namespace, $hints);

		return $this;
	}

	/**
	 * Increment the rendering counter.
	 * 
	 * @return int
	 */
	public function increment(): int
	{
		return $this->renderCount++;
	}

	/**
	 * Decrement the rendering counter.
	 * 
	 * @return int
	 */
	public function decrement(): int
	{
		return $this->renderCount--;
	}

	/**
	 * Check if there are no active render operations.
	 * 
	 * @return bool
	 */
	public function doneRendering(): bool
	{
		return $this->renderCount == 0;
	}

	/**
	 * Flush all of the parser state like sections.
	 * 
	 * @return void
	 */
	public function flushState(): void
	{
		$this->renderCount = 0;

		$this->flushSections();
	}

	/**
	 * Flush all of the section contents if done rendering.
	 * 
	 * @return void
	 */
	public function flushStateIfDoneRendering(): void
	{
		if ($this->doneRendering()) {
			$this->flushState();
		}
	}

	/**
	 * Get all of the shared data for the environment.
	 * 
	 * @return array
	 */
	public function getShared(): array
	{
		return $this->shared;
	}

	/**
	 * Get the IoC container instance.
	 * 
	 * @return \Syscodes\Components\Contracts\Container\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Set the IoC container instance.
	 * 
	 * @param  \Syscodes\Components\Contracts\Container\Container  $container
	 * 
	 * @return void
	 */
	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}
}