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

/**
 * Outputs many string to the cli.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Writer
{
	/**
	 * Get the Color for the Cli command.
	 * 
	 * @var \Syscodes\Console\Output\Color $colorizer
	 */
	protected $colorizer;

	/**
	 * The method of colorizer command.
	 * 
	 * @var string $method 
	 */
	protected $method;

	/**
 	 * The standar STDERR is where the application writes its error messages.
 	 *
 	 * @var string $stderr 
 	 */
	protected $stderr;

	/**
 	 * The estandar STDOUT is where the application records its output messages.
 	 *
 	 * @var resource $stdout
 	 */
	protected $stdout;

	/**
	 * Constructor. Create a new Write class instance.
	 * 
	 * @param  string|null  $path
	 * @param  \Syscodes\Console\Output\Color|null
	 * 
	 * @return void 
	 */
	public function __construct(string $path = null, Color $colorizer = null)
	{
		if ($path) {
			$path = fopen($path, 'w');
		}

		// Writes with color all messages
		$this->colorizer = $colorizer ?? new Color;

		// Writes its error messages
		$this->stderr = $path ?: \STDERR;

		// Records its output messages
		$this->stdout = $path ?: \STDOUT;
	}
	
	/**
	 * Outputs a string to the cli.	If you send an array it will implode them
	 * with a line break.
	 * 
	 * @param  string|array  $text  The text to output, or array of lines
	 * @param  bool  $eol  End of line command
	 * 
	 * @return string
	 */
	public function write(string $text = '', bool $eol = false)
	{
		[$method, $this->method] = [$this->method ?: 'line', ''];

		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		$text  = $this->colorizer->{$method}($text, []);
		$error = (false !== \stripos($method, 'error'));
		
		if ($eol) {
			$text .= \PHP_EOL;
		}
		
		return $this->doWrite($text, $error);
	}
	
	/**
	 * The library is intended for used on Cli command, this commands 
	 * may be called from controllers and elsewhere of framework.
	 * 
	 * @param  string  $text  The text to output
	 * @param  bool  $error  Choose the activation of the 'error' method
	 * 
	 * @return self
	 */
	public function doWrite(string $text, bool $error = false): self
	{
		$handle = $error ? $this->stderr : $this->stdout;

		\fwrite($handle, $text);

		return $this;
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
		$this->write(str_repeat(\PHP_EOL, \max($num, 1)));
	}

	/**
     * Dynamically set methods.
     * 
     * @param  string  $name
     * 
     * @return self
     */
    public function __get(string $name): self
    {
        if (false === \strpos($this->method, $name)) {
			$this->method .= $this->method ? \ucfirst($name) : $name;
		}

		return $this;
    }
	
	/**
	 * Dynamically handle calls into the Writer instance.
	 * 
	 * @param  string  $method
	 * @param  array  $parameters
	 * 
	 * @return self
	 */
	public function __call(string $method, array $parameters): self
	{
		$this->method = $method;

		return $this->write(...$arguments);
	}
}