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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\View\Concerns;

use InvalidArgumentException;
use Syscodes\Components\Contracts\View\View;

/**
 * Trait ManagesLayouts.
 */
trait ManagesLayouts
{	
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
	 * Starting section.
	 * 
	 * @param  string  $section
	 * @param  string|null  $content  
	 * 
	 * @return array
	 */
	public function startSection($section, $content = null)
	{
		if (null === $content) {
			if (ob_start()) {
				$this->sectionStack[] = $section;
			}
		} else {
			$this->extendSection($section, $content instanceof View ? $content : e($content));
		}
	}

	/**
	 * Inject inline content into a section.
	 * 
	 * @param  string  $section
	 * @param  string  $content
	 * 
	 * @return void
	 */
	public function injectSection($section, $content)
	{
		$this->beginSection($section, $content);
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
		if (isset($this->sections[$section])) {
			$content = str_replace(static::parent(), $content, $this->sections[$section]);
		}
		
		$this->sections[$section] = $content;
	}
	
	/**
	 * Close and printing section.
	 * 
	 * @return string
	 */
	public function showSection(): string
	{
		if (empty($this->sectionStack)) {
			return '';
		}
		
		return $this->giveContent($this->stopSection());
	}
	
	/**
	 * Give sections the page view from the master page.
	 * 
	 * @param  string  $name
	 * 
	 * @return string
	 */
	public function giveContent($name, $default = ''): string
	{
		$sectionContent = $default instanceof View ? $default : e($default);
		
		if (isset($this->sections[$name])) {
			$sectionContent = $this->sections[$name];
		}
		
		return str_replace(static::parent(), '', $sectionContent);
	}
	
	/**
	 * Closing section.
	 * 
	 * @param  bool  $overwrite  
	 * 
	 * @return mixed
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function stopSection($overwrite = false)
	{
		if (empty($this->sectionStack)) {
			throw new InvalidArgumentException('You cannot finish a section without first starting with one.');
		}
		
		$last = array_pop($this->sectionStack);
		
		if ($overwrite) { 
			$this->sections[$last] = ob_get_clean();
		} else {
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
		if (empty($this->sectionStack)) {
			throw new InvalidArgumentException('You cannot finish a section without first starting with one.');
		}
		
		$last = array_pop($this->sectionStack);
		
		if (isset($this->sections[$last])) {
			$this->sections[$last] .= ob_get_clean();
		} else {
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
	public function hasSection($name): bool
	{
		return array_key_exists($name, $this->sections);
	}
	
	/**
	 * Get the entire array of sections.
	 * 
	 * @return array
	 */
	public function getSections(): array
	{
		return $this->sections;
	}
	
	/**
	 * Replace the @parent directive to a placeholder.
	 * 
	 * @return string
	 */
	public static function parent(): string
	{
		return '@parent';
	}
	
	/**
	 * Flush all of the section contents.
	 * 
	 * @return void
	 */
	public function flushSections(): void
	{
		$this->sections     = [];
		$this->sectionStack = [];
	}
}