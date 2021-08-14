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

namespace Syscodes\Console\Input;

use LogicException;
use InvalidArgumentException;

/**
 * This class valides the arguments and options to set in command line.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class InputDefinition
{
    /**
     * The argument implement.
     * 
     * @var array $arguments
     */
    protected $arguments;

    /**
     * An array argument.
     * 
     * @var bool $hasArrayArgument
     */
    protected $hasArrayArgument = false;

    /**
     * An array optional argument.
     * 
     * @var bool $hasOptionalArgument
     */
    protected $hasOptionalArgument = false;

    /**
     * An array negations.
     * 
     * @var array $negations
     */
    protected $negations = [];

    /**
     * An array InputOption object.
     * 
     * @var array $options
     */
    protected $options;

    /**
     * Gets the number of InputArguments.
     * 
     * @var int $requiredCount
     */
    protected $requiredCount = 0;

    /**
     * Gets the InputOption to shortcut.
     * 
     * @var array $shortcuts
     */
    protected $shortcuts = [];

    /**
     * Constructor. Create a new InputDefinition instance.
     * 
     * @param  array  $arguments  The arguments for command
     * @param  array $options  The options for command
     * 
     * @return void
     * 
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $arguments = [], array $options = [])
    {
        $this->setArguments($arguments);
        $this->setOptions($options);
    }
    
    /*
    |----------------------------------------------------------------
    | Some Methods For The Arguments
    |---------------------------------------------------------------- 
    */

    /**
     * Sets the InputArgument objects.
     * 
     * @param  array  $arguments  The arguments array InputArgument objects
     * 
     * @return \Syscodes\Console\Input\inputArgument 
     */
    public function setArguments(array $arguments = []): InputArgument
    {
        $this->arguments = [];

        $this->addArguments($arguments);
    }

    /**
     * Adds a array of InputArgument objects.
     * 
     * @param  \Syscodes\Console\Input\InputArgument|array  $arguments  The arguments array InputArgument objects
     * 
     * @return \Syscodes\Console\Input\inputArgument
     */
    public function addArguments(array $arguments = []): InputArgument
    {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
    }

    /**
     * Adds an argument.
     * 
     * @param  \Syscodes\Console\Input\InputArgument  $argument  The arguments array InputArgument objects
     * 
     * @return \Syscodes\Console\Input\InputArgument
     */
    public function addArgument(InputArgument $argument): InputArgument
    {
        if (isset($this->arguments[$argument->getName()])) {
            throw new LogicException(sprintf('Whoops! This argument with name "%s" already exists', $argument->getName()));
        }

        if ($this->hasArrayArgument) {
            throw new LogicException(sprintf('Cannot add an argument "%s" after an array argument', $argument->getName()));
        }

        if ($argument->isRequired() && $this->hasOptionalArgument) {
            throw new LogicException(sprintf('Cannot add a argument "%s" after an optional one', $argument->getName()));
        }

        if ($argument->isArray()) {
            $this->hasArrayArgument = true;
        }

        if ($argument->isRequired()) {
            ++$this->requiredCount;
        } else {
            $this->hasOptionalArgument = true;
        }

        $this->arguments[$argument->getName()] = $argument;
    }

    /**
     * Gets an InputArgument by name or by position of an array.
     * 
     * @param  string|int  $name  The InputArgument name or position
     * 
     * @return \Syscodes\Console\Input\InputArgument
     * 
     * @throws \InvalidArgumentException
     */
    public function getArgument($name)
    {
        if ( ! $this->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist', $name));
        }

        $arguments = \is_int($name) ? array_values($this->arguments) : $this->arguments;

        return $arguments[$name];
    }

    /**
     * Checks an InputArgument objects if exists by name or by position.
     * 
     * @param  string|int  $name  The InputArgument name or position
     * 
     * @return bool  True if the InputArgument object exists, false otherwise
     */
    public function hasArgument($name): bool
    {
        $arguments = \is_int($name) ? array_values($this->arguments) : $this->arguments;

        return isset($arguments[$name]);
    }

    /**
     * Gets the array of InputArgument objects.
     * 
     * @return \Syscodes\Console\Input\InputArgument|array  An array the InputArgument objects
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Gets the number of arguments.
     * 
     * @return int  The number of InputArguments
     */
    public function getArgumentCount(): int
    {
        return $this->hasArrayArgument ? \PHP_INT_MAX : \count($this->arguments);
    }

    /**
     * Gets the number of arguments.
     * 
     * @return int  The number of required InputArguments
     */
    public function getArgumentRequiredCount(): int
    {
        return $this->requiredCount;
    }

    /*
    |----------------------------------------------------------------
    | Some Methods For The Options
    |---------------------------------------------------------------- 
    */

    /**
     * Sets the InputOption objects.
     * 
     * @param  array  $options  The options array InputOption objects
     * 
     * @return \Syscodes\Console\Input\InputOption
     */
    public function setOptions(array $options = []): InputOption
    {
        $this->options = [];

        $this->addOptions($options);
    }

    /**
     * Adds a array of InputOption objects.
     * 
     * @param  \Syscodes\Console\Input\InputOption|array  $options  The options array InputOption objects
     * 
     * @return \Syscodes\Console\Input\InputOption
     */
    public function addOptions(array $options = []): InputOption
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * Adds an option.
     * 
     * @param  \Syscodes\Console\Input\InputOption  $Option  The Options array InputOption objects
     * 
     * @return \Syscodes\Console\Input\InputOption
     * 
     * @throws \LogicException
     */
    public function addOption(InputOption $Option): InputOption
    {
        if (isset($this->options[$option->getName()])) {
            throw new LogicException(sprintf('Whoops! This option with name "%s" already exists', $option->getName()));
        }

        if (isset($this->negations[$option->getName()])) {
            throw new LogicException(sprintf('Whoops! This option with name "%s" already exists', $option->getName()));
        }

        

        $this->options[$option->getName()] = $option;
    }
}