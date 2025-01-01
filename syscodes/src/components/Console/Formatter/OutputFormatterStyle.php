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

namespace Syscodes\Components\Console\Formatter;

use Syscodes\Components\Console\Style\TagStyle;
use Syscodes\Components\Contracts\Console\Output\OutputFormatterStyle as OutputFormatterStyleInterface;

/**
 * Allows that formatter style class for defining styles.
 */
class OutputFormatterStyle implements OutputFormatterStyleInterface
{
    /**
     * Gets the background of CLI command.
     * 
     * @var string $background
     */
    private string $background;

    /**
     * Gets the color of CLI command.
     * 
     * @var \Syscodes\Components\Console\Style\TagStyle $color
     */
    private TagStyle $color;

    /**
     * Gets the foreground of CLI command.
     * 
     * @var string $foreground
     */
    private string $foreground;

    /**
     * Gets a specific style option.
     * 
     * @var array $options
     */
    private array $options = [];

    /**
     * Constructor. The new a OutputFormatterStyles instance.
     * 
     * @param  string|null  $foreground  The style foreground color name
     * @param  string|null  $background  The style background color name
     * @param  array  $options  The specify style option
     * 
     * @return void
     */
    public function __construct(?string $foreground = null, ?string $background = null, array $options = [])
    {
        $this->color = new TagStyle($this->foreground = $foreground ?: '', $this->background = $background ?: '', $this->options = $options);
    }
    
    /**
     * Sets style foreground color.
     * 
     * @param  string|null  $color
     * 
     * @return void When the color name isn't defined
     */
    public function setForeground(?string $color = null): void
    {
        $this->color = new TagStyle($this->foreground = $color ?: '', $this->background, $this->options);
    }
    
    /**
     * Sets style background color.
     * 
     * @param  string|null  $color
     * 
     * @return void When the color name isn't defined
     */
    public function setBackground(?string $color = null): void
    {
        $this->color = new TagStyle($this->foreground, $this->background = $color ?: '', $this->options);
    }
    
    /**
     * Sets some specific style option.
     * 
     * @param  string  $option
     * 
     * @return void
     */
    public function setOption(string $option): void
    {
        $this->options[] = $option;

        $this->color = new TagStyle($this->foreground, $this->background, $this->options);
    }
    
    /**
     * Unsets some specific style option.
     * 
     * @param  string  $option
     * 
     * @return void
     */
    public function unsetOption(string $option): void
    {
        $pos = array_search($option, $this->options);

        if (false !== $pos) {
            unset($this->options[$pos]);
        }

        $this->color = new TagStyle($this->foreground, $this->background, $this->options);
    }
    
    /**
     * Sets multiple style options at once.
     * 
     * @param  array  $option
     * 
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->color = new TagStyle($this->foreground, $this->background, $this->options = $options);
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
        return $this->color->apply($text);
    }
}