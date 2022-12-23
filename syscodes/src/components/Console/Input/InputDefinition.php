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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
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
     * {@inheritdoc}
     */
    public function setArguments(array $arguments = [])
    {
        $this->arguments = [];

        $this->addArguments($arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function addArguments(array $arguments = [])
    {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addArgument(InputArgument $argument)
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function hasArgument($name): bool
    {
        $arguments = \is_int($name) ? array_values($this->arguments) : $this->arguments;

        return isset($arguments[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgumentCount(): int
    {
        return $this->hasArrayArgument ? \PHP_INT_MAX : \count($this->arguments);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setOptions(array $options = [])
    {
        $this->options   = [];
        $this->shortcuts = [];

        $this->addOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    public function addOptions(array $options = [])
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getOption(string $name)
    {
        if ( ! $this->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The "--%s" option does not exist', $name));
        }

        return $this->options[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     * @return bool  True if the InputOption object exists, false otherwise
     */
    public function hasShortcut(string $name): bool
    {
        return isset($this->shortcuts[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionByShortcut(string $name)
    {
        return $this->getOption($this->shortcutToName($name));
    }

    /**
     * {@inheritdoc}
     */
    public function shortcutToName(string $name)
    {
        if ( ! isset($this->shortcuts[$name])) {
            throw new InvalidArgumentException(sprintf('The "-%s" option does not exist', $name));
        }

        return $this->shortcuts[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasNegation(string $name): bool
    {
        return isset($this->negations[$name]);
    }

    /**
     * {@inheritdoc}
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