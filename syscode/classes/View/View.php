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

namespace Syscode\View;

use Exception;
use Traversable;
use InvalidArgumentException;
use Syscode\Contracts\Core\Http\Lenevor;
use Syscode\Contracts\View\View as ViewContract;
use Syscode\Core\Http\Exceptions\LenevorException;

/**
 * This class control the views.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class View implements ViewContract
{
	use Establishes\ManagesLayouts;
	
	/**
	 * Array of global variables.
	 * 
	 * @var array $globalData
	 */
	protected static $globalData = [];

	/**
	 * Array of local variables.
	 *
	 * @var array $data
	 */
	protected $data = [];

	/**
	 * The view finder implementation.
	 * 
	 * @var \Syscode\View\FileViewFinder $finder
	 */
	protected $finder;

	/**
	 * Get the view.
	 *
	 * @var string $view
	 */
	protected $view;

	/**
	 * Constructor: Call the file and your data.
	 *
	 * 
	 * @param  \Syscode\View\FileViewFinder  $finder
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(FileViewFinder $finder, $view = null, $data = [])
	{
		$this->finder = $finder;
		$this->view   = $view;
		$this->data   = $this->getData($data);
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
	 * you must call [View::setview].
	 *
	 * @example View::render($file, $data);
	 *
	 * @param  string       $file       View view
	 * @param  array        $data       Array of values
	 * 
	 * @return void
	 */
	public static function render($file = null, array $data = [])
	{
		return self::make($file, $data);
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
			$this->data[$key] = $value;
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
	public function capture($override = false)
	{	
		$cleanRender = function($__file, array $__data)
		{
			// Capture the view output
			ob_start();

			// Import the view variables to local namespace
			extract($__data, EXTR_SKIP);

			if ( ! empty(static::$globalData))
			{
				// Import the global view variables to local namespace
				extract(static::$globalData, EXTR_SKIP | EXTR_REFS);
			}

			try
			{
				// Load the view within the current scope
				include $__file;

				// Get the captured output and close the buffer
				$output = ob_get_clean();

				if ($this->extend)
				{
					$extendView = $this->extend;

					$this->extend = null;

					$output = $this->make($extendView, array_merge($__data, $this->extendData));
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

		$result = $cleanRender($override ?: $this->view, $this->data);
		
		return $result;
	}

	/**
	 * The view data will be extracted.
	 * 
	 * @param  array  $data
	 * 
	 * @return array
	 */
	public function getData(array $data = [])
	{
		return array_merge($data, $this->data);
	}

	/**
	 * Global and local data are merged and extracted to create local variables within the view file.
	 * Renders the view object to a string.
	 *
	 * @example $output = $view->make();
	 *
	 * @param  string  $file  View view  (null by default)
	 * @param  array   $data  Array of values
	 *
	 * @return string
	 *
	 * @throws \Syscode\View\Exceptions\ViewException
	 */
	public function make($file = null, $data = []) 
	{
		try
		{
			// Override the view view if needed
			$this->view = $this->finder->find($file);
			$this->data = $this->getData($data);
				
			// Combine local and global data and capture the output
			return $this->capture();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Check existance view file.
	 * 
	 * @param  string  $file
	 *
	 * @return bool
	 */
	public function viewExists($file)
	{
		try 
		{
			$this->finder->find($file);
		}
		catch(InvalidArgumentException $e)
		{
			return false;
		}

		return true;
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
		return $this->make();
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