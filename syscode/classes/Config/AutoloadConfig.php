<?php 

namespace Syscode\Config;

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
class AutoloadConfig 
{
	/**
	 * Map of class names and locations.
	 *
	 * @var array $classmap
	 */
	public $classmap = [];

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
		 * Class Map
		 * ---------------------------------------------------------------------
		 *
		 * The class map provides a map of class names and their exact location 
		 * on the drive.
		 *  
		 */
		$sys = require SYS_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadClassmap.php';
		$bst = require BST_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadClassmap.php';

		$this->classmap = array_merge($bst, $sys);

		/**
		 * ---------------------------------------------------------------------
		 * Include Files
		 * ---------------------------------------------------------------------
		 * 
		 * This maps the locations of any files in your application to 
		 * their location on the file system.
		 * 
		 */
		$sys = require SYS_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadFiles.php';
		$bst = require BST_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadFiles.php';

		$this->includeFiles = array_merge($bst, $sys);

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
		$sys = require SYS_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadPsr4.php';
		$bst = require BST_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadPsr4.php';

		$this->psr4 = array_merge($bst, $sys);
	}
}