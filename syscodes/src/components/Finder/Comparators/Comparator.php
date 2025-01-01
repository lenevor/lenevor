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

namespace Syscodes\Components\Finder\Comparators;

use InvalidArgumentException;

/**
 * Allows realize the comparison operator.
 */
class Comparator
{
    /**
     * The type of comparison operator.
     * 
     * @var string $operator
     */
    private string $operator;

    /**
     * Gets the target.
     * 
     * @var string $target
     */
    private string $target;

    /**
     * Constructor. Create a new Comparator class instance.
     * 
     * @return void
     */
    public function __construct(string $target, string $operator = '==')
    {
        if ( ! in_array($operator, ['>', '<', '>=', '<=', '==', '!='])) {
            throw new InvalidArgumentException(sprintf('Invalid operator "%s"', $operator));
        }

        $this->target   = $target;
        $this->operator = $operator;        
    }

    /**
     * Gets the type of comparison operator.
     * 
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Gets the target value.
     * 
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }
    
    /**
     * Tests against the target.
     *
     * @param  mixed  $test
     * 
     * @return bool 
     */
    public function test(mixed $test): bool
    {
        return match ($this->operator) {
            '>' => $test > $this->target,
            '>=' => $test >= $this->target,
            '<' => $test < $this->target,
            '<=' => $test <= $this->target,
            '!=' => $test != $this->target,
            default => $test == $this->target,
        };
    }
}