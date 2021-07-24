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

use InvalidArgumentException;

/**
 * Allows in the foreground and background a specific color 
 * for any text you should to highlight.  
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class Color
{
    protected const COLORS = [
		'black'   => 0,
		'red'     => 1,
		'green'   => 2,
		'yellow'  => 3,
		'blue'    => 4,
		'magenta' => 5,
		'cyan'    => 6,
		'white'   => 7,
		'default' => 9,
	];

	protected const LIGHT_COLORS = [
		'gray'          => 0,
		'light-red'     => 1,
		'light-green'   => 2,
		'light-yellow'  => 3,
		'light-blue'    => 4,
		'light-magenta' => 5,
		'light-cyan'    => 6,
		'light-white'   => 7,
	];

	protected const OPTIONS = [
		'bold' => ['set' => 1, 'unset' => 22],
		'underline' => ['set' => 4, 'unset' => 24],
		'blink' => ['set' => 5, 'unset' => 25],
		'reverse' => ['set' => 7, 'unset' => 27],
		'conceal' => ['set' => 8, 'unset' => 28],
	];

	/**
	 * The background color to text or CLI command.
	 * 
	 * @var string $background
	 */
	protected $background;

	/**
	 * The foreground color to text or CLI command.
	 * 
	 * @var string $foreground
	 */
	protected $foreground;

	/**
	 * Gets options the colors for CLI command.
	 * 
	 * @var array $options
	 */
	protected $options = [];

	/**
	 * Constructor. Create a new Color instance.
	 * 
	 * @param  string  $foreground
	 * @param  string  $background
	 * @param  array  $options
	 * 
	 * @return void
	 */
	public function __construct(string $foreground = '', string $background = '', array $options = [])
	{
		$this->foreground =  $this->parser($foreground);
		$this->background =  $this->parser($background, true);

		foreach ($options as $option) {
			if ( ! isset(self::OPTIONS[$option])) {
				throw new InvalidArgumentException(
					sprintf('Invalid option specified: "%s". Expected one of (%s).', 
						$option, 
						implode(',', array_keys(self::OPTIONS))
					)
				);
			}
			
			$this->options[$option] = self::OPTIONS[$option];
		}
	}

	/**
	 * Gets the parse color for capture to the color type that is needed 
	 * on foreground and background of CLI Commands.
	 * 
	 * @param  string  $color
	 * @param  bool  $background
	 * 
	 * @return string
	 * 
	 * @throws \InvalidArgumentException
	 */
	private function parser(string $color, bool $background = false): string
	{
		if ('' === $color) {
			return '';
		}

		if (isset(self::COLORS[$color])) {
			return ($background ? '4' : '3').self::COLORS[$color];
		}

		if (isset(self::LIGHT_COLORS[$color])) {
			return ($background ? '10' : '9').self::LIGHT_COLORS[$color];
		}

		throw new InvalidArgumentException(
			sprintf('Invalid "% s" color; expected one of (% s). ',
				$color,
				implode(', ',array_merge(array_keys(self::COLORS), array_keys(self::LIGHT_COLORS)))
			)
		);
	}

	/**
	 * Gets the result of the string applied to the text in the CLI command.
	 * 
	 * @param  string  $text
	 * 
	 * @return string
	 */
	public function apply(string $text): string
	{
		return $this->set().$text.$this->unset();
	}

	/**
	 * Gets the set color to CLI command.
	 * 
	 * @return string
	 */
	public function set(): string
	{
		$codes = [];
		
		if ('' !== $this->foreground) {
			$codes[] = $this->foreground;
		}
		
		if ('' !== $this->background) {
			$codes[] = $this->background;
		}
		
		foreach ($this->options as $option) {
			$codes[] = $option['set'];
		}
		
		if (0 === \count($codes)) {
			return '';
		}
		
		return sprintf("\033[%sm", implode(';', $codes));
	}

	/**
	 * Gets the unset color to CLI command.
	 * 
	 * @return string
	 */
	public function unset(): string
	{
		$codes = [];
		
		if ('' !== $this->foreground) {
			$codes[] = 39;
		}
		
		if ('' !== $this->background) {
			$codes[] = 49;
		}
		
		foreach ($this->options as $option) {
			$codes[] = $option['unset'];
		}
		
		if (0 === \count($codes)) {
			return '';
		}
		
		return sprintf("\033[%sm", implode(';', $codes));
	}
}