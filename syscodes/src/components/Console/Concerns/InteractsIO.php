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

namespace Syscodes\Console\Concerns;

use Syscodes\Conttracts\Console\Output as OutputInterface;

/**
 * Trait InteractsIO.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait InteractsIO
{
    /**
     * The output interface implementation.
     * 
     * @var \Syscodes\Contracts\Console\Output $output
     */
    protected $output;

    /**
	 * Enter a number of empty lines.
	 * 
	 * @param  int  $num  Number of lines to output
	 * 
	 * @return string
	 */
	public function newline(int $num = 1)
	{
		$this->output->newline($num);
	}

	/**
     * Writes a string formatting for comment output.
     * 
     * @param  string  $message
     * 
     * @return void
     */
    public function comment($message)
    {

    }

    /**
     * Writes a string formatting for success output.
     * 
     * @param  string  $message
     * 
     * @return void
     */
    public function success($message)
    {

    }

    /**
     * Writes a string formatting for info output.
     * 
     * @param  string  $message
     * 
     * @return void
     */
    public function info($message)
    {

    }

    /**
     * Writes a string formatting for warning output.
     * 
     * @param  string  $message
     * 
     * @return void
     */
    public function warning($message)
    {

    }

    /**
     * Writes a string formatting for error output.
     * 
     * @param  string  $message
     * 
     * @return void
     */
    public function error($message)
    {

    }

    /**
     * Writes a string formatting for stantard output.
     * 
     * @param  string  $message
     * @param  string|null  $style
     * 
     * @return void
     */
    public function commandline($message, string $style = null)
    {

    }

	/**
	 * Outputs series of minus characters to CLI output, specified as a visual separator. 
	 * 
	 * @param  int  $newlines  Number of lines to output, defaults to 0
	 * @param  int  $width  Width of the line, default to 79
	 * 
	 * @return void
	 */
	public function hr(int $newlines = 0, $width = 79): void
	{
		$this->output->write('', $newlines);
		$this->output->write(str_repeat('-', $width));
		$this->output->write('', $newlines);
	}
}