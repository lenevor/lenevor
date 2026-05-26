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

namespace Syscodes\Components\Support;

/**
 * Allows a new conditionable proxy instance.
 */
class HigherOrderWhenProxy
{
    /**
     * The target being conditionally operated on.
     *
     * @var mixed
     */
    protected $target;

    /**
     * The condition for proxying.
     *
     * @var bool
     */
    protected $condition;

    /**
     * Indicates whether the proxy has a condition.
     *
     * @var bool
     */
    protected $hasCondition = false;

    /**
     * Determine whether the condition should be negated.
     *
     * @var bool
     */
    protected $negateConditionOnCapture;

    /**
     * Constructor. Create a new proxy instance.
     *
     * @param  mixed  $target
     * 
     * @return void
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Set the condition on the proxy.
     *
     * @param  bool  $condition
     * 
     * @return static
     */
    public function condition($condition): static
    {
        [$this->condition, $this->hasCondition] = [$condition, true];

        return $this;
    }

    /**
     * Indicate that the condition should be negated.
     *
     * @return static
     */
    public function negateConditionOnCapture(): static
    {
        $this->negateConditionOnCapture = true;

        return $this;
    }

    /**
     * Magic method.
     * 
     * Proxy accessing an attribute onto the target.
     *
     * @param  string  $key
     * 
     * @return mixed
     */
    public function __get($key)
    {
        if ( ! $this->hasCondition) {
            $condition = $this->target->{$key};

            return $this->condition($this->negateConditionOnCapture ? ! $condition : $condition);
        }

        return $this->condition
            ? $this->target->{$key}
            : $this->target;
    }

    /**
     * Magic method.
     * 
     * Proxy a method call on the target.
     *
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ( ! $this->hasCondition) {
            $condition = $this->target->{$method}(...$parameters);

            return $this->condition($this->negateConditionOnCapture ? ! $condition : $condition);
        }

        return $this->condition
            ? $this->target->{$method}(...$parameters)
            : $this->target;
    }
}