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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Style;

use InvalidArgumentException;

/**
 * Configures the foreground, background and options 
 * for any text you should to highlight.
 */
final class TagStyle
{
    /**
     * Gets the background of CLI command.
     * 
     * @var string $background
     */
    private $background;

    /**
     * Gets the foreground of CLI command.
     * 
     * @var string $foreground
     */
    private $foreground;

    /**
     * Gets a specific style option.
     * 
     * @var array $options
     */
    private $options = [];

    /**
     * Constructor. The new a OutputFormatterStyles instance.
     * 
     * @param  string  $foreground  The style foreground color name
     * @param  string  $background  The style background color name
     * @param  array  $options  The specify style option
     * 
     * @return void
     */
    public function __construct(string $foreground = '', string $background = '', array $options = [])
    {
        if ('' !== $foreground || null !== $foreground) {
            $this->foreground = $this->parseColor($foreground);
        }
        
        if ('' !== $background || null !== $background) {
            $this->background = $this->parseColor($background, true);
        }
        
        foreach ($options as $option) {
            if ( ! isset(FormatColor::AVAILABLE_OPTIONS[$option])) {
                throw new InvalidArgumentException(
                    sprintf('Invalid option specified: "%s". Expected one of (%s).', 
                        $option, 
                        implode(', ', array_keys(FormatColor::AVAILABLE_OPTIONS))
                    )
                );
            }

            $this->options[$option] = FormatColor::AVAILABLE_OPTIONS[$option];
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
    private function parseColor(string $color, bool $background = false): string
    {
        if ('' === $color) {
            return '';
        }
        
        if (isset(FormatColor::COLORS[$color])) {
            return ($background ? '4' : '3').FormatColor::COLORS[$color];
        }
        
        if (isset(FormatColor::BRIGHT_COLORS[$color])) {
            return ($background ? '10' : '9').FormatColor::BRIGHT_COLORS[$color];
        }
        
        throw new InvalidArgumentException(
            sprintf('Invalid "%s" color; expected one of (%s).', 
                $color, implode(', ', array_merge(array_keys(FormatColor::COLORS), array_keys(FormatColor::BRIGHT_COLORS)))
            )
        );
    }
    
    /**
     * Applies the style to a given text.
     * 
     * @param  string  $text
     * 
     * @return string
     */
    public function apply(string $text): string
    {
        $codes = [];
        
        if ('' !== $this->foreground) {
            $codes[] = $this->foreground;
        }
        
        if ('' !== $this->background) {
            $codes[] = $this->background;
        }
        
        if (count($this->options)) {
            $codes = array_merge($codes, $this->options);
        }
        
        if (0 === count($codes)) {
            return '';
        }
        
        return sprintf("\033[%sm%s\033[0m", implode(';', $codes), $text);
    }
}