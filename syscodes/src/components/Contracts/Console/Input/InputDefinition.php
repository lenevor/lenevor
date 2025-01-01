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
 * InputDefinition Interface.
 */
interface InputDefinition
{
    /**
     * Sets the InputArgument objects.
     * 
     * @param  array  $arguments  The arguments array InputArgument objects
     * 
     * @return \Syscodes\Components\Console\Input\inputArgument 
     */
    public function setArguments(array $arguments = []);

    /**
     * Adds a array of InputArgument objects.
     * 
     * @param  \Syscodes\Components\Console\Input\InputArgument[]  $arguments  The arguments array InputArgument objects
     * 
     * @return \Syscodes\Components\Console\Input\inputArgument
     */
    public function addArguments(?array $arguments = []);

    /**
     * Adds an argument.
     * 
     * @param  \Syscodes\Components\Console\Input\InputArgument  $argument  The arguments array InputArgument objects
     * 
     * @return void
     */
    public function addArgument(InputArgument $argument): void;

    /**
     * Gets an InputArgument by name or by position of an array.
     * 
     * @param  string|int  $name  The InputArgument name or position
     * 
     * @return \Syscodes\Components\Console\Input\InputArgument
     * 
     * @throws \InvalidArgumentException
     */
    public function getArgument($name);

    /**
     * Checks an InputArgument objects if exists by name or by position.
     * 
     * @param  string|int  $name  The InputArgument name or position
     * 
     * @return bool  True if the InputArgument object exists, false otherwise
     */
    public function hasArgument($name): bool;

    /**
     * Gets the array of InputArgument objects.
     * 
     * @return \Syscodes\Components\Console\Input\InputArgument|array  An array the InputArgument objects
     */
    public function getArguments();

    /**
     * Gets the number of arguments.
     * 
     * @return int  The number of InputArguments
     */
    public function getArgumentCount(): int;

    /**
     * Gets the number of arguments.
     * 
     * @return int  The number of required InputArguments
     */
    public function getArgumentRequiredCount(): int;

    /**
     * Sets the InputOption objects.
     * 
     * @param  array  $options  The options array InputOption objects
     * 
     * @return \Syscodes\Components\Console\Input\InputOption
     */
    public function setOptions(array $options = []);

    /**
     * Adds a array of InputOption objects.
     * 
     * @param  \Syscodes\Components\Console\Input\InputOption|array  $options  The options array InputOption objects
     * 
     * @return \Syscodes\Components\Console\Input\InputOption
     */
    public function addOptions(array $options = []);

    /**
     * Adds an option.
     * 
     * @param  \Syscodes\Components\Console\Input\InputOption  $Option  The Options array InputOption objects
     * 
     * @return \Syscodes\Components\Console\Input\InputOption
     * 
     * @throws \LogicException
     */
    public function addOption(InputOption $Option);

    /**
     * Gets an InputOption by name of an array.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return \Syscodes\Components\Console\Input\InputOption|array
     * 
     * @throws \InvalidArgumentException
     */
    public function getOption(string $name);

    /**
     * Checks an InputOption objects if exists by name.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return bool  True if the InputOption object exists, false otherwise
     */
    public function hasOption(string $name): bool;

    /**
     * Gets the array of InputOption objects.
     * 
     * @return \Syscodes\Components\Console\Input\InputOption|array  An array the InputOption objects
     */
    public function getOptions(): array;

    /**
     * Checks an InputOption objects if exists by shortcut.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return bool  True if the InputOption object exists, false otherwise
     */
    public function hasShortcut(string $name): bool;

    /**
     * Gets an InputOption info array.
     * 
     * @param  string  $name  The Shortcut name
     * 
     * @return \Syscodes\Components\Console\Input\InputOption|array  An InputOption object
     */
    public function getOptionByShortcut(string $name);

    /**
     * Gets the InputOption name given a shortcut.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return mixed  True if the InputOption shortcut exists, false otherwise
     * 
     * @throws \InvalidArgumentException
     */
    public function shortcutToName(string $name);

    /**
     * Checks an InputOption objects if exists by negated name.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return bool  True if the InputOption object exists, false otherwise
     */
    public function hasNegation(string $name): bool;

    /**
     * Gets the InputOption name given a negation.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return mixed  True if the InputOption negation exists, false otherwise
     * 
     * @throws \InvalidArgumentException
     */
    public function negationToName(string $name): string;
}