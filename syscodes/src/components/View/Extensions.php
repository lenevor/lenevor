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

namespace Syscodes\Components\View;

/**
 * The Extension trait.
 */
trait Extensions
{
	/**
	 * The file extension.
	 *
	 * @var array $extension
	 */
	protected $extension = [
		'plaze.php', 
		'php',
		'html',
	];
	
	/**
	 * The type to engine bindings.
	 *
	 * @var array $extensions
	 */
	protected $extensions = [
		'plaze.php' => 'plaze', 
		'php' => 'php',
		'html' => 'file',
	];

	/**
	 * Get the template file extension.
	 * 
	 * @return array
	 */
	public function getExtensions(): array
	{
		return $this->extension;
	}

	/**
	 * Get type to engine bindings.
	 * 
	 * @return array
	 */
	public function getKeysToExtensions(): array
	{
		return array_keys($this->extensions);
	}
}