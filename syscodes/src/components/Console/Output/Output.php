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

namespace Syscodes\Components\Console\Output;

use Syscodes\Components\Console\Concerns\InteractsIO;
use Syscodes\Components\Console\Formatter\OutputFormatter;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Output\OutputFormatter as OutputFormatterInterface;

/**
 * Allows the use of the formatter in the messages to be displayed 
 * in the output console.
 */
abstract class Output implements OutputInterface
{
	use InteractsIO;
	
	/**
	 * Gets formatter for output console.
	 * 
	 * @var \Syscodes\Components\Contracts\Console\Output\OutputFormatter $formatter
	 */
	protected $formatter;
	
	/**
	 * Gets the verbosity level.
	 * 
	 * @var int $verbosity
	 */
	protected $verbosity;
	
	/**
	 * Constructor. Create a new Output instance.
	 * 
	 * @param  int|null  $verbosity  The verbosity level
	 * @param  bool  $decorated  Whether to decorated messages
	 * @param  \Syscodes\Components\Contracts\Console\Output\OutputFormatter|null  $formatter  The output formatter instance
	 * 
	 * @return void
	 */
	public function __construct(?int $verbosity = OutputInterface::VERBOSITY_NORMAL, bool $decorated = false, ?OutputFormatterInterface $formatter = null)
	{
		$this->verbosity = $verbosity ?? OutputInterface::VERBOSITY_NORMAL;
		$this->formatter = $formatter ?? new OutputFormatter();
		
		$this->formatter->setDecorated($decorated);
	}
	
	/**
	 * Gets the decorated flag.
	 * 
	 * @return bool
	 */
	public function getDecorated(): bool
	{
		return $this->formatter->getDecorated();
	}
	
	/**
	 * Sets the decorated flag.
	 * 
	 * @param  bool  $decorated  Whether to decorated messages
	 * 
	 * @return void
	 */
	public function setDecorated(bool $decorated): void
	{
		$this->formatter->setDecorated($decorated);
	}
	
	/**
	 * Returns a output formatter instance.
	 * 
	 * @return \Syscodes\Components\Contracts\Console\Output\OutputFormatter
	 */
	public function getFormatter(): OutputFormatterInterface
	{
		return $this->formatter;
	}
	
	/**
	 * Sets a output formatter instance.
	 * 
	 * @param  \Syscodes\Components\Contracts\Console\Output\OutputFormatter  $formatter;
	 * 
	 * @return void
	 */
	public function setFormatter(OutputFormatterInterface $formatter): void
	{
		$this->formatter = $formatter;
	}
	
	/**
	 * Gets the current verbosity of the output.
	 * 
	 * @return int
	 */
	public function getVerbosity(): int
	{
		return $this->verbosity;
	}
	
	/**
	 * Sets the verbosity of the output.
	 * 
	 * @param  int  $level
	 * 
	 * @return void
	 */
	public function setVerbosity(int $level): void
	{
		$this->verbosity = $level;
	}
	
	/**
	 * Returns whether verbosity is quiet (-q).
	 * 
	 * @return bool
	 */
	public function isQuiet(): bool
	{
		return OutputInterface::VERBOSITY_QUIET === $this->verbosity;
	}
	
	/**
	 * Returns whether verbosity is verbose (-v).
	 * 
	 * @return bool
	 */
	public function isVerbose(): bool
	{
		return OutputInterface::VERBOSITY_VERBOSE <= $this->verbosity;
	}
	
	/**
	 * Returns whether verbosity is very verbose (-vv).
	 * 
	 * @return bool
	 */
	public function isVeryVerbose(): bool
	{
		return OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
	}
	
	/**
	 * Returns whether verbosity is debug (-vvv).
	 * 
	 * @return bool
	 */
	public function isDebug(): bool
	{
		return OutputInterface::VERBOSITY_QUIET <= $this->verbosity;
	}
	
	/**
	 * Enter a number of empty lines.
	 * 
	 * @param  int  $num  Number of lines to output
	 * 
	 * @return string
	 */
	public function newLine(int $num = 1)
	{
		return $this->write(str_repeat(\PHP_EOL, max($num, 1)));
	}
	
	/**
	 * Writes a message to the output and adds a newline at the end.
	 * 
	 * @param  string|iterable  $messages  The message as an iterable of strings or a single string
	 * @param  int  $options  A bitmask of options (0 is considered the same as self::OUTPUT_NORMAL)
	 * 
	 * @return string
	 */
	public function writeln($messages, int $options = OutputInterface::OUTPUT_NORMAL)
	{
		return $this->write($messages, true, $options);
	}
	
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
	public function write($messages, bool $newline = false, int $options = OutputInterface::OUTPUT_NORMAL)
	{
		if ( ! is_iterable($messages)) {
			$messages = [$messages];
		}
		
		$types = OutputInterface::OUTPUT_NORMAL | OutputInterface::OUTPUT_RAW | OutputInterface::OUTPUT_PLAIN;
		$type  = $types & $options ?: OutputInterface::OUTPUT_NORMAL;
		
		$verbosities = OutputInterface::VERBOSITY_QUIET | OutputInterface::VERBOSITY_NORMAL | OutputInterface::VERBOSITY_VERBOSE | OutputInterface::VERBOSITY_VERY_VERBOSE | OutputInterface::VERBOSITY_DEBUG;
		
		$verbosity = $verbosities & $options ?: OutputInterface::VERBOSITY_NORMAL;
		
		if ($verbosity > $this->getVerbosity()) {
			return;
		}
		
		foreach ($messages as $message) {
			match ($type) {
				OutputInterface::OUTPUT_NORMAL => $message = $this->formatter->format($message),
				OutputInterface::OUTPUT_RAW,
				OutputInterface::OUTPUT_PLAIN => $message = strip_tags($this->formatter->format($message)),
			};
		}
		
		$this->toWrite($message ?? '', $newline);
	}
	
	/**
	 * Writes a message to the output.
	 * 
	 * @param  string  $message  The text to output
	 * @param  bool  $newline  Add a newline command
	 * 
	 * @return void
	 */
	abstract protected function toWrite(string $message, bool $newline): void;
}