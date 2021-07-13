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

use Syscodes\Core\Http\Exceptions\LenevorException;

/**
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Color
{
    /**
     * Background color identifier.
     * 
     * @var array $backgroundColors
     */
    protected $backgroundColors = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47'
    ];

    /**
     * Foreground color identifier.
     * 
     * @var array $foregroundColors
     */
    protected $foregroundColors = [
       'black'         => '0;30',
       'dark_gray'     => '1;30',
       'blue'          => '0;34',
       'dark_blue'     => '1;34',
       'light_blue'    => '1;34',
       'green'         => '0;32',
       'light_green'   => '1;32',
       'cyan'          => '0;36', 
       'light_cyan'    => '1;36',
       'red'           => '0;31',
       'light_red'     => '1;31',
       'purple'        => '0;35',
       'light_purple'  => '1;35',
       'light_yellow'  => '0;33',
       'yellow'        => '1;33',
       'light_gray'    => '0;37',
       'white'         => '1;37'
    ];

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
 	 * @param  string  $foreground  The foreground color
 	 * @param  string  $background  The background color
 	 * @param  string  $format  Other formatting to apply. Currently only 'underline' is understood
 	 *
 	 * @return string  The color coded string
 	 *
 	 * @throws \Syscodes\Core\Exceptions\LenevorException
 	 */
 	public function line(string $text, string $foreground, string $background = null, string $format = null)
 	{
 		if ($this->noColor) {
 			return $text;
 		}

 		if ( ! Arr::exists($this->foregroundColors, $foreground)) {
 			throw new LenevorException($this->error("Invalid CLI foreground color: {$foreground}."));
 		}

 		if ( $background !== null && ! Arr::exists($this->backgroundColors, $background)) {
 			throw new LenevorException($this->error("Invalid CLI background color: {$background}."));
 		}

 		$string = "\033[".$this->foregroundColors[$foreground]."m";

 		if ($background !== null) {
 			$string .= "\033[".$this->backgroundColors[$background]."m";
 		}

 		if ($format === 'underline') {
 			$string .= "\033[4m";
 		}

 		$string .= $text."\033[0m";

 		return $string;
 	}

    /**
 	 * Outputs an error to the CLI using STDERR instead of STDOUT.
 	 *
 	 * @param  string|array  $text  The text to output, or array of errors
 	 * @param  string  $foreground  The foreground color
 	 * @param  string|null  $background  the background color
 	 *
 	 * @return string
 	 */
 	public function error(string $text = '', string $foreground = 'light_red', string $background = null)
 	{
		if (is_array($text)) {
			$text = implode(PHP_EOL, $text);
		}
		
		if ($foreground || $background) {
			$text = $this->line($text, $foreground, $background);
		}
		
		(new Write)->fwrite($this->stderr, $text.PHP_EOL);
	}
}