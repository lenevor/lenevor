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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Session;

use Syscodes\Components\Contracts\Session\Session;

/**
 * This class manage all the sessions of application.
 */
class SessionDecorator implements SessionInterface
{
    /**
     * The session store.
     * 
     * @var \Syscodes\Components\Session\Store $store
     */
    protected $store;

    /**
     * Constructor. The new Session class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Session  $session
     * 
     * @return void
     */
    public function __construct(Session $session)
    {
        $this->store = $session;
    }

    /**
     * Starts the session storage.
     * 
     * @return bool
     */
    public function start(): bool
    {
        return $this->store->start();
    }
    
    /**
     * Returns the session ID.
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->store->getId();
    }
    
    /**
     * Sets the session ID.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function setId(string $id): void
    {
        if ($this->getId() !== $id) {
            $this->store->setId($id);
        }
    }
    
    /**
     * Returns the session name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->store->getName();
    }
    
    /**
     * Sets the session name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setName(string $name): void
    {
        $this->store->setName($name);
    }
    
    /**
     * Invalidates the current session.
     * 
     * @return bool
     */
    public function invalidate(): bool
    {
        $this->store->invalidate();

        return true;
    }
    
    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     * 
     * @param  bool  $destroy  Whether to delete the old session or leave it to garbage collection
     * 
     * @return bool
     */
    public function migrate(bool $destroy = false): bool
    {
        $this->store->migrate($destroy);

        return true;
    }
    
    /**
     * Force the session to be saved and closed.
     * 
     * @return void
     */
    public function save(): void
    {
        $this->store->save();
    }
    
    /**
     * Checks if an attribute is defined.
     *
     * @param  string  $nanme
     * 
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->store->has($name);
    }
    
    /**
     * Returns an attribute.
     * 
     * @param  string  $name
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->store->get($name, $default);
    }
    
    /**
     * Sets an attribute.
     * 
     * @param  string  $name
     * @param  mixed  $value
     * 
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        $this->store->put($name, $value);
    }
    
    /**
     * Returns attributes.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->store->all();
    }
    
    /**
     * Sets attributes.
     * 
     * @param  array  $attributes
     * 
     * @return void
     */
    public function replace(array $attributes): void
    {
        $this->store->replace($attributes);
    }
    
    /**
     * Removes an attribute.
     * 
     * @param  string  $name
     * 
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name): mixed
    {
        return $this->store->remove($name);
    }
    
    /**
     * Clears all attributes.
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->store->flush();
    }
    
    /**
     * Checks if the session was started.
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->store->isStarted();
    }
}