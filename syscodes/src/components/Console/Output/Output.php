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

namespace Syscodes\Console\Output;

use Syscodes\Console\Formatter\OutputFormatter;
use Syscodes\Contracts\Console\Output as OutputInterface;
use Syscodes\Contracts\Console\OutputFormatter as OutputFormatterInterface;

/**
 * Allows the use of the formatter in the messages to be displayed 
 * in the output console.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
abstract class Output implements OutputInterface
{
	/**
	 * Gets formatter for output console.
	 * 
	 * @var \Syscodes\Contracts\Console\OutputFormatter $formatter
	 */
	protected $formatter;

	/**
	 * Constructor. Create a new Output instance.
	 * 
	 * @param  bool  $decorated  Whether to decorated messages
	 * @param  \Syscodes\Contracts\Console\OutputFormatter|null  $formatter  The output formatter instance
	 * 
	 * @return void
	 */
	public function __construct(bool $decorated = false, OutputFormatterInterface $formatter = null)
	{
		$this->formatter = $formatter ?? new OutputFormatter();
		$this->formatter->setDecorated($decorated);
	}

	/**
	 * Gets the decorated flag.
	 * 
	 * @return bool
	 */
	public function getDecorated(): string
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
	 * @return \Syscodes\Contracts\Console\OutputFormatter
	 */
	public function getFormatter(): OutputFormatterInterface
	{
		return $this->formatter;
	}

	/**
	 * Sets a output formatter instance.
	 * 
	 * @param  \Syscodes\Contracts\Console\OutputFormatter  $formatter;
	 * 
	 * @return void
	 */
	public function setFormatter(OutputFormatterInterface $formatter): void
	{
		$this->formatter = $formatter;
	}

	/**
	 * Enter a number of empty lines.
	 * 
	 * @param  int  $num  Number of lines to output
	 * 
	 * @return string
	 */
	public function newline(int $num = 1)
	{
		return str_repeat(\PHP_EOL, \max($num, 1));
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
		$this->write('', $newlines);
		$this->write(str_repeat('-', $width));
		$this->write('', $newlines);
	}

	/**
	 * Writes a message to the output and adds a newline at the end..
	 * 
	 * @param  string|iterable  $messages  The message as an iterable of strings or a single string
	 * @param  int  $options  A bitmask of options (0 is considered the same as self::OUTPUT_NORMAL)
	 * 
	 * @return string
	 */
	public function writeln($messages, int $options = self::OUTPUT_NORMAL)
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
	public function write($messages, bool $newline = false, int $options = self::OUTPUT_NORMAL)
    {
		if ( ! is_iterable($messages)) {
			$messages = [$messages];
		}
		
		$types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
		$type = $types & $options ?: self::OUTPUT_NORMAL;
		
		foreach ($messages as $message) {
			switch($type) {
				case OutputInterface::OUTPUT_NORMAL:
					$message = $this->formatter->format($message);
					break;
				case OutputInterface::OUTPUT_RAW:
					break;
				case OutputInterface::OUTPUT_PLAIN:
					$message = strip_tags($this->formatter->format($message));
					break;
			}
		}

		return $this->toWrite($message ?? '', $newline);
    }

	/**
     * Writes a message to the output.
     * 
     * @param  string  $message  The text to output
     * @param  bool  $newline  Add a newline command
     * 
     * @return mixed
     */
    abstract protected function toWrite(string $message, bool $newline);
}