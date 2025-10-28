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

use InvalidArgumentException;
use Syscodes\Components\Contracts\Console\Output\OutputFormatter as OutputFormatterInterface;
use Syscodes\Components\Contracts\Console\Output\OutputFormatterStyle as OutputFormatterStyleInterface;

/**
 * Formatter class for console output.
 */
class OutputFormatter implements OutputFormatterInterface
{
    /**
     * Checks if the decorated is actived for console output.
     * 
     * @var bool $decorated
     */
    protected $decorated;

    /**
     * Gets the styles for console output.
     * 
     * @var array $styles
     */
    protected $styles = [];

    /**
     * Constructor. Create a new OutputFormatter instance.
     *
     * @param  bool  $decorated  Whether this formatter should actually decorate strings
     * @param  array   $styles  Array of "name => FormatterStyle" instances
     *
     * @return void
     */
    public function __construct(bool $decorated = false, array $styles = [])
    {
        $this->decorated = (bool) $decorated;

        $this->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $this->setStyle('info', new OutputFormatterStyle('green'));
        $this->setStyle('comment', new OutputFormatterStyle('yellow'));
        $this->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
        $this->setStyle('success', new OutputFormatterStyle('black', 'green'));
        $this->setStyle('note', new OutputFormatterStyle('blue'));
        $this->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));

        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }
    }

    /**
     * Sets the decorated for styles in messages.
     * 
     * @param  bool  $decorated
     * 
     * @return void
     */
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = (bool) $decorated;
    }

    /**
     * Gets the decorated for styles in messages.
     * 
     * @return bool
     */
    public function getDecorated(): bool
    {
        return $this->decorated;
    }

    /**
     * Sets a new style.
     * 
     * @param  string  $name
     * @param  \Syscodes\Components\Contracts\Console\OutputFormatterStyle  $style
     * 
     * @return void
     */
    public function setStyle($name, OutputFormatterStyleInterface $style): void
    {
        $this->styles[strtolower($name)] = $style;
    }

    /**
     * Checks if output formatter has style with specified name.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function hasStyle(string $name): bool
    {
        return isset($this->styles[strtolower($name)]);
    }

    /**
     * Gets style options from style with specified name.
     * 
     * @param  string  $name
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    public function getStyle(string $name): string
    {
        if (!$this->hasStyle($name)) {
            throw new InvalidArgumentException('Undefined style: '.$name);
        }

        return $this->styles[strtolower($name)];
    }

    /**
     * Formats a message depending to the given styles.
     * 
     * @param  string  $message
     * 
     * @return string
     */
    public function format(?string $message): string
    {
        return preg_replace_callback(self::FORMAT_PATTERN, [$this, 'replaceStyle'], $message);
    }

    /**
     * Replaces style of the output.
     *
     * @param  array  $match
     *
     * @return string  The replaced style
     */
    private function replaceStyle($match)
    {
        if (isset($this->styles[strtolower($match[1])])) {
            $style = $this->styles[strtolower($match[1])];
        } else {
            $style = $this->createStyleFromString($match[1]);

            if (false === $style) {
                return $match[0];
            }
        }

        return $this->getDecorated() && strlen($match[2]) > 0 ? $style->apply($this->format($match[2])) : $match[2];
    }

    /**
     * Tries to create new style instance from string.
     *
     * @param  string  $string
     *
     * @return \Syscodes\Component\Console\Formatter\OutputFormatterStyle|bool  False if string is not format string
     */
    private function createStyleFromString($string)
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }

        if ( ! preg_match_all('~([^=]+)=([^;]+)(;|$)~', $string, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $style = new OutputFormatterStyle();
        
        foreach ($matches as $match) {
            array_shift($match);

            $match[0] = strtolower($match[0]);

            if ('fg' == $match[0]) {
                $style->setForeground($match[1]);
            } elseif ('bg' == $match[0]) {
                $style->setBackground($match[1]);
            } elseif ('options' === $match[0]) {
                preg_match_all('([^,;]+)', strtolower($match[1]), $options);
                
                $options = array_shift($options);
                
                foreach ($options as $option) {
                    $style->setOption($option);
                }
            } else {
                return null;
            }
        }

        return $style;
    }
}