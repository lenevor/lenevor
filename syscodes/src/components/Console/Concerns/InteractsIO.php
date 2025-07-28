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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Concerns;

/**
 * Trait InteractsIO.
 */
trait InteractsIO
{
	/**
	 * The output interface implementation.
	 * 
	 * @var \Syscodes\Components\Contracts\Console\Output $output
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
		$this->newLine($num);
	}
	
	/**
	 * Writes a string formatting for comment output.
	 * 
	 * @param  string  $message
	 * 
	 * @return void
	 */
	public function comment(string $message)
	{
		$this->commandline($message, 'comment');
	}

	/**
	 * Writes a string formatting for note output.
	 * 
	 * @param  string  $message
	 * 
	 * @return void
	 */
	public function note(string $message)
	{
		$this->commandline($message, 'note');
	}
	
	/**
	 * Writes a string formatting for success output.
	 * 
	 * @param  string  $message
	 * 
	 * @return void
	 */
	public function success(string $message)
	{
		$this->commandline($message, 'success');
	}
	
	/**
	 * Writes a string formatting for info output.
	 * 
	 * @param  string  $message
	 * 
	 * @return void
	 */
	public function info(string $message)
	{
		$this->commandline($message, 'info');
	}

	/**
	 * Writes a string formatting for question output.
	 * 
	 * @param  string  $message
	 * 
	 * @return void
	 */
	public function question(string $message)
	{
		$this->commandline($message, 'question');
	}
	
	/**
	 * Writes a string formatting for warning output.
	 * 
	 * @param  string  $message
	 * 
	 * @return void
	 */
	public function warning(string $message)
	{
		$this->commandline($message, 'warning');
	}
	
	/**
	 * Writes a string formatting for error output.
	 * 
	 * @param  string  $message
	 * 
	 * @return void
	 */
	public function error(string $message)
	{
		$this->commandline($message, 'error');
	}
	
	/**
	 * Writes a string formatting for stantard output.
	 * 
	 * @param  string  $message
	 * @param  string|null  $style
	 * 
	 * @return void
	 */
	public function commandline(string $message, ?string $style = null)
	{
		$styled = $style ? "<$style>$message</>" : $message;
		
		return $this->writeln($styled);
	}
	
	/**
	 * Outputs series of minus characters to CLI output, specified as a visual separator.
	 * 
	 * @param  int  $newlines  Number of lines to output, defaults to 0
	 * @param  int  $width  Width of the line, default to 79
	 * 
	 * @return string
	 */
	public function hr(int $newlines = 0, $width = 79) 
	{
		$this->writeln('', $newlines);
		$this->writeln(str_repeat('-', $width));
		$this->write('', $newlines);
	}
}