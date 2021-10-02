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

use Syscodes\Console\Input\InputDefinition;

/**
 * This class represents an input provided as an array.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * @param  \Syscodes\Console\Input\InputDefinition|null  $definition
     * 
     * @return void
     */
    public function __construct(array $parameters, InputDefinition $definition = null)
    {
        $this->parameters = $parameters;

        parent::__construct($definition);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function hasParameterOption($values, bool $params = false): bool
    {
        foreach ($this->parameters as $key => $value) {
            if ( ! \is_int($key)) {
                $value = $key;
            }

            if ($params && '--' === $value) {
                return false;
            }

            if (\in_array($value, (array) $values)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterOption($values, $default = false, bool $params = false)
    {
        foreach ($this->parameters as $key => $value) {
            if ($params && ('--' === $key || (\is_int($key) && '--' === $value))) {
                return $default;
            }
            
            if (\is_int($key)) {
                if (\in_array($value, (array) $value)) {
                    return true;
                }
            } elseif (\in_array($key, (array) $value)) {
                return $value;
            }
        }
        
        return $default;
    }
}