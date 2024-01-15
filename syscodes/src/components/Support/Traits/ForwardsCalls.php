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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Traits;

use Error;
use BadMethodCallException;

/**
 * Trait ForwardsCalls.
 */
trait ForwardsCalls
{
    /**
     * Forward a method call to the given object.
     * 
     * @param  mixed  $object
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     * 
     * @throws \BadMethodCallException
     */
    protected function forwardCallTo($object, $method, $parameters)
    {
        try {
            return $object->{$method}(...$parameters);
        } catch (Error|BadMethodCallException $e) {
            static::BadMethodCallException($method);
        }
    }

    /**
     * Forward a method call to given object. Returns $this if the forwarded call.
     * 
     * @param  mixed  $object
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     * 
     * @throws \BadMethodCallException
     */
    protected function forwardObjectCallTo($object, $method, $parameters)
    {
        $result = $this->forwardCallTo($object, $method, $parameters);

        if ($result === $object) {
            return $this;
        }

        return $result;
    }

    /**
     * Throw a bad method call exception for the given method.
     * 
     * @param  string  $method
     * 
     * @return void
     * 
     * @throws \BadMethodCallException
     */
    protected static function badMethodCallException($method): void
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}