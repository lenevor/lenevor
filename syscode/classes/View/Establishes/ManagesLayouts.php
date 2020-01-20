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

use InvalidArgumentException;
use Syscode\Contracts\View\View;

/**
 * Trait ManagesLayouts.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait ManagesLayouts
{
	/**
	 * Extend a parent template.
	 *
	 * @var string $extend
	 */
	protected $extend;

	/**
	 * Extend data.
	 * 
	 * @var array $extendData
	 */
	protected $extendData = [];

	/**
	 * Started blocks.
	 *
	 * @var array $sections
	 */
	protected $sections = [];
	
	/**
	 * The stack of in-progress sections.
	 * 
	 * @var array $sectionStack
     */
    protected $sectionStack = [];

	/**
	 * Extending a view.
	 *
	 * @param  string  $layout
	 *
	 * @return string
	 */
	public function extends($layout, $data = [])
	{
		$this->extend     = $layout;
		$this->extendData = $data;
	}
    
    /**
	 * Starting section.
	 *
	 * @param  string  $section
	 * @param  string|null  $content  (null by default)
	 *
	 * @return array
	 */
	public function beginSection($section, $content = null)
	{
		if (null === $content)
		{
			$this->sectionStack[] = $section;
			
			ob_start();
			
		}
		else
		{
			$this->extendSection($section, $content instanceof View ? $content : e($content));
		}
	}

	/**
	 * Append content to a given section.
	 * 
	 * @param  string  $section
	 * @param  string  $content
	 * 
	 * @return void
	 */
	protected function extendSection($section, $content)
	{
		if (isset($this->sections[$section]))
		{
			$content = str_replace('@@parent', $content, $this->sections[$section]);
		}

		$this->sections[$section] = $content;
	}
    
    /**
	 * Close and printing section.
	 * 
	 * @return string
	 */
	public function showSection()
	{
		return $this->giveContent($this->stopSection());
	}
	
	/**
	 * Closing section.
	 * 
	 * @param  bool  $overwrite  (false by default)
	 *
	 * @return mixed
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function stopSection($overwrite = false)	
	{
		if (empty($this->sectionStack))
		{
			throw new InvalidArgumentException('You cannot finish a section without first starting with one.');
		}

		$last = array_pop($this->sectionStack);

		if ($overwrite)
		{
			$this->sections[$last] = ob_get_clean();
		}
		else
		{
			$this->extendSection($last, ob_get_clean());
		}

		return $last;
	}

	/**
	 * Stop injecting content into a section and append it.
	 * 
	 * @return string
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function appendSection()
	{
		if (empty($this->sectionStack))
		{
			throw new InvalidArgumentException('You cannot finish a section without first starting with one.');
		}

		$last = array_pop($this->sectionStack);

		if (isset($this->sections[$last]))
		{
			$this->sections[$last] .= ob_get_clean();
		}
		else
		{
			$this->sections[$last] = ob_get_clean();
		}

		return $last;
	}

	/**
	 * Check if section exists.
	 * 
	 * @param  string  $name
	 * 
	 * @return bool
	 */
	public function hasSection($name)
	{
		return array_key_exists($name, $this->sections);
	}

	/**
	 * Give sections the page view from the master page.
	 *
	 * @param  string  $name
	 *
	 * @return string
	 */
	public function giveContent($name, $default = '')
	{
		$sectionContent = $default instanceof View ? $default : e($default);

		if (isset($this->sections[$name]))
		{
			$sectionContent = $this->sections[$name];
		}

		return str_replace('@@parent', '', $sectionContent);
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
	 * Flush all of the section contents.
	 * 
	 * @return void
	 */
	public function flushSections()
	{
		$this->sections     = [];
		$this->sectionStack = [];
	}
}