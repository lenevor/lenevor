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
use Syscodes\Support\Facades\Request;
use Syscodes\Contracts\Container\Container;
use Syscodes\Contracts\Console\Application as ApplicationContracts;

/**
 * Console application.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Application implements ApplicationContracts
{
	/**
	 * The Lenevor application instance..
	 * 
	 * @var \Syscodes\Contracts\Container|Container $lenevor
	 */
	protected $lenevor;

	/**
	 * Console constructor. Initialize the console of Lenevor.
	 *
	 * @param  \Syscodes\Contracts\Core\Lenevor  $core
	 * 
	 * @return void
	 */
	public function __construct(Container $lenevor)
	{		
		// Initialize the Cli
		if (isCli()) {
			Cli::initialize();
		}

		$this->lenevor = $lenevor;
	}

	/**
	 * Runs the current command discovered on the CLI.
	 *
	 * @return void
	 */
	public function run()
	{
		$path = Cli::getURI();

				
	}

	/**
	 * Displays basic information about the Console.
	 *
	 * @return $this 
	 */
	public function showHeader()
	{		
		Cli::write(Version::PRODUCT.' '
			.Cli::color(Version::RELEASE, 'cyan').' | '
			.'Server Time: '.Cli::color(date('Y/m/d H:i:sa'), 'light_yellow').' | '
			.cli::color('['.PHP_OS.']', 'light_purple')
		);

		Cli::newLine(1);

		return $this;
	}
}