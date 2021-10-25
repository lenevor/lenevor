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

namespace Syscodes\Bundles\ApplicationBundle\Autoloader;

/**
 * Autoload.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class Autoload extends AutoloadConfig
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
    public $prs4 = []; 
	
	/**
	 * Constructor. Create a new Autoload instance.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->classmap     = require $this->coreClassmap;
		$this->includeFiles = require $this->coreFiles;
		$this->psr4         = require $this->corePsr4;
	}
}