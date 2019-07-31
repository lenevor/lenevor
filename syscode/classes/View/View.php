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

namespace Syscode\View;

use Exception;
use Traversable;
use Syscode\Support\Finder;
use InvalidArgumentException;
use Syscode\Contracts\Core\Http\Lenevor;
use Syscode\View\Exceptions\ViewException;
use Syscode\Contracts\View\View as ViewContract;
use Syscode\Core\Http\Exceptions\LenevorException;

/**
 * This class control the views.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class View implements ViewContract
{
	/**
	 * Array of global variables.
	 * 
	 * @var array $globalData
	 */
	protected static $globalData = [];

	/**
	 * Empty default block to be extended by child templates.
	 *
	 * @var array $blocks
	 */
	protected $blocks = [];

	/**
	 * Array of local variables.
	 *
	 * @var array $data
	 */
	protected $data = [];

	/**
	 * Extend a parent template.
	 *
	 * @var string $extend
	 */
	protected $extend;

	/**
	 * The extension to engine bindings.
	 *
	 * @var string $extension
	 */
	protected $extension;

	/**
	 * Set the view filename.
	 *
	 * @var string|null $filename
	 */
	protected $filename = null;

	/**
	 * Started blocks.
	 *
	 * @var array $sections
	 */
	protected $sections = [];

	/**
	 * Constructor: Call the file and your data.
	 *
	 * @example $view = new View($file);
	 * 
	 * @param  string       $file  View filename
	 * @param  array        $data  Array of values
	 * @param  string|null  $extension
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($file = null, $data = null, $extension = null)
	{
		// Add the extension
		if (is_null($extension))
		{
			$extension       = (new Extension)->get();
			$this->extension = $extension;
		}
		else
		{
			$this->extension = $extension;
		}

		if (is_object($data) === true)
		{
			$data = get_object_vars($data);
		}
		elseif ($data AND ! is_array($data))
		{
			throw new InvalidArgumentException(__('view.dataObjectArray'));
		}
		
		if ($file !== null) 
		{
			$this->setFilename($file);
		}

		if ($data !== null)
		{
			// Add the values to the current data
			$this->data = $data + $this->data;
		}
	}

	/**
	 * Assigns a global variable by reference, similar to [$this->bind], 
	 * except that the variable will be accessible to all views.
	 *
	 * @example View::bindGlobal($key, $value);
	 *
	 * @param  string  $key    Variable name
	 * @param  mixed   $value  Referenced variable
	 *
	 * @return void
	 */
	public static function bindGlobal($key, & $value) 
	{
		static::$globalData[$key] =& $value;
	}

	/**
	 * Returns a new View object. If you do not define the "file" parameter, 
	 * you must call [View::setFilename].
	 *
	 * @example View::render($file, $data, $extension);
	 *
	 * @param  string       $file       View filename
	 * @param  array|null   $data       Array of values
	 * @param  string|null  $extension  String extension
	 * 
	 * @return void
	 */
	public static function render($file = null, array $data = null, $extension = null)
	{
		return new static($file, $data, $extension);
	}

	/**
	 * Sets a global variable, similar to [static::set], except that the
	 * variable will be accessible to all views.
	 *
	 * @example View::setGlobal($key, $value); 
	 *
	 * @param  string|array  $key    Variable name
	 * @param  mixed         $value  Value
	 *
	 * @return void
	 *
	 * @uses   instanceof \Traversable
	 */
	public static function setGlobal($key, $value = null) 
	{
		if (is_array($key) || $key instanceof Traversable)
		{
			foreach ($key as $name => $value)
			{
				static::$globalData = [$name => $value];
			}
		} 
		else 
		{
			static::$globalData = [$key => $value];
		}
	}

	/**
	 * Add a piece of data to the view.
	 *
	 * @example $view->assign($content, $data);
	 *
	 * @param  string|array  $key
	 * @param  mixed         $value
	 *
	 * @return $this
	 */
	public function assign($key, $value = null)
	{
		if (is_array($key)) 
		{
			$this->data = array_merge($this->data, $key);
		} 
		else 
		{
			$this->data = [$key => $value];
		}

		return $this;
	}

	/**
	 * Assigns a value by reference. The benefit of binding is that values can be altered
	 * without re-setting them. It is also possible to bind variables before they have values. 
	 * Assigned values will be available as a variable within the view file:
	 *     
	 * @example $view->bind('ref', $bar);
	 *
	 * @param  string  $key    Variable name
	 * @param  mixed   $value  Referenced variable
	 *
	 * @return $this
	 */
	public function bind($key, & $value) 
	{
		$this->data[$key] =& $value;

		return $this;
	}

	/**
	 * Captures the output that is generated when a view is included. 
	 * The view data will be extracted to make local variables.
	 *
	 * @example $output = $view->capture();
	 *
	 * @param  bool  $override  File override
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	protected function capture($override = false)
	{	
		$cleanRender = function($__file, array $__data)
		{
			// Import the view variables to local namespace
			extract($__data, EXTR_SKIP);

			if ( ! empty(static::$globalData))
			{
				// Import the global view variables to local namespace
				extract(static::$globalData, EXTR_SKIP | EXTR_REFS);
			}

			// Capture the view output
			ob_start();

			try
			{
				// Load the view within the current scope
				include $__file;

				// Get the captured output and close the buffer
				$output = ob_get_clean();

				if ($this->extend)
				{
					$view = $this->extend;

					$this->extend = '';

					$output = $this->make($view, $__data);
				}
			}
			catch (Exception $e)
			{
				// Delete the output buffer				
				if (@ob_end_clean() !== false)
				{
					// Re-throw the exception
					throw $e;
				}
			}

			return $output;
		};

		$result = $cleanRender($override ?: $this->filename, $this->data);
		
		return $result;
	}

	/**
	 * Extending a view.
	 *
	 * @param  string  $layout
	 *
	 * @return string
	 */
	public function extend($layout)
	{
		$this->extend = $layout;

		ob_start();
	}

	/**
	 * The view data will be extracted.
	 * 
	 * @param  array  $data
	 * 
	 * @return array|null
	 */
	public function getData(array $data = [])
	{
		return $this->data = is_null($data) ? $this->data : $data;
	}

	/**
	 * Searches for the given variable and returns its value.
	 * Local variables will be returned before global variables.
	 *
	 * @example  $value = $view->get('foo', 'bar');
	 *
	 * If the key is not given or null, the entire data array is returned.
	 *
	 * @param  string  $key      The variable name
	 * @param  mixed   $default  The default value to return 
	 *
	 * @return mixed
	 *
	 * @uses   \Syscode\Contracts\Core\Lenevor
	 *
	 * @throws \Syscode\Core\Exceptions\LenevorException
	 */
	public function &get($key, $default = null)
	{
		if (strpos($key, '.') === false)
		{
			if (array_key_exists($key, $this->data))
			{
				return $this->data[$key];
			}
			elseif (array_key_exists($key, static::$globalData))
			{
				return static::$globalData[$key];
			}
			else
			{
				throw new LenevorException(__('view.variableNotSet'));
			}
		}
		else
		{
			return Lenevor::value($default);
		}
	}

	/**
	 * Give sections the page view from the master page.
	 *
	 * @param  string  $blockName
	 *
	 * @return string
	 */
	public function give($blockName)
	{
		$stacks = array_key_exists($blockName, $this->blocks) ? $this->blocks[$blockName] : null;

		return $stacks ? $this->renderStacks($stacks) : '';
	}

	/**
	 * Include another view in a view.
	 *
	 * @param  string  $file
	 * @param  array   $data
	 *
	 * @return string
	 */
	public function insert($file, array $data = [])
	{
		$this->has($file);

		$path = $this->resolverPath($file);

		extract($data, EXTR_SKIP);
		
		include $path;
	}

	/**
	 * Global and local data are merged and extracted to create local variables within the view file.
	 * Renders the view object to a string.
	 *
	 * @example $output = $view->make();
	 *
	 * @param  string|null  $file  View filename
	 * @param  array|null   $data  Array of values
	 *
	 * @return string
	 *
	 * @throws \Syscode\View\Exceptions\ViewException
	 */
	public function make($file = null, $data = null) 
	{
		// Override the view filename if needed
		if (null !== $file)
		{
			$this->setFilename($file);
		}

		if (null !== $data)
		{
			$this->getData($data);
		}

		// And make sure we have one
		if (empty($this->filename))
		{
			throw new ViewException(__('view.rendering'));
		}

		// Combine local and global data and capture the output
		$output = $this->capture();

		return $output;
	}

	/**
	 * Check existance view file.
	 *
	 * @param  string  $file
	 *
	 * @return true
	 *
	 * @throws \Syscode\View\Exceptions\ViewException
	 */
	protected function has($file)
	{
		if ( ! $this->viewExists($file)) 
		{
			throw new ViewException(__('view.notFound', ['file' => $file]));
		}

		return true;
	}

	/**
	 * Check existance view file.
	 
	 * @param  string  $file
	 *
	 * @return bool
	 */
	public function viewExists($file)
	{
		$file = $this->resolverPath($file);

		return is_file($file);
	}

	/**
	 * Alias of @parent.
	 *
	 * @return string
	 */
	public function parent()
	{
		return '@parent';
	}

	/**
	 * Render block stacks.
	 *
	 * @param  array  $stacks
	 *
	 * @return mixed
	 */
	protected function renderStacks(array $stacks)
	{
		$current = array_pop($stacks);

		if (count($stacks)) 
		{
	   		return str_replace('@parent', $current, $this->renderStacks($stacks));
		} 
		else 
		{
	    	return $current;
		}
	}

	/**
	 * Resolve view directory.
	 *
	 * @param  string  $file
	 *
	 * @return string  The view directory
	 */
	public function resolverPath($file)
	{
		return Finder::search($file, 'views', $this->extension);
	}

	/**
	 * Starting section.
	 *
	 * @param  string  $blockName
	 *
	 * @return array
	 */
	public function section($blockName)
	{
		$this->sections[] = $blockName;

		ob_start();
	}

	/**
	 * Sets the view filename.
	 * 
	 * @example $output = $view->setFilename($file);
	 *
	 * @param  string  $file  View filename
	 *
	 * @return $this
	 *
	 * @throws \Syscode\View\Exceptions\ViewException
	 */
	public function setFilename($file)
	{
		if (($path = $this->resolverPath($file)) === false)
		{
			throw new ViewException(__('view.notFound', ['file' => $file]));
		}

		// Store the file path locally and extension
		if ( ! file_exists($file))
		{
			$this->filename = $path;
		}

		return $this;
	}

	/**
	 * Assigns a variable by name. Assigned values will be available as a
	 * variable within the view file:
	 *
	 * This value can be accessed as $var within the view
	 * @example $view->set(array('food' => 'bread', 'beverage' => 'water'));
	 *
	 * @param  string|array  $key    Variable name
	 * @param  mixed         $value  Value
	 *
	 * @return $this
	 *
	 * @uses   instanceof \Traversable
	 */
	public function set($key, $value = null) 
	{
		if (is_array($key) || $key instanceof Traversable)
		{
			foreach ($key as $name => $value) 
			{
				$this->set($name, $value);
			}
		}
		else
		{
			if (strpos($key, '.') === false)
			{
				$this->data = [$key => $value];
			}
			else
			{
				array_set($this->data, $key, $value);
			}
		}

		return $this;
	}

	/**
	 * Close and printing section.
	 *
	 * @param  string  $blockName
	 * 
	 * @return string
	 */
	public function show()
	{
		$blockName = $this->sections[count($this->sections)-1];

		$this->stop();

		echo $this->give($blockName);
	}
	
	/**
	 * Closing section.
	 *
	 * @param  array|string  $blockName
	 *
	 * @return mixed
	 */
	public function stop()	
	{
		$blockName = array_pop($this->sections);

		if ( ! array_key_exists($blockName, $this->blocks))
		{
			$this->blocks[$blockName] = [];
		}

		$this->blocks[$blockName][] = ob_get_clean();
	}	

	/**
	 * Magic method. Searches for the given variable and returns its value.
	 * Local variables will be returned before global variables.
	 *
	 * @example $value = $view->var;
	 * 
	 * @param  string  $key  Variable name
	 *
	 * @return mixed
	 *
	 * @throws \Syscode\LenevorException
	 */
	public function & __get($key) 
	{
		return $this->get($key);
	}

	/**
	 * Magic method. Determines if a variable is set.
	 *
	 * @example isset($view->foo);
	 *
	 * Variables are not considered to be set.
	 *
	 * @access public
	 * @param  string  $key  variable name
	 *
	 * @return boolean
	 */
	public function __isset($key) 
	{
		return (isset($this->data[$key]) || isset(static::$globalData[$key]));
	}

	/**
	 * Magic method. Calls [$this->set] with the same parameters.
	 *
	 * @example $view->var = 'something';
	 *
	 * @param  string  $key    Variable name
	 * @param  mixed   $value  Value
	 *
	 * @return void
	 */
	public function __set($key, $value) 
	{
		$this->set($key, $value);
	}

	/**
	 * Magic method. Returns the output of [static::render].
	 *
	 * @return string
	 *
	 * @uses   View->make()
	 * 
	 * @throws \Exception
	 */
	public function __toString() 
	{
		try
		{
			return $this->make();
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Magic method. Unsets a given variable.
	 *
	 * @example unset($view->var);
	 *
	 * @param  string  $key  Variable name
	 *
	 * @return void
	 */
	public function __unset($key) 
	{
		unset($this->data[$key], static::$globalData[$key]);
	}
}