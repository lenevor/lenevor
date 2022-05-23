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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\View;

use InvalidArgumentException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Contracts\View\ViewFinder;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\View\Engines\EngineResolver;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\View\Factory as FactoryContract;

/**
 * This class allows parser of a view.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
	 * @var \Syscodes\Contracts\Container\Container $container
	 */
	protected $container;

	/**
	 * The engine implementation.
	 * 
	 * @var \Syscodes\View\Engines\EngineResolver $engines
	 */
	protected $engines;

	/**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Contracts\Events\Dispatcher $events
	 */
	protected $events;

	/**
	 * The view finder implementation.
	 * 
	 * @var \Syscodes\View\FileViewFinder $finder
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
	 * Constructor: Create a new Parser class instance.
	 * 
	 * @param  \Syscodes\View\Engines\EngineResolver  $engine
	 * @param  \Syscodes\Contracts\View\ViewFinder  $finder
	 * @param  \Syscodes\Contracts\Events\Dispatcher  $events
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function make($view, $data = [])
	{
		$path = $this->finder->find(
			$view = $this->normalizeName($view)
		);
		
		// Loader class instance.
		return take($this->viewInstance($view, $path, $data), function ($view) {
			$this->callCreator($view);
		});
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
	 * Create a new view instance from the given arguments.
	 * 
	 * @param  string  $file  View filename
	 * @param  string  $path  Path filename
	 * @param  array  $data  Array of values
	 * 
	 * @return \Syscodes\Contracts\View\View
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
		
		return Arr::first($extensions, function($value) use ($path) {
			return Str::endsWith($path, '.'.$value);
		});
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function callCreator(View $view): void
	{
		$this->events->dispatch('creating: '.$view->getView(), [$view]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getExtensions(): array
	{
		return $this->extensions;
	}
	
	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function replaceNamespace($namespace, $hints): self
	{
		$this->finder->replaceNamespace($namespace, $hints);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function increment(): int
	{
		return $this->renderCount++;
	}

	/**
	 * {@inheritdoc}
	 */
	public function decrement(): int
	{
		return $this->renderCount--;
	}

	/**
	 * {@inheritdoc}
	 */
	public function doneRendering(): bool
	{
		return $this->renderCount == 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function flushState(): void
	{
		$this->renderCount = 0;

		$this->flushSections();
	}

	/**
	 * {@inheritdoc}
	 */
	public function flushStateIfDoneRendering(): void
	{
		if ($this->doneRendering()) {
			$this->flushState();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getShared(): array
	{
		return $this->shared;
	}

	/**
	 * Get the IoC container instance.
	 * 
	 * @return \Syscodes\Contracts\Container\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Set the IoC container instance.
	 * 
	 * @param  \Syscodes\Contracts\Container\Container  $container
	 * 
	 * @return void
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}
}