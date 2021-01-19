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

namespace Syscodes\Console;

use Syscodes\Version;
use Syscodes\Contracts\Core\Lenevor;
use Syscodes\Support\Facades\Request;
use Syscodes\Contracts\Console\Application as ApplicationContracts;

/**
 * Console application.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Application implements ApplicationContracts
{
	/**
	 * Get the Lenevor class instance.
	 * 
	 * @var \Syscodes\Contracts\Core\Lenevor $core
	 */
	protected $core;

	/**
	 * Console constructor. Initialize the console of Lenevor.
	 *
	 * @param  \Syscodes\Contracts\Core\Lenevor  $core
	 * 
	 * @return void
	 */
	public function __construct(Lenevor $core)
	{		
		// Initialize the Cli
		if ($core->initCli())
		{
			Cli::initialize($core);
		}

		$this->core = $core;
	}

	/**
	 * Runs the current command discovered on the CLI.
	 *
	 * @return void
	 */
	public function run()
	{
		Cli::getURI();

		
	}

	/**
	 * Displays basic information about the Console.
	 *
	 * @return void
	 *
	 * @uses   Version::PRODUCT
	 * @uses   Version::RELEASE 
	 */
	public function showHeader()
	{		
		Cli::write(Version::PRODUCT.' '
			.Cli::color(Version::RELEASE, 'cyan').' | '
			.'Server Time: '.Cli::color(date('Y/m/d H:i:sa'), 'light_yellow').' | '
			.cli::color('['.PHP_OS.']', 'light_purple')
		);

		Cli::newLine(1);
	}
}