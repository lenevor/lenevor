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

namespace Syscodes\Components\Contracts\Console\Input;

/**
 * InputOption Interface.
 */
interface InputOption
{
    public const VALUE_NONE      = 1; // (e.g. --yell). This is the default behavior of options
    public const VALUE_REQUIRED  = 2; // (e.g. --iterations=5 or -i5)
    public const VALUE_OPTIONAL  = 4; // (e.g. --yell or --yell=loud)
    public const VALUE_IS_ARRAY  = 8; // (e.g. --dir=/foo --dir=/bar)
    public const VALUE_NEGATABLE = 16; // (e.g. --ansi or --no-ansi)

    /**
     * Gets the default value.
     * 
     * @return mixed
     */
    public function getDefault();
    
    /**
     * Sets the default value.
     * 
     * @param  mixed  $default
     * 
     * @return mixed
     * 
     * @throws \LogicException
     */
    public function setDefault($default = null): void;

    /**
     * Gets the description text.
     * 
     * @return string  The description text
     */
    public function getDescription(): string;

    /**
     * Sets the description text.
     * 
     * @param  string  $description  The description text
     * 
     * @return void
     */
    public function setDescription(string $description): void;

    /**
     * Gets the option name.
     * 
     * @return string The name
     */
    public function getName(): string;

    /**
     * Sets the option name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setName(string $name): void;

    /**
     * Gets the option shortcut.
     * 
     * @return string|array|null  The shortcut
     */
    public function getShortcut();

    /**
     * Sets the option shortcut.
     * 
     * @param  string|array|null  $shortcut
     * 
     * @return void
     */
    public function setShortcut($shortcut): void;
    
    /**
     * Gets true if the option accepts a value.
     * 
     * @return bool  True if value mode is not self::VALUE_NONE, false otherwise
     */
    public function isAcceptValue(): bool;

    /**
     * Gets true if the option requires a value.
     *
     * @return bool  True if value mode is self::VALUE_REQUIRED, false otherwise
     */
    public function isValueRequired(): bool;
    
    /**
     * Gets true if the option takes an optional value.
     *
     * @return bool  True if value mode is self::VALUE_OPTIONAL, false otherwise
     */
    public function isValueOptional(): bool;
    
    /**
     * Gets true if the option can take multiple values.
     * 
     * @return bool  True if mode is self::VALUE_IS_ARRAY, false otherwise
     */
    public function isArray(): bool;

    /**
     * Gets an option have either positive or negative value.
     * 
     * @return bool  True if mode is self::VALUE_NEGATABLE, false otherwise
     */
    public function isNegatable(): bool;
}