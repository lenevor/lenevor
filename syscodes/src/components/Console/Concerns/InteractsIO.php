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

use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Trait InteractsIO.
 */
trait InteractsIO
{
	/**
     * The input interface implementation.
     *
     * @var \Syscodes\Components\Contracts\Console\Input\Input $input
     */
    protected $input;

	/**
	 * The output interface implementation.
	 * 
	 * @var \Syscodes\Components\Contracts\Console\Output $output
	 */
	protected $output;
	
	/**
	 * The default verbosity of output commands.
	 * 
	 * @var int $verbosity
	 */
	protected $verbosity = OutputInterface::VERBOSITY_NORMAL;
	
	/**
	 * The mapping between human readable verbosity levels and OutputInterface.
	 * 
	 * @var array $verbosityMap
	 */
	protected $verbosityMap = [
		'v' => OutputInterface::VERBOSITY_VERBOSE,
		'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
		'vvv' => OutputInterface::VERBOSITY_DEBUG,
		'quiet' => OutputInterface::VERBOSITY_QUIET,
		'normal' => OutputInterface::VERBOSITY_NORMAL,
	];
	
	/**
	 * Determine if the given argument is present.
	 * 
	 * @param  string|int  $name
	 * 
	 * @return bool
	 */
	public function hasArgument($name): bool
	{
		return $this->input->hasArgument($name);
    }
	
	/**
	 * Get the value of a command argument.
	 * 
	 * @param  string|null  $key
	 * 
	 * @return array|string|bool|null
	 */
	public function argument($key): array|string|bool|null
	{
		if (is_null($key)) {
			return $this->input->getArguments();
		}
		
		return $this->input->getArgument($key);
	}
	
	/**
	 * Get all of the arguments passed to the command.
	 * 
	 * @return array
	 */
	public function arguments(): array
	{
		return $this->arguments();
	}
	
	/**
	 * Determine whether the option is defined in the command signature.
	 * 
	 * @param  string  $name
	 * 
	 * @return bool
	 */
	public function hasOption($name): bool
	{
		return $this->input->hasOption($name);
	}
	
	/**
	 * Get the value of a command option.
	 * 
	 * @param  string|null  $key
	 * 
	 * @return string|array|bool|null
	 */
	public function option($key = null): array|string|bool|null
	{
		if (is_null($key)) {
			return $this->input->getOptions();
		}
		
		return $this->input->getOption($key);
	}
	
	/**
	 * Get all of the options passed to the command.
	 * 
	 * @return array
	 */
	public function options(): array
	{
		return $this->option();
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
		$this->output->newLine($num);
	}
	
	/**
	 * Writes a string formatting for comment output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function comment(string $message, $verbosity = null)
	{
		$this->line($message, 'fg=default;bg=default', $verbosity);
	}

	/**
	 * Writes a string formatting for note output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function note(string $message, $verbosity = null)
	{
		$this->line($message, 'fg=yellow', $verbosity);
	}
	
	/**
	 * Writes a string formatting for success output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function success(string $message, $verbosity = null)
	{
		$this->line($message, 'fg=black;bg=green', $verbosity);
	}
	
	/**
	 * Writes a string formatting for info output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function info(string $message, $verbosity = null)
	{
		$this->line($message, 'fg=green;options=bold', $verbosity);
	}

	/**
	 * Writes a string formatting for caution output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function caution(string $message, $verbosity = null)
	{
		$this->line($message, 'fg=red;bg=white', $verbosity);
	}

	/**
	 * Writes a string formatting for question output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function question(string $message, $verbosity = null)
	{
		$this->line($message, 'question', $verbosity);
	}
	
	/**
	 * Writes a string formatting for warning output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function warning(string $message, $verbosity = null)
	{
		$this->line($message, 'fg=black;bg=yellow', $verbosity);
	}
	
	/**
	 * Writes a string formatting for error output.
	 * 
	 * @param  string  $message
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function error(string $message, $verbosity = null)
	{
		$this->line($message, 'fg=white;bg=red;options=bold', $verbosity);
	}
	
	/**
	 * Writes a string formatting for stantard output.
	 * 
	 * @param  string  $message
	 * @param  string|null  $style
	 * @param  int|string|null  $verbosity
	 * 
	 * @return void
	 */
	public function line(string $message, ?string $style = null, $verbosity = null)
	{
		$styled = $style ? "<$style>$message</>" : $message;
		
		return $this->output->writeln($styled, $this->parseVerbosity($verbosity));
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
		$this->output->writeln('', $newlines);
		$this->output->writeln(str_repeat('-', $width));
		$this->output->writeln('', $newlines);
	}
	
	/**
	 * Set the input interface implementation.
	 * 
	 * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
	 * 
	 * @return void
	 */
	public function setInput(InputInterface $input): void
	{
		$this->input = $input;
	}
	
	/**
	 * Set the output interface implementation.
	 * 
	 * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output
	 * 
	 * @return void
	 */
	public function setOutput(OutputInterface $output): void
	{
		$this->output = $output;
	}
	
	/**
	 * Set the verbosity level.
	 * 
	 * @param  string|int  $level
	 * 
	 * @return void
	 */
	protected function setVerbosity($level): void
	{
		$this->verbosity = $this->parseVerbosity($level);
	}
	
	/**
	 * Get the verbosity level in terms of OutputInterface level.
	 * 
	 * @param  string|int|null  $level
	 * 
	 * @return int
	 */
	protected function parseVerbosity($level = null): int
	{
		$level ??= '';
		
		if (isset($this->verbosityMap[$level])) {
			$level = $this->verbosityMap[$level];
		} elseif ( ! is_int($level)) {
			$level = $this->verbosity;
		}
		
		return $level;
	}

    /**
     * Get the output implementation.
     *
     * @return \Syscodes\Components\Console\Output\Output
     */
    public function getOutput()
    {
        return $this->output;
    }
}