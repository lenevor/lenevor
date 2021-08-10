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

namespace Syscodes\Console\Style;

use InvalidArgumentException;

/**
 * Allows in the foreground and background a specific color 
 * for any text you should to highlight.  
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class ColorCode
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
		'bold'      => ColorANSICode::BOLD,
		'underline' => ColorANSICode::UNDERLINE,
		'blink'     => ColorANSICode::BLINK,
		'reverse'   => ColorANSICode::REVERSE,
		'concealed' => ColorANSICode::CONCEALED,
	];

	/**
	 * The background color to text or CLI command.
	 * 
	 * @var int $background
	 */
	protected $background = 0;

	/**
	 * The foreground color to text or CLI command.
	 * 
	 * @var int $foreground
	 */
	protected $foreground = 0;

	/**
	 * Gets options the colors for CLI command.
	 * 
	 * @var array $options
	 */
	protected $options = [];

	/**
     * Create a color style from a parameter string.
     * 
     * @param  string  $string  e.g 'fg=white;bg=black;options=bold,underscore'
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $string): ColorCode
    {
        $options = [];
        $parts   = explode(';', str_replace(' ', '', $string));
        
        $foreground = $background = '';

        foreach ($parts as $part) {
            $subParts = explode('=', $part);
            
            if (count($subParts) < 2) {
                continue;
            }
            
            switch ($subParts[0]) {
                case 'fg':
                    $foreground = $subParts[1];
                    break;
                case 'bg':
                    $background = $subParts[1];
                    break;
                case 'options':
                    $options = explode(',', $subParts[1]);
                    break;
                default:
                    throw new RuntimeException('Invalid option');
            }
        }
        
        return new self($foreground, $background, $options);
    }

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
						implode(', ', array_keys(self::OPTIONS))
					)
				);
			}
			
			$this->options[] = $option;
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
			sprintf('Invalid "%s" color; expected one of (% s). ',
				$color,
				implode(', ',array_merge(array_keys(self::COLORS), array_keys(self::LIGHT_COLORS)))
			)
		);
	}

	/**
	 * Gets the set color to CLI command.
	 * 
	 * @return string
	 */
	public function toStyle(): string
	{
		$codes = [];
		
		if ($this->foreground) {
			$codes[] = $this->foreground;
		}
		
		if ($this->background) {
			$codes[] = $this->background;
		}
		
		foreach ($this->options as $option) {
			$codes[] = self::OPTIONS[$option];
		}
		
		return implode(';', $codes);
	}
    
    /**
     * Returns the Colors as an codes string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->toStyle();
	}
}