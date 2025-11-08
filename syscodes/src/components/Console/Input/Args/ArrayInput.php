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

/**
 * This class represents an input provided as an array.
 */
class ArrayInput extends Input
{
    /**
     * Gets the parameters input.
     * 
     * @var array $parameters
     */
    protected $parameters;

    /**
     * Constructor. Create a new ArrayInput instance.
     * 
     * @param  array  $parameters
     * @param  \Syscodes\Components\Console\Input\InputDefinition|null  $definition
     * 
     * @return void
     */
    public function __construct(array $parameters, ?InputDefinition $definition = null)
    {
        $this->parameters = $parameters;

        parent::__construct($definition);
    }

    /**
     * Processes command line arguments.
     * 
     * @return void
     */
    protected function parse(): void
    {
        foreach ($this->parameters as $key => $value) {
            if ('--' === $key) {
                return;
            }
            if (str_starts_with($key, '--')) {
                $this->addLongOption(substr($key, 2), $value);
            } elseif (str_starts_with($key, '-')) {
                $this->addShortOption(substr($key, 1), $value);
            } else {
                $this->addArgument($key, $value);
            }
        }
    }

    /**
     * Gets the first argument from unprocessed parameters (not parsed).
     * 
     * @return string|null
     */
    public function getFirstArgument(): ?string
    {
        foreach ($this->parameters as $param => $value) {
            if ($param && \is_string($param) && '-' === $param[0]) {
                continue;
            }

            return $value;
        }

        return null;
    }

    /**
     * Gets true if the unprocessed parameters (not parsed) contain a value.
     * 
     * @param  string|array  $values  The values to look for in the unprocessed parameters
     * @param  bool  $params  Just check the actual parameters, skip the ones with end of options signal (--) 
     * 
     * @return bool
     */
    public function hasParameterOption(string|array $values, bool $params = false): bool
    {
        $values = (array) $values;

        foreach ($this->parameters as $k => $v) {
            if (!\is_int($k)) {
                $v = $k;
            }

            if ($params && '--' === $v) {
                return false;
            }

            if (\in_array($v, $values)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the value of a unprocessed option (not parsed).
     * 
     * @param  string|array  $values  The values to look for in the unprocessed parameters
     * @param  mixed  $default  The default value
     * @param  bool  $params  Just check the actual parameters, skip the ones with end of options signal (--)
     * 
     * @return mixed
     */
    public function getParameterOption(string|array $values, mixed $default = false, bool $params = false): mixed
    {
        $values = (array) $values;

        foreach ($this->parameters as $k => $v) {
            if ($params && ('--' === $k || (\is_int($k) && '--' === $v))) {
                return $default;
            }

            if (\is_int($k)) {
                if (\in_array($v, $values)) {
                    return true;
                }
            } elseif (\in_array($k, $values)) {
                return $v;
            }
        }

        return $default;
    }

    /**
     * Adds a short option value.
     *
     * @throws InvalidOptionException When option given doesn't exist
     */
    private function addShortOption(string $shortcut, mixed $value): void
    {
        if ( ! $this->definition->hasShortcut($shortcut)) {
            throw new InvalidArgumentException(\sprintf('The "-%s" option does not exist.', $shortcut));
        }

        $this->addLongOption($this->definition->getOptionByShortcut($shortcut)->getName(), $value);
    }

    /**
     * Adds a long option value.
     *
     * @param  string  $name
     * @param  mixed  $value
     * 
     * @throws InvalidOptionException 
     */
    private function addLongOption(string $name, mixed $value): void
    {
        if (!$this->definition->hasOption($name)) {
            if (!$this->definition->hasNegation($name)) {
                throw new InvalidArgumentException(\sprintf('The "--%s" option does not exist.', $name));
            }

            $optionName = $this->definition->negationToName($name);
            $this->options[$optionName] = false;

            return;
        }

        $option = $this->definition->getOption($name);

        if (null === $value) {
            if ($option->isValueRequired()) {
                throw new InvalidArgumentException(\sprintf('The "--%s" option requires a value.', $name));
            }

            if (!$option->isValueOptional()) {
                $value = true;
            }
        }

        $this->options[$name] = $value;
    }

    /**
     * Adds an argument value.
     * 
     * @param  string|int  $name
     * @param  mixed  $value
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    protected function addArgument(string|int $name, mixed $value): void
    {
        if ( ! $this->definition->hasArgument($name)) {
            throw new InvalidArgumentException(\sprintf('The "%s" argument does not exist.', $name));
        }

        $this->arguments[$name] = $value;
    }

    /**
     * Magic method.
     * 
     * Returns a stringified representation of the args passed to the command.
     * 
     * @return string
     */
    public function __toString(): string
    {
        $params = [];

        foreach ($this->parameters as $param => $val) {
            if ($param && \is_string($param) && '-' === $param[0]) {
                $glue = ('-' === $param[1]) ? '=' : ' ';
                if (\is_array($val)) {
                    foreach ($val as $v) {
                        $params[] = $param.('' != $v ? $glue.$this->escapeToken($v) : '');
                    }
                } else {
                    $params[] = $param.('' != $val ? $glue.$this->escapeToken($val) : '');
                }
            } else {
                $params[] = \is_array($val) ? implode(' ', array_map($this->escapeToken(...), $val)) : $this->escapeToken($val);
            }
        }

        return implode(' ', $params);
    }
}