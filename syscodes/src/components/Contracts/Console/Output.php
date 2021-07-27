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

namespace Syscodes\Contracts\Console;

use Syscodes\Contracts\Console\OutputFormatter as OutputFormatterInterface;

/**
 * <Output> is the interface implemented by all Output classes.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface Output
{
	public const OUTPUT_NORMAL = 1;
	public const OUTPUT_RAW = 2;
	public const OUTPUT_PLAIN = 3;

	/**
	 * Gets the decorated flag.
	 * 
	 * @return bool
	 */
	public function getDecorated(): string;

	/**
	 * Sets the decorated flag.
	 * 
	 * @param  bool  $decorated  Whether to decorated messages
	 * 
	 * @return void
	 */
	public function setDecorated(bool $decorated): void;

	/**
	 * Returns a output formatter instance.
	 * 
	 * @return \Syscodes\Contracts\Console\OutputFormatter
	 */
	public function getFormatter(): OutputFormatterInterface;

	/**
	 * Sets a output formatter instance.
	 * 
	 * @param  \Syscodes\Contracts\Console\OutputFormatter  $formatter;
	 * 
	 * @return void
	 */
	public function setFormatter(OutputFormatterInterface $formatter): void;

	/**
	 * Enter a number of empty lines.
	 * 
	 * @param  int  $num  Number of lines to output
	 * 
	 * @return string
	 */
	public function newline(int $num = 1);

    /**
	 * Outputs a string to the cli.	If you send an array it will implode them
	 * with a line break.
	 * 
	 * @param  string|iterable  $messages  The text to output, or array of lines
	 * @param  bool  $newline  Add a newline command
	 * @param  int  $options  A bitmask of options (0 is considered the same as self::OUTPUT_NORMAL)
	 * 
	 * @return string
	 */
	public function write($messages, bool $newline = false, int $options = 0);

    /**
	 * Writes a message to the output and adds a newline at the end..
	 * 
	 * @param  string|iterable  $messages  The message as an iterable of strings or a single string
	 * @param  int  $options  A bitmask of options (0 is considered the same as self::OUTPUT_NORMAL)
	 * 
	 * @return string
	 */
	public function writeln($messages, int $options = 0);
}