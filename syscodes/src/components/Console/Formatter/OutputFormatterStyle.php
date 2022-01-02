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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Formatter;

use InvalidArgumentException;
use Syscodes\Components\Console\Style\TagStyle;
use Syscodes\Components\Contracts\Console\OutputFormatterStyle as OutputFormatterStyleInterface;

/**
 * Allows that formatter style class for defining styles.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class OutputFormatterStyle implements OutputFormatterStyleInterface
{
    /**
     * Gets the background of CLI command.
     * 
     * @var string $background
     */
    protected $background;

    /**
     * Gets the color of CLI command.
     * 
     * @var string $color
     */
    protected $color;

    /**
     * Gets the foreground of CLI command.
     * 
     * @var string $foreground
     */
    protected $foreground;

    /**
     * Gets a specific style option.
     * 
     * @var array $options
     */
    protected $options = [];

    /**
     * Constructor. The new a OutputFormatterStyles instance.
     * 
     * @param  string|null  $foreground  The style foreground color name
     * @param  string|null  $background  The style background color name
     * @param  array  $options  The specify style option
     * 
     * @return void
     */
    public function __construct($foreground = null, $background = null, array $options = [])
    {
        if (null !== $foreground) {
            $this->setForeground($this->parseColor($foreground));
        }
        
        if (null !== $background) {
            $this->setBackground($this->parseColor($background, true));
        }
        
        if (count($options)) {
            $this->setOptions($options);
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
        
        if (isset(TagStyle::COLORS[$color])) {
            return ($background ? '4' : '3').TagStyle::COLORS[$color];
        }
        
        if (isset(TagStyle::BRIGHT_COLORS[$color])) {
            return ($background ? '10' : '9').TagStyle::BRIGHT_COLORS[$color];
        }
        
        throw new InvalidArgumentException(
            sprintf('Invalid "%s" color; expected one of (%s).', 
                $color, implode(', ', array_merge(array_keys(TagStyle::COLORS), array_keys(TagStyle::BRIGHT_COLORS)))
            )
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function setForeground(string $color = null): void
    {
        if (null === $color) {
            $this->foreground = null;
            
            return;
        }
        
        $this->foreground = $color;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setBackground(string $color = null): void
    {
        if (null === $color) {
            $this->background = null;

            return;
        }
        
        $this->background = $color;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setOption(string $option): void
    {
        if ( ! isset(TagStyle::AVAILABLE_OPTIONS[$option])) {
            throw new InvalidArgumentException(
                sprintf('Invalid option specified: "%s". Expected one of (%s)',
                    $option,
                    implode(', ', array_keys(TagStyle::AVAILABLE_OPTIONS))
                )
            );
        }
        
        if (false === array_search(TagStyle::AVAILABLE_OPTIONS[$option], $this->options)) {
            $this->options[] = TagStyle::AVAILABLE_OPTIONS[$option];
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function unsetOption(string $option): void
    {
        if ( ! isset(TagStyle::AVAILABLE_OPTIONS[$option])) {
            throw new InvalidArgumentException(
                sprintf('Invalid option specified: "%s". Expected one of (%s)',
                    $option,
                    implode(', ', array_keys(TagStyle::AVAILABLE_OPTIONS))
                )
            );
        }
        
        $position = array_search(TagStyle::AVAILABLE_OPTIONS[$option], $this->options);
        
        if (false !== $position) {
            unset($this->options[$position]);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): void
    {
        $this->options = [];
        
        foreach ($options as $option) {
            $this->setOption($option);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function apply(string $text): string
    {
        $codes = [];
        
        if (null !== $this->foreground) {
            $codes[] = $this->foreground;
        }
        
        if (null !== $this->background) {
            $codes[] = $this->background;
        }
        
        if (count($this->options)) {
            $codes = array_merge($codes, $this->options);
        }
        
        return sprintf("\033[%sm%s\033[0m", implode(';', $codes), $text);
    }
}