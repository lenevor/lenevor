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
 * @since       0.1.2
 */

namespace Syscodes\Config;

/**
 * Auto-loader Config
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class AutoloadConfig 
{
	/**
	 * Map of class names and locations.
	 *
	 * @var array $classmap
	 */
	public $classmap = [];

	/**
	 * If true, then auto-enabled will happen across all namespaces 
	 * loaded by Composer, as well as the namespaces configured locally.
	 * 
	 * @var bool $enabledInComposer
	 */
	public $enabledInComposer = true;

	/**
	 * Array of files for autoloading.
	 * 
	 * @var array $includeFiles
	 */
	public $includeFiles = [];

	/**
	 * Array of namespaces for autoloading.
	 *
	 * @var array $psr4
	 */
	public $psr4 = [];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		/**
		 * ---------------------------------------------------------------------
		 * Namespaces
		 * ---------------------------------------------------------------------
		 *
		 * This maps the locations of any namespaces in your application to 
		 * their location on the file system. These are used by the Autoloader 
		 * to locate files the first time they have been instantiated. 
		 * 
		 */
		$this->psr4 = require SYS_PATH.'src'.DIRECTORY_SEPARATOR.'register'.DIRECTORY_SEPARATOR.'autoloadPsr4.php';
		
		/**
		 * ---------------------------------------------------------------------
		 * Class Map
		 * ---------------------------------------------------------------------
		 *
		 * The class map provides a map of class names and their exact location 
		 * on the drive.
		 *  
		 */
		$this->classmap = require SYS_PATH.'src'.DIRECTORY_SEPARATOR.'register'.DIRECTORY_SEPARATOR.'autoloadClassmap.php';

		/**
		 * ---------------------------------------------------------------------
		 * Include Files
		 * ---------------------------------------------------------------------
		 * 
		 * This maps the locations of any files in your application to 
		 * their location on the file system.
		 * 
		 */
		$this->includeFiles = require SYS_PATH.'src'.DIRECTORY_SEPARATOR.'register'.DIRECTORY_SEPARATOR.'autoloadFiles.php';
	}

	/**
	 * Get the classes to filename map.
	 * 
	 * @param  array  $classmap
	 * 
	 * @return void
	 */
	public function addClassMap(array $classmap)
	{
		if (isset($this->classmap))
		{
			$this->classmap = array_merge($this->classmap, $classmap);
		}
		else
		{
			$this->classmap = $classmap;
		}

		return $this->classmap;
	}

	/**
	 * Get the filename map.
	 * 
	 * @param  array  $files
	 * 
	 * @return void
	 */
	public function addFiles(array $files)
	{
		if (isset($this->includeFiles))
		{
			$this->includeFiles = array_merge($this->includeFiles, $files);
		}
		else
		{
			$this->includeFiles = $files;
		}

		return $this->includeFiles;
	}

	/**
	 * Registers a set of PSR-4 directories for a given namespace.
	 * 
	 * @param  array  $psr4
	 * 
	 * @return void
	 */
	public function addPsr4(array $psr4)
	{
		if (isset($this->classmap))
		{
			$this->psr4 = array_merge($this->psr4, $psr4);
		}
		else
		{
			$this->psr4 = $psr4;
		}

		return $this->psr4;
	}
}