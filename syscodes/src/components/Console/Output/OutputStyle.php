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

use Syscodes\Contracts\Console\Output as OutputInterface;
use Syscodes\Contracts\Console\OutputStyle as OutputStyleInterface;
use Syscodes\Contracts\Console\OutputFormatter as OutputFormatterInterface;

/**
 * Allows decorates a string output for add style at command line.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class OutputStyle implements OutputInterface, OutputStyleInterface
{
    /**
     * The output interface implementation.
     * 
     * @var \Syscodes\Contracts\Console\Output $output
     */
    protected $output;

    /**
     * Constructor. Create a new OutputSyle instance.
     * 
     * @param  \Syscodes\Contracts\Console\Output  $output
     * 
     * @return void
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
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
        $this->output->write(str_repeat(\PHP_EOL, \max($num, 1)));
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
	 * Gets the decorated flag.
	 * 
	 * @return bool
	 */
	public function getDecorated(): string
    {

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

    }

	/**
	 * Returns a output formatter instance.
	 * 
	 * @return \Syscodes\Contracts\Console\OutputFormatter
	 */
	public function getFormatter(): OutputFormatterInterface
    {

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

    }

    /**
	 * Writes a message to the output and adds a newline at the end.
	 * 
	 * @param  string|iterable  $messages  The message as an iterable of strings or a single string
	 * @param  int  $options  A bitmask of options (0 is considered the same as self::OUTPUT_NORMAL)
	 * 
	 * @return string
	 */
	public function writeln($messages, int $options = self::OUTPUT_NORMAL)
    {

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

    }
}