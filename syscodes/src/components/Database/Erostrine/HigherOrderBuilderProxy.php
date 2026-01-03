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

namespace Syscodes\Components\Database\Erostrine;

/**
 * Allows a new builder proxy instance.
 */
class HigherOrderBuilderProxy
{
    /**
     * The collection being operated on.
     *
     * @var \Syscodes\Components\Database\Erostrine\Builder
     */
    protected $builder;

    /**
     * The method being proxied.
     * 
     * @var mixed $method
     */
    public $method;

    /**
     * Constructor. Create a new builder proxy instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Builder  $builder 
     * @param  string  $method
     */
    public function __construct(Builder $builder, $method)
    {
        $this->builder = $builder;
        $this->method  = $method;
    }

    /**
     * Magic method. 
     * 
     * Dynamically pass method calls to the target.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->builder->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}