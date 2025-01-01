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

namespace Syscodes\Components\Console\Input;

use InvalidArgumentException;
use Syscodes\Components\Console\Input\InputDefinition;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;

/**
 * The Input base class is the main for all concrete Input classes.
 */
abstract class Input implements InputInterface
{
    /**
     * The argument implement.
     * 
     * @var array $arguments
     */
    protected $arguments = [];

    /**
     * The InputDefinition implement.
     * 
     * @var \Syscodes\Components\Console\Input\InputDefinition $definition
     */
    protected $definition;

    /**
     * The input interactive.
     * 
     * @var bool $interactive
     */
    protected $interactive = true;

    /**
     * An array InputOption object.
     * 
     * @var array $options
     */
    protected $options = [];

    /**
     * Constructor. Create a new Input instance.
     * 
     * @param  \Syscodes\Components\Console\Input\InputDefinition|null  $definition
     * 
     * @return void  
     */
    public function __construct(?InputDefinition $definition = null)
    {
        if (null === $definition) {
            $this->definition = new InputDefinition();
        } else {
            $this->linked($definition);
        }
    }

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
    public function linked(InputDefinition $definition): void
    {
        $this->arguments  = [];
        $this->options    = [];
        $this->definition = $definition;

        $this->parse();
    }

    /**
     * Processes command line arguments.
     * 
     * @return void
     */
    abstract protected function parse(): void;

    /**
     * Get the input interactive.
     * 
     * @return bool
     */
    public function isInteractive(): bool
    {
        return $this->interactive;
    }

    /**
     * Sets the input interactive.
     * 
     * @param  bool  $interactive
     * 
     * @return void
     */
    public function setInteractive(bool $interactive): void
    {
        $this->interactive = $interactive;
    }

    /*
    |----------------------------------------------------------------
    | Some Methods For The Arguments
    |---------------------------------------------------------------- 
    */
    
    /**
     * Gets the argument value for given by name.
     * 
     * @param  string  $name  The argument name
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function getArgument(string $name): mixed
    {
        if ( ! $this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist', $name));
        }

        return $this->arguments[$name] ?? $this->definition->getArgument($name)->getDefault();
    }

    /**
     * Sets the arguments by name.
     * 
     * @param  string  $name  The argument name
     * @param  mixed  $value The value argument
     * 
     * @return void 
     */
    public function setArgument(string $name, mixed $value): void
    {
        if ( ! $this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist', $name));
        }

        $this->arguments[$name] = $value;
    }

    /**
     * Checks an InputArgument objects if exists by name.
     * 
     * @param  string  $name  The argument name
     * 
     * @return bool  True if the InputArgument object exists, false otherwise
     */
    public function hasArgument(string $name): bool
    {
        return $this->definition->hasArgument($name);
    }

    /**
     * Gets the array of arguments.
     * 
     * @return array  An arguments array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /*
    |----------------------------------------------------------------
    | Some Methods For The Options
    |---------------------------------------------------------------- 
    */

    /**
     * Gets the option value for given by name.
     * 
     * @param  string  $name  The option name
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function getOption(string $name): mixed
    {
        if ($this->definition->hasNegation($name)) {
            if (null === $value = $this->getOption($this->definition->negationToName($name))) {
                return $value;
            }

            return ! $value;
        }

        if ( ! $this->definition->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist', $name));
        }

        return array_key_exists($name, $this->options) ? $this->options[$name] : $this->definition->getOption($name)->getDefault();
    }

    /**
     * Sets the options by name.
     * 
     * @param  string  $name  The option name
     * @param  mixed  $value  The value option 
     * 
     * @return void
     */
    public function setOption(string $name, mixed $value): void
    {
        if ($this->definition->hasNegation($name)) {
            $this->options[$this->definition->negationToName($name)] = ! $value;

            return;
        } elseif ( ! $this->definition->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" argument does not exist', $name));
        }

        $this->options[$name] = $value;
    }

    /**
     * Checks an InputOption objects if exists by name.
     * 
     * @param  string  $name  The option name
     * 
     * @return bool  True if the Inputoption object exists, false otherwise
     */
    public function hasOption(string $name): bool
    {
        return $this->definition->hasOption($name) || $this->definition->hasNegation($name);
    }

    /**
     * Gets the array of options.
     * 
     * @return array  An options array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
    
    /**
     * Escapes a token through escapeshellarg if it contains unsafe chars.
     * 
     * @param  string  $token
     * 
     * @return string
     */
    public function escapeToken($token): string
    {
        return preg_match('{^[\w-]+$}', $token) ? $token : escapeshellarg($token);
    }
}