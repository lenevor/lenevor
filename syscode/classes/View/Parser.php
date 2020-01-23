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
 * @since       0.6.0
 */

namespace Syscode\View;

use InvalidArgumentException;
use Syscode\Contracts\View\Parser as ParserContract;

/**
 * This class allows parser of a view.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Parser implements ParserContract
{	
	use Establishes\ManagesLayouts;

	/**
	 * Constructor: Create a new View class instance.
	 * 
	 * @param  \Syscode\View\FileViewFinder  $finder
	 *
	 * @return void
	 */
	public function __construct(FileViewFinder $finder)
	{
		$this->finder = $finder;
    }

    /**
	 * Check existance view file.
	 * 
	 * @param  string  $view
	 *
	 * @return bool
	 */
	public function viewExists($view)
	{
		try 
		{
			$this->finder->find($view);
		}
		catch(InvalidArgumentException $e)
		{
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
	 * @param  string  $file  View filename  (null by default)
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
			// Override the view filename if needed
			$file = $this->finder->find($file);
				
			// Loader class instance.
			return $this->viewInstance($file, $data);
		}
		catch(Exception $e)
		{
			throw $e;
		}
    }
    
    /**
     * Create a new view instance from the given arguments.
     * 
     * @param  string  $file  View filename  (null by default)
	 * @param  array   $data  Array of values
     * 
     * @return \Syscode\Contracts\View\View
     */
    protected function viewInstance($view, $data)
    {
        return (new View($this, $view, $data))->render();
    }
}