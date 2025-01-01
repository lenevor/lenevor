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
    protected function parse(): void {}

    /**
     * Gets the first argument from unprocessed parameters (not parsed).
     * 
     * @return string|null
     */
    public function getFirstArgument()
    {
        foreach ($this->parameters as $key => $value) {
            if ($key && '-' === $key[0]) {
                continue;
            }
            
            return $value;
        }
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
        foreach ($this->parameters as $key => $value) {
            if ( ! is_int($key)) {
                $value = $key;
            }

            if ($params && '--' === $value) {
                return false;
            }

            if (in_array($value, (array) $values)) {
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
        foreach ($this->parameters as $key => $value) {
            if ($params && ('--' === $key || (is_int($key) && '--' === $value))) {
                return $default;
            }
            
            if (is_int($key)) {
                if (in_array($value, (array) $values)) {
                    return true;
                }
            } elseif (in_array($key, (array) $values)) {
                return $value;
            }
        }
        
        return $default;
    }
}