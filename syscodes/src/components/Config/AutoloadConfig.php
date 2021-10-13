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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Config;

/**
 * Auto-loader Config
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class AutoloadConfig 
{
	/**
	 * ---------------------------------------------------------------------
	 * Class Map
	 * ---------------------------------------------------------------------
	 *
	 * The class map provides a map of class names and their exact location 
	 * on the drive.
	 *  
	 * @var array $coreClassmap
	 */
	protected $coreClassmap = [
		SYS_PATH.'src'.DIRECTORY_SEPARATOR.'register'.DIRECTORY_SEPARATOR.'autoloadClassmap.php',
	];

	/**
	 * ---------------------------------------------------------------------
	 * Include Files
	 * ---------------------------------------------------------------------
	 * 
	 * This maps the locations of any files in your application to 
	 * their location on the file system.
	 * 
	 * @var array $coreFiles
	 */
	protected $coreFiles = [
		SYS_PATH.'src'.DIRECTORY_SEPARATOR.'register'.DIRECTORY_SEPARATOR.'autoloadFiles.php'
	];

	/**
	 * ---------------------------------------------------------------------
	 * Namespaces
	 * ---------------------------------------------------------------------
	 *
	 * This maps the locations of any namespaces in your application to 
	 * their location on the file system. These are used by the Autoloader 
	 * to locate files the first time they have been instantiated. 
	 * 
	 * @var array $corePsr4
	 */
	protected $corePsr4 = [
		SYS_PATH.'src'.DIRECTORY_SEPARATOR.'register'.DIRECTORY_SEPARATOR.'autoloadPsr4.php',
	];

	/**
	 * Constructor. Create a new autoload config instance.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->classmap     = array_merge(require $this->coreClassmap[0], $this->classmap);
		$this->includeFiles = array_merge(require $this->coreFiles[0], $this->includeFiles);
		$this->psr4         = array_merge(require $this->corePsr4[0], $this->prs4);
	}
}