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

namespace Syscodes\Components\Http\Session;

/**
 * Interface for the session.
 */
interface SessionInterface
{
    /**
     * Starts the session storage.
     * 
     * @return bool
     */
    public function start(): bool;
    
    /**
     * Returns the session ID.
     * 
     * @return string
     */
    public function getId(): string;
    
    /**
     * Sets the session ID.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function setId(string $id);
    
    /**
     * Returns the session name.
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Sets the session name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setName(string $name);
    
    /**
     * Invalidates the current session.
     * 
     * @return bool
     */
    public function invalidate(): bool;
    
    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     * 
     * @param  bool  $destroy  Whether to delete the old session or leave it to garbage collection
     * 
     * @return bool
     */
    public function migrate(bool $destroy = false): bool;
    
    /**
     * Force the session to be saved and closed.
     * 
     * @return void
     */
    public function save(): void;
    
    /**
     * Checks if an attribute is defined.
     *
     * @param  string  $nanme
     * 
     * @return bool 
     */
    public function has(string $name): bool;
    
    /**
     * Returns an attribute.
     * 
     * @param  string  $name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;
    
    /**
     * Sets an attribute.
     * 
     * @param  string  $name
     * @param  mixed  $value
     * 
     * @return void
     */
    public function set(string $name, mixed $value): void;
    
    /**
     * Returns attributes.
     *
     * @return array 
     */
    public function all(): array;
    
    /**
     * Sets attributes.
     * 
     * @param  array  $attributes
     * 
     * @return void
     */
    public function replace(array $attributes): void;
    
    /**
     * Removes an attribute.
     * 
     * @param  string  $name
     * 
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name): mixed;
    
    /**
     * Clears all attributes.
     * 
     * @return void
     */
    public function clear(): void;
    
    /**
     * Checks if the session was started.
     *
     * @return bool
     */
    public function isStarted(): bool;
}
