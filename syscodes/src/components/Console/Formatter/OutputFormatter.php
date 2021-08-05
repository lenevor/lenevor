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
use Syscodes\Console\Style\ColorTag;
use Syscodes\Contracts\Console\Output;
use Syscodes\Console\Formatter\OutputFormatterStyle;
use Syscodes\Contracts\Console\OutputFormatter as OutputFormatterInterface;
use Syscodes\Contracts\Console\OutputFormatterStyle as OutputFormatterStyleInterface;

/**
 * Formatter class for console output.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class OutputFormatter implements OutputFormatterInterface
{
    // CLI color template
    public const COLOR_TPL = "\033[%sm%s\033[0m";

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
    public function __construct(bool $decorated = false, array $styles = [])
    {
        $this->decorated = $decorated;

        $this->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $this->setStyle('comment', new OutputFormatterStyle('yellow'));
        $this->setStyle('info', new OutputFormatterStyle('blue'));
        $this->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));
        $this->setStyle('success', new OutputFormatterStyle('black', 'green'));

        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }
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
    public function getStyle(string $name)
    {
        if ( ! $this->hasStyle($name)) {
            throw new InvalidArgumentException(sprintf('Undefined style: "%s"', $name));
        }

        return $this->styles[\strtolower($name)] ?? [];
    }

    /**
     * Sets a new style.
     * 
     * @param  string  $name
     * @param  \Syscodes\Contracts\Console\OutputFormatterStyle  $style
     * 
     * @return void
     */
    public function setStyle(string $name, OutputFormatterStyleInterface $style): void
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
        return $this->formatInStyle($message);
    }

    /**
     * Formats a message using to a given style.
     * 
     * @param  string  $messsage
     * @param  int  $with
     * 
     * @return string
     */
    protected function formatInStyle(?string $message): string
    {
        if ( ! $message || false === strpos($message, '</')) {
            return $message;
        }

        if (strpos($message, '</') > 0) {
            return self::parseTag($message);
        }

        return sprintf(self::COLOR_TPL, $this->styles[$message], $message);
    }
    
    /**
     * Parse color tag e.g: <info>message</info>
     * 
     * @param  string  $string
     * 
     * @return string
     */
    public static function parseTag(string $string): string
    {
        return ColorTag::parse($string);
    }
}