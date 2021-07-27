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
use Syscodes\Console\Formatter\OutputFormatterStyles;

/**
 * Formatter class for console output.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class OutputFormatter
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
     * @param  bool  $decorated
     * @param  array  $styles
     * 
     * @return void
     */
    public function __construct(bool $decorated =  false, array $styles = [])
    {
        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }

        $this->setStyle('error', new OutputFormatterStyles('white', 'red'));
        $this->setStyle('comment', new OutputFormatterStyles('yellow'));
        $this->setStyle('info', new OutputFormatterStyles('blue'));
        $this->setStyle('warning', new OutputFormatterStyles('black', 'yellow'));
        $this->setStyle('success', new OutputFormatterStyles('black', 'green'));

        $this->decorated = $decorated;
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
    public function getStyle(string $name): array
    {
        if ( ! $this->hasStyle($name)) {
            throw new InvalidArgumentException(sprintf('Undefined style: "%s"', $name));
        }

        return $this->styles[\strtolower($name)];
    }

    /**
     * Sets a new style.
     * 
     * @param  string  $name
     * @param  \Syscodes\Contracts\Console\OutputFormatterStyles  $style
     * 
     * @return void
     */
    public function setStyle(string $name, OutputFormatterStyles $style): void
    {
        $this->styles[\strtolower($name)] = $style;
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
        return isset($this->styles[\strtolower($name)]);
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
     * Sets the decorated for styles in messages.
     * 
     * @param  bool  $decorated
     * 
     * @return void
     */
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = $decorated;
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
        //return (new OutputFormatterStyles('cyan'))->apply($message);
        return $this->formatting($message);
    }

    /**
     * 
     */
    protected function formatting($message)
    {
        return (new OutputFormatterStyles('cyan'))->apply($message);
    }
}