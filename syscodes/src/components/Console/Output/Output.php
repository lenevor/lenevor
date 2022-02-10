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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Output;

use Syscodes\Components\Console\Concerns\InteractsIO;
use Syscodes\Components\Console\Formatter\OutputFormatter;
use Syscodes\Components\Contracts\Console\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\OutputFormatter as OutputFormatterInterface;

/**
 * Allows the use of the formatter in the messages to be displayed 
 * in the output console.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
abstract class Output implements OutputInterface
{
	use InteractsIO;
	
	/**
	 * Gets formatter for output console.
	 * 
	 * @var \Syscodes\Components\Contracts\Console\OutputFormatter $formatter
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
	 * @param  \Syscodes\Components\Contracts\Console\OutputFormatter|null  $formatter  The output formatter instance
	 * 
	 * @return void
	 */
	public function __construct(?int $verbosity = OutputInterface::VERBOSITY_NORMAL, bool $decorated = false, OutputFormatterInterface $formatter = null)
	{
		$this->verbosity = $verbosity ?? OutputInterface::VERBOSITY_NORMAL;
		$this->formatter = $formatter ?? new OutputFormatter();
		
		$this->formatter->setDecorated($decorated);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getDecorated(): string
	{
		return $this->formatter->getDecorated();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function setDecorated(bool $decorated): void
	{
		$this->formatter->setDecorated($decorated);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getFormatter(): OutputFormatterInterface
	{
		return $this->formatter;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function setFormatter(OutputFormatterInterface $formatter): void
	{
		$this->formatter = $formatter;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getVerbosity(): int
	{
		return $this->verbosity;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function setVerbosity(int $level): void
	{
		$this->verbosity = $level;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function isQuiet(): bool
	{
		return OutputInterface::VERBOSITY_QUIET === $this->verbosity;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function isVerbose(): bool
	{
		return OutputInterface::VERBOSITY_VERBOSE <= $this->verbosity;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function isVeryVerbose(): bool
	{
		return OutputInterface::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
	}
	
	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function writeln($messages, int $options = OutputInterface::OUTPUT_NORMAL)
	{
		return $this->write($messages, true, $options);
	}
	
	/**
	 * {@inheritdoc}
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