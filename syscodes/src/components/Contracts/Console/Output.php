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

/**
 * <Output> is the interface implemented by all Output classes.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface Output
{
    /**
	 * Outputs a string to the cli.	If you send an array it will implode them
	 * with a line break.
	 * 
	 * @param  string|iterable  $messages  The text to output, or array of lines
	 * @param  bool  $newline  Add a newline command
	 * 
	 * @return string
	 */
	public function write($messages, bool $newline = false);

    /**
	 * Writes a message to the output and adds a newline at the end..
	 * 
	 * @param  string|iterable  $messages  The message as an iterable of strings or a single string
	 * 
	 * @return string
	 */
	public function writeln($messages);
}