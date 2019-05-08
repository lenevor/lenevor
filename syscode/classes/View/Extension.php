<?php

namespace Syscode\View;

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
class Extension
{
	/**
	 * The file extension.
	 *
	 * @var string|null $extension
	 */
	protected $extension = ['plaze.php', 'php'];
	
	/**
	 * The type to engine bindings.
	 *
	 * @var array $extensions
	 */
	protected $fileExtensions = ['plaze.php' => 'plaze', 'php' => 'php'];

	/**
	 * Constructor. Create new FileExtension instance.
	 *
	 * @param  string  $extension  Use 'plaze' or 'php'
	 *
	 * @return mixed
	 */
	public function __construct($extension = 'plaze')
	{
		$this->set($extension);
	}

	/**
	 * Set the template file extension.
	 *
	 * @param  string  $extension
	 *
	 * @return string
	 */
	public function set($extension)
	{
		foreach ($this->fileExtensions as $key => $value) 
		{
			if ($value === $extension)
			{
				$this->extension = $key;
			}
		}	

		return $this;
	}

	/**
	 * Get the template file extension.
	 * 
	 * @return string
	 */
	public function get()
	{
		return $this->extension;
	}
}