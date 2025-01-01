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
 * InputArgument Interface.
 */
interface InputArgument
{
    public const REQUIRED = 1;
    public const OPTIONAL = 2;
    public const IS_ARRAY = 4;
    
    /**
     * Gets the argument name.
     * 
     * @return string  The argument name
     */
    public function getName(): string;
    
    /**
     * Sets the argument name.
     * 
     * @param  string  $name  The argument name
     * 
     * @return void
     */
    public function setName(string $name): void;

    /**
     * Gets the argument mode.
     * 
     * @return int
     */
    public function getMode(): int;

    /**
     * Sets the argument mode.
     * 
     * @param  int  $mode  The argument mode
     * 
     * @return void
     */
    public function setMode(int $mode): void;
    
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
     * Gets the default value.
     * 
     * @return mixed
     */
    public function getDefault(): mixed;
    
    /**
     * Sets the default value.
     * 
     * @param  mixed  $default
     * 
     * @return mixed
     * 
     * @throws \LogicException
     */
    public function setDefault(mixed $default = null): void;
    
    /**
     * Gets true if the argument is required.
     * 
     * @return bool  True if parameter mode is self::REQUIRED, false otherwise
     */
    public function isRequired(): bool;
    
    /**
     * Gets true if the argument can take multiple values.
     * 
     * @return bool  True if mode is self::IS_ARRAY, false otherwise
     */
    public function isArray(): bool;
}