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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation\Rules;

use Closure;
use InvalidArgumentException;
use Stringable;

/**
 * Gets the attribute must be required.
 */
class RequiredIf implements Stringable
{
    /**
     * The condition that validates the attribute.
     *
     * @var (\Closure(): bool)|bool
     */
    public $condition;

    /**
     * Contructor. Create a new required validation rule based on a condition.
     *
     * @param  (\Closure(): bool)|bool|null  $condition
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($condition)
    {
        if (is_null($condition)) {
            $condition = false;
        }

        if ($condition instanceof Closure || is_bool($condition)) {
            $this->condition = $condition;
        } else {
            throw new InvalidArgumentException('The provided condition must be a callable or boolean.');
        }
    }

    /**
     * Magic method.
     * 
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (is_callable($this->condition)) {
            return call_user_func($this->condition) ? 'required' : '';
        }

        return $this->condition ? 'required' : '';
    }
}