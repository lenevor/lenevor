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

namespace Syscodes\Console;

use Exception;
use Syscodes\Collections\Arr;
use Syscodes\Core\Http\Exceptions\LenevorException;

/**
 * Set of static methods useful for CLI request handling.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Cli
{
	// [
	// 	'black'         => '0;30',
	// 	'dark_gray'     => '1;30',
	// 	'red'           => '0;31',
	// 	'light_red'     => '1;31',
	// 	'green'         => '0;32',
	// 	'light_green'   => '1;32',
	// 	'light_yellow'  => '0;33',
	// 	'yellow'        => '1;33',
	// 	'blue'          => '0;34',
	// 	'dark_blue'     => '1;34',
	// 	'light_blue'    => '1;34',
	// 	'purple'        => '0;35',
	// 	'light_purple'  => '1;35',	
	// 	'cyan'          => '0;36', 
	// 	'light_cyan'    => '1;36',
	// 	'light_gray'    => '0;37',
	// 	'white'         => '1;37'
 	// ];
	 
	const BLACK    = 30;
	const RED      = 31;
	const GREEN    = 32;
	const YELLOW   = 33;
	const BLUE     = 34;
	const PURPLE   = 35;
	const CYAN     = 36;
	const WHITE    = 37;
	const GRAY     = 47;
	const DARKGRAY = 100;
	
	/**
	 * Get CLI format for color and bold.
	 * 
	 * @var string $format
	 */
	protected static $format = "\033[:mod:;:fg:;:bg:m";

	/**
	 * Indicates that you do not use any color for foreground or background.
	 *
	 * @var bool $noColor
	 */
	public static $noColor = false;

	/**
	 * String of arguments to be used in console.
	 *
	 * @var array $options
	 */
	protected static $options = [];

	/**
	 * Readline Support for command line.
	 *
	 * @var bool $readlineSupport
	 */
	public static $readlineSupport = false;

	/**
	 * List of array segments.
	 *
	 * @var array $segments
	 */
	protected static $segments = [];

 	/**
 	 * The standar STDERR is where the application writes its error messages.
 	 *
 	 * @var string $stderr 
 	 */
 	protected static $stderr;

 	/**
 	 * The estandar STDOUT is where the application records its output messages.
 	 *
 	 * @var resource $stdout
 	 */
 	protected static $stdout;

 	/**
	 * Message that tells the user that he is waiting to receive an order.
	 *
	 * @var string $waitMsg
	 */
	public static $waitMsg = 'Press any key to continue...';

	/**
	 * Static constructor. Parses all the CLI params.
	 * 
	 * @return \Syscodes\Contracts\Core\Lenevor  $core
	 * 
	 * @throws \Exception
	 */
 	public static function initialize()
 	{
 		// Readline is an extension for PHP that makes interactive the command console
 		static::$readlineSupport = extension_loaded('readline');

 		// clear segments & options to keep testing clean
 		static::$options  = [];
 		static::$segments = [];

 		static::parseCommandLine();

 		// Writes its error messages
 		static::$stderr = STDERR;

 		// Records its output messages
 		static::$stdout = STDOUT;
 	}

 	/**
 	 * Beeps a certain number of times.
	 *
	 * @param  int  $num  The number of times to beep
	 *
 	 * @return int
 	 */
 	public static function bell(int $num = 1)
 	{
 		echo str_repeat("\x07", $num);
 	}

 	/**
 	 * Clears the screen of output.
 	 *
 	 * @return void
 	 */
 	public static function clearScreen()
 	{
 		static::isWindows()
 			// Windows doesn't work for this, but their terminal is tiny so shove this in
 			? static::newLine(40)
 			// Anything with a flair of Unix will handle these magic characters
 			: fwrite(static::$stdout, chr(27)."[H".chr(27)."[2J");
 	}

 	/**
 	 * Returns the given text with the correct color codes for a foreground and
	 * optionally a background color.
 	 *
 	 * @param  string  $text  The text to color
 	 * @param  array  $style  Get style for foreground and background
	 * @param  string|null  $type  The 'underline' format
 	 *
 	 * @return string  The color coded string
 	 */
 	public static function color(string $text, array $style = [], string $type = null)
 	{
 		if (static::$noColor) {
 			return $text;
 		}

		$style += ['bg' => null, 'fg' => static::WHITE, 'bold' => 0, 'mod' => null];

		$format = $style['bg'] === null
            ? str_replace(';:bg:', '', static::$format)
            : static::$format;

        $string = strtr($format, [
			':mod:' => (int) ($style['mod'] ?? $style['bold']),
            ':fg:'  => (int) $style['fg'],
            ':bg:'  => (int) $style['bg'] + 10,
        ]);

		if ('underline' === $type) {
			$string .= "\033[4m";
		}
		
		$string .= $text."\033[0m";

 		return $string;
 	}

 	/**
 	 * Get the number of characters in a string.
 	 *
 	 * @param  string  $string
	 * @param  array  $style
 	 *
 	 * @return int
 	 */
 	public static function strlen(?string $string, array $style = [])
 	{
 		if (is_null($string)) {
 			return 0;
 		}

 		foreach ($style as $color) {
 			$string = strtr($string, ["\033[".$color.'m' => '']);
 		}

 		$string = strtr($string, ["\033[4m" => '', "\033[0m" => '']);

 		return mb_strlen($string);
 	}

 	/**
 	 * Outputs an error to the CLI using STDERR instead of STDOUT.
 	 *
 	 * @param  string|array  $text  The text to output, or array of errors
 	 * @param  array  $style  Get style for foreground and background
 	 *
 	 * @return string
 	 */
 	public static function error(string $text, array $style = [])
 	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		$text = static::color($text, ['fg' => static::RED] + $style);

		static::fwrite(static::$stderr, $text.PHP_EOL);
	}

	/**
	 * Attempts to determine the width of the viewable CLI window.
	 *
	 * @param  int  $default
	 *
	 * @return int
	 */
	public static function getWidth(int $default = 80)
	{
		if (static::isWindows() || (int) shell_exec('tput cols') === 0) {
			return $default;
		}

		return (int) shell_exec('tput cols');
	}

	/**
	 * Attempts to determine the height of the viewable CLI window.
	 *
	 * @param  int  $default
	 *
	 * @return int
	 */
	public static function getHeight(int $default = 32)
	{
		if (static::isWindows()) {
			return $default;
		}

		return (int) shell_exec('tput lines');
	}

	/**
	 * Takes a string and writes it to the command line, wrapping to a maximum width. 
	 * If no maximum width is specified, will wrap to the window's max.
	 *
	 * @param  string  $string
	 * @param  int  $max
	 * @param  int $padLeft
	 *
	 * @return string
	 */
	public static function wrap(string $string = null, int $max = 0, int $padLeft = 0)
	{
		if (empty($string)) {
			return '';
		}

		if ($max === 0) {
			$max = static::getWidth();
		}

		if (static::getWidth() < $max) {
			$max = static::getWidth();
		}

		$max = $max - $padLeft;

		$lines = wordwrap($string, $max);

		if ($pad_left > 0) {
			$lines = explode(PHP_EOL, $lines);

			$first = true;

			array_walk ($lines, function (&$line, $index) use ($pad_left, &$first) {
				if ( ! $first) {
					$line = str_repeat(' ', $pad_left) . $line;
				} else {
					$first = false;
				}
			});

			$lines = implode(PHP_EOL, $lines);
		}

		return $lines;
	}

 	/**
 	 * Get input from the shell, using readline or the standard STDIN.
 	 *
 	 * @param  string|int  $prefix  The name of the option (int if unnamed)
 	 *
 	 * @return string
 	 */
 	public static function input($prefix = '')
 	{
 		if (static::$readlineSupport) {
 			return readline($prefix);
 		}

 		echo $prefix;

 		return fgets(STDIN);
 	}

 	/**
 	 * If operating system === windows.
 	 * 
 	 * @return string
 	 */
 	public static function isWindows()
 	{
 		return stripos(PHP_OS, 'WIN') === 0;
 	}

 	/**
 	 * Enter a number of empty lines.
 	 * 
 	 * @param  int  $num  Number of lines to output
 	 *
 	 * @return void
 	 */
 	public static function newLine(int $num = 1)
 	{
 		for ($i = 0; $i < $num; $i++) {			
 			static::write(\PHP_EOL);
 		}
 	}

 	/**
	 * Returns the option with the given name. You can also give the option number.
	 *
	 * @param  string|int  $name  The name of the option (int if unnamed)
	 * @param  mixed  $default  The value to return if the option is not defined
	 *
	 * @return mixed
	 * 
	 * @uses   \Syscodes\Contract\Core\Lenevor
	 */
 	public static function option($name, $default = null)
 	{
 		if ( ! isset(static::$options[$name])) {
 			return Lenevor::value($default);
 		}

 		return static::$options[$name];
	}

	/**
	 * Parses the command line it was called from and collects all
	 * options and valid segments.
	 * 
	 * @return bool
	 */
	protected static function parseCommandLine()
	{
		$options = false;

		for ($i = 1; $i < $_SERVER['argc']; $i++) {
			if ( ! $options && mb_strpos($_SERVER['argv'][$i], '-') === false) {
				static::$segments[] = $_SERVER['argv'][$i];

				continue;
			}

			$options = true;

			$args  = str_replace('-', '', $_SERVER['argv'][$i]);
			$value = null;

			if (isset($_SERVER['argv'][$i + 1]) && mb_strpos($_SERVER['argv'][$i + 1], '-') !== 0) {
				$value = $_SERVER['argv'][$i + 1];
				$i++;
			}

			static::$options[$args] = $value;

			$options = false;
		}
	}

	/**
	 * Returns the command line string portions of the arguments, minus
	 * any options, as a string.
	 *
	 * @return string
	 */
	public static function getURI()
	{
		return implode('/', static::$segments);
	}

	/**
	 * Returns an individual segment.
	 *
	 * @param  int  $index
	 * 
	 * @return mixed|null
	 */
	public static function getSegment(int $index)
	{
		if ( ! isset(static::$segments[$index - 1])) {
			return null;
		}

		return static::$segments[$index - 1];
	}

	/**
	 * Returns the raw array of segments found.
	 *
	 * @return array
	 */
	public static function getSegments()
	{
		return static::$segments;
	}

 	/**
 	 * Asks the user for input.  This can have either 1 or 2 arguments.
	 *
	 * Usage:
	 *
	 * // Waits for any key press
	 * Cli::prompt();
	 *
	 * // Takes any input
	 * $color = Cli::prompt('What is your favorite color?');
	 *
	 * // Takes any input, but offers default
	 * $color = Cli::prompt('What is your favourite color?', 'white');
	 *
	 * // Will only accept the options in the array
	 * $ready = Cli::prompt('Are you ready?', array('y','n'));
	 *
	 * @return string The user input
	 */
 	public static function prompt()
 	{
 		$args = func_get_args();

		$options = [];
		$output  = '';
		$default = null;

		// How many we got
		$arg_count = count($args);

		// Is the last argument a boolean? True means required
		$required = end($args) === true;

		// Reduce the argument count if required was passed, we don't care about that anymore
		$required === true and --$arg_count;

		// This method can take a few crazy combinations of arguments, so lets work it out
		switch ($arg_count) {
			case 2:

				// E.g: $ready = Cli::prompt('Are you ready?', ['y','n']);
				if (is_array($args[1])) {
					list($output, $options) = $args;
				}
				// E.g: $color = Cli::prompt('What is your favourite color?', 'white');
				elseif (is_string($args[1])) {
					list($output, $default) = $args;
				}

			break;

			case 1:

				// No question (probably been asked already) so just show options
				// E.g: $ready = Cli::prompt(array('y','n'));
				if (is_array($args[0])) {
					$options = $args[0];
				}
				// Question without options
				// E.g: $ready = Cli::prompt('What did you do today?');
				elseif (is_string($args[0])) {
					$output = $args[0];
				}

			break;
		}

		// If a question has been asked with the read
		if ($output !== '') {
			$extra_output = '';

			if ($default !== null) {
				$extra_output = ' [ Default: "'.$default.'" ]';
			} elseif ($options !== []) {
				$extra_output = ' [ '.implode(' | ', $options).' ]';
			}

			static::fwrite(static::$stdout, $output.$extra_output.': ');
		}

		// Read the input from keyboard.
		$input = trim(static::input()) ?: $default;

		// No input provided and we require one (default will stop this being called)
		if (empty($input) and $required === true) {
			static::write('This is required.');
			static::newLine();

			$input = forward_static_call_array([__CLASS__, 'prompt'], $args);
		}

		// If options are provided and the choice is not in the array, tell them to try again
		if ( ! empty($options) and ! in_array($input, $options)) {
			static::write('This is not a valid option. Please try again.');
			static::newLine();

			$input = forward_static_call_array([__CLASS__, 'prompt'], $args);
		}

		return $input;
 	}

 	/**
 	 * Allows you to set a commandline option from code.
 	 *
 	 * @param  string|int  $name  The name of the option (int if unnamed)
	 * @param  mixed|null  $value  The value to set, or null to delete the option
	 *
	 * @return mixed
	 */
 	public static function setOption($name, $value = null)
 	{
 		if ($value == null) {
 			if (isset(static::$options[$name])) {
 				unset(static::$options[$name]);
 			}
 		} else {
 			static::$options[$name] = $value;
 		}
 	}

 	/**
 	 * Waits a certain number of seconds, optionally showing a wait message and
	 * waiting for a key press.
 	 *
 	 * @param  int  $seconds  Number of seconds
 	 * @param  bool  $countdown  Show a countdown or not
 	 *
 	 * @return string
 	 */
 	public static function wait(int $seconds = 0, bool $countdown = false)
 	{
 		if ($countdown === true) {
			$time = $seconds;

 			while ($time > 0) {
 				fwrite(static::$stdout, $time.'... ');
 				sleep(1);
 				$time--;
 			}

 			static::write();
 		} else {
 			if ($seconds = 0) {
 				sleep($seconds);
 			} else {
 				static::write(static::$waitMsg);
 				static::input();
 			}
 		}
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
 	public static function write(string $text = '', bool $eol = false)
 	{
 		if (is_array($text)) {
 			$text = implode(PHP_EOL, $text);
 		}

 		$text = static::color($text, []);

		if ($eol) {
			$text .= \PHP_EOL;
		}
 		
 		static::fwrite(static::$stdout, $text);
 	}

 	/**
 	 * Returns a well formatted table.
 	 *
 	 * @param  array  $tbody  List of rows
 	 * @param  array  $thead  List of columns
 	 *
 	 * @return void
 	 */
 	public static function table(array $tbody, array $thead = [])
 	{
 		$rows = [];

 		if ( ! empty($thead)) {
 			$rows[] = array_values($thead);
 		}

 		foreach ($tbody as $tr) {
 			$rows[] = count($rows);
 		}

 		$totalRows = count($rows);

 		// Store all columns lengths
 		$allColsLengths = [];

 		// Store maximum lengths by column
 		$maxColsLengths = [];

 		for ($row = 0; $row < $totalRows; $row++) {
 			$column = 0;

 			foreach ($rows[$row] as $col) {
 				$allColsLengths[$row][$column] = static::strlen($col);

 				if ( ! isset($maxColsLengths[$column]) || $allColsLengths[$row][$column] > $maxColsLengths[$column]) {
 					$maxColsLengths[$column] = $allColsLengths[$row][$column];
 				}

 				$column++;
 			}
 		}

 		for ($row = 0; $row < $totalRows; $row++) {
 			$column = 0;

 			foreach ($rows[$row] as $col)
 			{
 				$diverse = $maxColsLengths[$column] - static::strlen($col);
 				
 				if ($diverse) {
 					$rows[$row][$column] = $rows[$row][$column].str_repeat(' ', $diverse);
 				}

 				$column++;
 			} 			
 		}

 		$table = '';
		$cols  = '';

 		for ($row = 0; $row < $rows; $row++) {
 			if (0 === $row) {
 				$cols = '+';

 				foreach ($rows[$row] as $col) {
 					$cols .= str_repeat('-', static::strlen($col) + 2).'+';
 				}

 				$table .= $cols.PHP_EOL;
 			}

 			$table .= '| '.implode('-', $rows[$row]).' |'.PHP_EOL;

 			if (0 === $row && ( ! empty($thead)) || ($row + 1) === $rows) {
 				$table .= $cols.PHP_EOL;
 			}
 		}

 		static::write($table);
 	}

	/**
	 * The library is intended for used on Cli commanda, 
	 * this commands can be called from controllers and 
	 * elsewhere of framework.
	 * 
	 * @param  resource  $handle
	 * @param  string  $text
	 * 
	 * @return $this
	 */
	protected static function fwrite($handle, string $text)
	{
		\fwrite($handle, $text);
	}
}