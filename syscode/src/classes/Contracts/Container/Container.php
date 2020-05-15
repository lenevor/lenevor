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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscode\Contracts\Container;

use Closure;
use Psr\Container\ContainerInterface;

/**
 * Class responsible of registering the bindings, instances and 
 * dependencies of classes when are contained for to be executes 
 * in the services providers.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
interface Container extends ContainerInterface
{
    /**
     * Alias a type to a diferent name.
     * 
     * @param  string  $id
     * @param  string  $alias
     * 
     * @return void
     */
    public function alias($id, string $alias);

    /**
     * Register a binding with container.
     * 
     * @param  string  $id
     * @param  \Closure|string|null  $value
     * @param  bool  $singleton
     * 
     * @return void
     */
    public function bind($id, $value = null, bool $singleton = false);

    /**
     * Extender an id type in the container.
     *
     * @param  string    $id
     * @param  \Closure  $closure
     * 
     * @return void
     */
    public function extend($id, Closure $closure);

     /**
     * Marks a callable as being a factory service.
     * 
     * @param  string  $id
     * 
     * @return \Closure
     */
    public function factory($id);

    /**
     * Return and array containing all bindings.
     * 
     * @return array
     */
    public function getBindings();

     /**
     * Register an existing instance as singleton in the container.
     *
     * @param  string  $id
     * @param  mixed  $instance
     * 
     * @return mixed
     */
    public function instance($id, $instance);

    /**
     * Resolve the given type from the container.
     * 
     * @param  string  $id
     * @param  array  $parameters
     * 
     * @return object
     */
    public function make($id, array $parameters = []);

    /**
     * Remove all id traces of the specified binding.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function remove($id);

    /**
     * Set the binding with given key / value.
     * 
     * @param  string  $id
     * @param  string  $value
     * 
     * @return $this
     */
    public function set($id, string $value);

     /**
     * Register a singleton binding in the container.
     * 
     * @param  string  $id
     * @param  \Closure|string|null  $value
     * 
     * @return void
     */
    public function singleton($id, $value = null);
}