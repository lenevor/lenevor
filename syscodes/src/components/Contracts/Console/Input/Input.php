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

use RuntimeException;
use InvalidArgumentException;
use Syscodes\Components\Console\Input\InputDefinition;

/**
 * Input Interface used by all input classes.
 */
interface Input
{
    /**
     * Allows you to link the arguments and options to define data 
     * on the command line.
     * 
     * @param  \Syscodes\Components\Console\Input\InputDefinition  $definition
     * 
     * @return void
     * 
     * @throws \RuntimeException
     */
    public function linked(InputDefinition $definition): void;

    /**
     * Gets the first argument from unprocessed parameters (not parsed).
     * 
     * @return string|null
     */
    public function getFirstArgument();

    /**
     * Gets true if the unprocessed parameters (not parsed) contain a value.
     * 
     * @param  string|array  $values  The values to look for in the unprocessed parameters
     * @param  bool  $params  Just check the actual parameters, skip the ones with end of options signal (--) 
     * 
     * @return bool
     */
    public function hasParameterOption(string|array $values, bool $params = false): bool;

    /**
     * Gets the value of a unprocessed option (not parsed).
     * 
     * @param  string|array  $values  The values to look for in the unprocessed parameters
     * @param  mixed  $default  The default value
     * @param  bool  $params  Just check the actual parameters, skip the ones with end of options signal (--)
     * 
     * @return mixed
     */
    public function getParameterOption(string|array $values, $default = false, bool $params = false): mixed;

    /**
     * Get the input interactive.
     * 
     * @return bool
     */
    public function isInteractive(): bool;

    /**
     * Sets the input interactive.
     * 
     * @param  bool  $interactive
     * 
     * @return void
     */
    public function setInteractive(bool $interactive): void;

    /**
     * Gets the argument value for given by name.
     * 
     * @param  string  $name  The argument name
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function getArgument(string $name): mixed;

    /**
     * Sets the arguments by name.
     * 
     * @param  string  $name  The argument name
     * @param  mixed  $value The value argument
     * 
     * @return void 
     */
    public function setArgument(string $name, mixed $value): void;

    /**
     * Checks an InputArgument objects if exists by name.
     * 
     * @param  string  $name  The argument name
     * 
     * @return bool  True if the InputArgument object exists, false otherwise
     */
    public function hasArgument(string $name): bool;

    /**
     * Gets the array of arguments.
     * 
     * @return array  An arguments array
     */
    public function getArguments(): array;

    /**
     * Gets the option value for given by name.
     * 
     * @param  string  $name  The option name
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function getOption(string $name): mixed;

    /**
     * Sets the options by name.
     * 
     * @param  string  $name  The option name
     * @param  mixed  $value  The value option 
     * 
     * @return void 
     */
    public function setOption(string $name, mixed $value): void;

    /**
     * Checks an InputOption objects if exists by name.
     * 
     * @param  string  $name  The option name
     * 
     * @return bool  True if the Inputoption object exists, false otherwise
     */
    public function hasOption(string $name): bool;

    /**
     * Gets the array of options.
     * 
     * @return array  An options array
     */
    public function getOptions(): array;
}