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

namespace Syscodes\Console\Formatter;

use Syscodes\Collections\Arr;
use Syscodes\Core\Http\Exceptions\LenevorException;

/**
 * Allows in the foreground and background a specific color 
 * for any text you should to highlight.  
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class Color
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
	protected $format = "\033[:mod:;:fg:;:bg:m";

    /**
     * Indicates that you do not use any color for foreground or background.
     *
     * @var bool $noColor
     */
    public $noColor = false;

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
 	public function line(string $text, array $style = [], string $type = null): string
 	{
 		if ($this->noColor) {
 			return $text;
 		}

		$style += ['bg' => null, 'fg' => static::WHITE, 'bold' => 0, 'mod' => null];

		$format = $style['bg'] === null
            ? str_replace(';:bg:', '', $this->format)
            : $this->format;

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
	 * Returns a line formatted as comment.
	 * 
	 * @param  string|array  $text  The text to output, or array of comment
	 * @param  array  $style  Get style for foreground and background
	 * 
	 * @return string
	 */
	public function comment(string $text, array $style = []): string
	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}

		return $this->line($text, [] + $style);
	}
	
	/**
	 * Returns a line formatted as error.
	 * 
	 * @param  string|array  $text  The text to output, or array of errors
	 * @param  array  $style  Get style for foreground and background
	 * 
	 * @return string
	 */
	public function error(string $text, array $style = []): string
	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		return $this->line($text, ['fg' => static::WHITE, 'bg' => static::RED] + $style);
	}
	
	/**
	 * Returns a line formatted as success (ok).
	 * 
	 * @param  string|array  $text  The text to output, or array of success
	 * @param  array  $style  Get style for foreground and background
	 * 
	 * @return string
	 */
	public function success(string $text, array $style = []): string
	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		return $this->line($text, ['fg' => static::BLACK, 'bg' => static::GREEN] + $style);
	}

	/**
	 * Returns a line formatted as warning.
	 * 
	 * @param  string|array  $text  The text to output, or array of warning
	 * @param  array  $style  Get style for foreground and background
	 * 
	 * @return string
	 */
	public function warning(string $text, array $style = []): string
	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		return $this->line($text, ['fg' => static::BLACK, 'bg' => static::YELLOW] + $style);
	}

	/**
	 * Returns a line formatted as info.
	 * 
	 * @param  string|array  $text  The text to output, or array of info
	 * @param  array  $style  Get style for foreground and background
	 * 
	 * @return string
	 */
	public function info(string $text, array $style = []): string
	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		return $this->line($text, ['fg' => static::BLUE] + $style);
	}

	/**
	 * Returns a line formatted as note.
	 * 
	 * @param  string|array  $text  The text to output, or array of note
	 * @param  array  $style  Get style for foreground and background
	 * 
	 * @return string
	 */
	public function note(string $text, array $style = []): string
	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		return $this->line($text, ['fg' => static::YELLOW] + $style);
	}
}