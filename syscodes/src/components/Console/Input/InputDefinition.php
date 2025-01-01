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

use LogicException;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Console\Input\InputOption;
use Syscodes\Components\Contracts\Console\Input\InputArgument;
use \Syscodes\Components\Contracts\Console\Input\InputDefinition as InputDefinitionInterface;

/**
 * This class valides the arguments and options to set in command line.
 */
class InputDefinition implements InputDefinitionInterface
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
     * @param  array  $definition  An array of InputArgument and InputOption instance
     * 
     * @return void
     * 
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $definition = [])
    {
        return $this->setDefinition($definition);
    }

    /**
     * Sets the definition of the input.
     * 
     * @param  array  $definition  An array of InputArgument and InputOption instance
     * 
     * @return void
     * 
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function setDefinition(array $definition): void
    {
        $arguments = [];
        $options   = [];

        foreach ($definition as $key) {
            if ($key instanceof InputOption) {
                $options[] = $key;
            } else {
                $arguments[] = $key;
            }
        }

        $this->setArguments($arguments);
        $this->setOptions($options);
    }
    
    /*
    |----------------------------------------------------------------
    | Some Methods For The Arguments
    |---------------------------------------------------------------- 
    */

    /**
     * /**
     * Sets the InputArgument objects.
     * 
     * @param  array  $arguments  The arguments array InputArgument objects
     * 
     * @return \Syscodes\Components\Console\Input\inputArgument 
     */
    public function setArguments(array $arguments = [])
    {
        $this->arguments = [];

        $this->addArguments($arguments);
    }

    /**
     * Adds a array of InputArgument objects.
     * 
     * @param  \Syscodes\Components\Console\Input\InputArgument[]  $arguments  The arguments array InputArgument objects
     * 
     * @return void
     */
    public function addArguments(?array $arguments = []): void
    {
        if (null !== $arguments) {
            foreach ($arguments as $argument) {
                $this->addArgument($argument);
            }            
        }
    }

    /**
     * Adds an argument.
     * 
     * @param  \Syscodes\Components\Console\Input\InputArgument  $argument  The arguments array InputArgument objects
     * 
     * @return void
     */
    public function addArgument(InputArgument $argument): void
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
     * @return \Syscodes\Components\Console\Input\InputArgument
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
     * @return \Syscodes\Components\Console\Input\InputArgument|array  An array the InputArgument objects
     */
    public function getArguments()
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
     * @return \Syscodes\Components\Console\Input\InputOption
     */
    public function setOptions(array $options = [])
    {
        $this->options   = [];
        $this->shortcuts = [];

        $this->addOptions($options);
    }

    /**
     * Adds a array of InputOption objects.
     * 
     * @param  \Syscodes\Components\Console\Input\InputOption|array  $options  The options array InputOption objects
     * 
     * @return \Syscodes\Components\Console\Input\InputOption
     */
    public function addOptions(array $options = [])
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * Adds an option.
     * 
     * @param  \Syscodes\Components\Console\Input\InputOption  $Option  The Options array InputOption objects
     * 
     * @return \Syscodes\Components\Console\Input\InputOption
     * 
     * @throws \LogicException
     */
    public function addOption(InputOption $option)
    {
        if (isset($this->options[$option->getName()])) {
            throw new LogicException(sprintf('Whoops! This option with name "%s" already exists', $option->getName()));
        }

        if (isset($this->negations[$option->getName()])) {
            throw new LogicException(sprintf('Whoops! This option with name "%s" already exists', $option->getName()));
        }

        if ($option->getShortcut()) {
            foreach (explode('|', $option->getShortcut()) as $shortcut) {
                if (isset($this->shortcuts[$shortcut])) {
                    throw new LogicException(sprintf('Whoops! This option with shortcut "%s" already exists', $shortcut));
                }

                $this->shortcuts[$shortcut] = $option->getName();
            }
        }

        $this->options[$option->getName()] = $option;

        if ($option->isNegatable()) {
            $negatedName = 'no-'.$option->getName();
            
            if (isset($this->options[$negatedName])) {
                throw new LogicException(sprintf('An option named "%s" already exists', $negatedName));
            }
            
            $this->negations[$negatedName] = $option->getName();
        }
    }

    /**
     * Gets an InputOption by name of an array.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return \Syscodes\Components\Console\Input\InputOption|array
     * 
     * @throws \InvalidArgumentException
     */
    public function getOption(string $name)
    {
        if ( ! $this->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "--%s" option does not exist', $name));
        }

        return $this->options[$name];
    }

    /**
     * Checks an InputOption objects if exists by name.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return bool  True if the InputOption object exists, false otherwise
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * Gets the array of InputOption objects.
     * 
     * @return \Syscodes\Components\Console\Input\InputOption|array  An array the InputOption objects
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Checks an InputOption objects if exists by shortcut.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return bool  True if the InputOption object exists, false otherwise
     */
    public function hasShortcut(string $name): bool
    {
        return isset($this->shortcuts[$name]);
    }

    /**
     * Gets an InputOption info array.
     * 
     * @param  string  $name  The Shortcut name
     * 
     * @return \Syscodes\Components\Console\Input\InputOption|array  An InputOption object
     */
    public function getOptionByShortcut(string $name)
    {
        return $this->getOption($this->shortcutToName($name));
    }

    /**
     * Gets the InputOption name given a shortcut.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return mixed  True if the InputOption shortcut exists, false otherwise
     * 
     * @throws \InvalidArgumentException
     */
    public function shortcutToName(string $name)
    {
        if ( ! isset($this->shortcuts[$name])) {
            throw new InvalidArgumentException(sprintf('The "-%s" option does not exist', $name));
        }

        return $this->shortcuts[$name];
    }

    /**
     * Checks an InputOption objects if exists by negated name.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return bool  True if the InputOption object exists, false otherwise
     */
    public function hasNegation(string $name): bool
    {
        return isset($this->negations[$name]);
    }

    /**
     * Gets the InputOption name given a negation.
     * 
     * @param  string  $name  The InputOption name
     * 
     * @return mixed  True if the InputOption negation exists, false otherwise
     * 
     * @throws \InvalidArgumentException
     */
    public function negationToName(string $name): string
    {
        if ( ! isset($this->negations[$name])) {
            throw new InvalidArgumentException(sprintf('The "-%s" option does not exist', $name));
        }

        return $this->negations[$name];
    }
    
    /**
     * Gets the synopsis.
     * 
     * @return string
     */
    public function getSynopsis(bool $short = false): string
    {
        $elements = [];
        
        if ($short && $this->getOptions()) {
            $elements[] = '[options]';
        } elseif ( ! $short) {
            foreach ($this->getOptions() as $option) {
                $value = '';
                
                if ($option->isAcceptValue()) {
                    $value = sprintf(' %s%s%s',
                        $option->isValueOptional() ? '[' : '',
                        \strtoupper($option->getName()),
                        $option->isValueOptional() ? ']' : ''
                    );
                }
                
                $shortcut = $option->getShortcut() ? sprintf('-%s|', $option->getShortcut()) : '';
                $negation = $option->isNegatable() ? sprintf('|--no-%s', $option->getName()) : '';
                $elements[] = sprintf('[%s--%s%s%s]', $shortcut, $option->getName(), $value, $negation);
            }
        }
        
        if (\count($elements) && $this->getArguments()) {
            $elements[] = '[--]';
        }
        
        $tail = '';
        
        foreach ($this->getArguments() as $argument) {
            $element = '<'.$argument->getName().'>';
            
            if ($argument->isArray()) {
                $element .= '...';
            }
            
            if ( ! $argument->isRequired()) {
                $element = '['.$element;
                $tail .= ']';
            }

            $elements[] = $element;
        }
        
        return implode(' ', $elements).$tail;
    }
}