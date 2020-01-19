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

namespace Syscode\View\Establishes;

/**
 * Trait ManagesLayouts.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait ManagesLayouts
{
	/**
	 * Empty default block to be extended by child templates.
	 *
	 * @var array $blocks
	 */
	protected $blocks = [];

	/**
	 * Extend a parent template.
	 *
	 * @var string $extend
	 */
	protected $extend;

	/**
	 * Started blocks.
	 *
	 * @var array $sections
	 */
	protected $sections = [];

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
	}

    /**
	 * Give sections the page view from the master page.
	 *
	 * @param  string  $name
	 *
	 * @return string
	 */
	public function give($name)
	{
		$stacks = array_key_exists($name, $this->blocks) ? $this->blocks[$name] : null;

		return $stacks ? $this->renderStacks($stacks) : '';
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
	 * Include another view in a view.
	 *
	 * @param  string  $file
	 * @param  array   $data
	 *
	 * @return string
	 */
	public function insert($file, array $data = [])
	{
		$path = $this->finder->find($file);

		extract($data, EXTR_SKIP);
		
		include $path;
    }
    
    /**
	 * Starting section.
	 *
	 * @param  string  $section
	 *
	 * @return array
	 */
	public function section($section)
	{
		$this->sections[] = $section;
		
		ob_start();
    }
    
    /**
	 * Close and printing section.
	 * 
	 * @return string
	 */
	public function show()
	{
		$section = $this->sections[count($this->sections)-1];

		$this->stop();

		echo $this->give($section);
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
		$sections = array_pop($this->sections);

		if ( ! array_key_exists($sections, $this->blocks))
		{
		 	$this->blocks[$sections] = [];
		}

		$this->blocks[$sections][] = ob_get_clean();
	}
}