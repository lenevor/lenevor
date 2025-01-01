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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Console\Output;

use Syscodes\Components\Contracts\Console\Output\OutputFormatter as OutputFormatterInterface;

/**
 * <Output> is the interface implemented by all Output classes.
 */
interface Output
{
	// Output formatter
	public const OUTPUT_NORMAL = 1;
	public const OUTPUT_RAW = 2;
	public const OUTPUT_PLAIN = 3;

	// Output verbose
	public const VERBOSITY_QUIET = 16;
	public const VERBOSITY_NORMAL = 32;
	public const VERBOSITY_VERBOSE = 64;
	public const VERBOSITY_VERY_VERBOSE = 128;
	public const VERBOSITY_DEBUG = 256;

	/**
	 * Gets the decorated flag.
	 * 
	 * @return bool
	 */
	public function getDecorated(): bool;

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
	 * @return \Syscodes\Components\Contracts\Console\Output\OutputFormatter
	 */
	public function getFormatter();

	/**
	 * Sets a output formatter instance.
	 * 
	 * @param  \Syscodes\Components\Contracts\Console\Output\OutputFormatter  $formatter;
	 * 
	 * @return void
	 */
	public function setFormatter(OutputFormatterInterface $formatter): void;

	/**
	 * Gets the current verbosity of the output.
	 * 
	 * @return int
	 */
	public function getVerbosity(): int;

	/**
	 * Sets the verbosity of the output.
	 * 
	 * @param  int  $level
	 * 
	 * @return void
	 */
	public function setVerbosity(int $level): void;

	/**
	 * Returns whether verbosity is quiet (-q).
	 * 
	 * @return bool
	 */
	public function isQuiet(): bool;

	/**
	 * Returns whether verbosity is verbose (-v).
	 * 
	 * @return bool
	 */
	public function isVerbose(): bool;

	/**
	 * Returns whether verbosity is very verbose (-vv).
	 * 
	 * @return bool
	 */
	public function isVeryVerbose(): bool;

	/**
	 * Returns whether verbosity is debug (-vvv).
	 * 
	 * @return bool
	 */
	public function isDebug(): bool;

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
	 * Writes a message to the output and adds a newline at the end.
	 * 
	 * @param  string|iterable  $messages  The message as an iterable of strings or a single string
	 * @param  int  $options  A bitmask of options (0 is considered the same as self::OUTPUT_NORMAL)
	 * 
	 * @return string
	 */
	public function writeln($messages, int $options = 0);

	/**
	 * Writes a string formatting for stantard output.
	 * 
	 * @param  string  $message
	 * @param  string|null  $style
	 * 
	 * @return void
	 */
	public function commandline(string $message, ?string $style = null);
}