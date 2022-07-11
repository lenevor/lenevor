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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Session;

use Syscodes\Components\Contracts\Session\Session;

/**
 * This class manage all the sessions of application.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * {@inheritdoc}
     */
    public function start(): bool
    {
        return $this->store->start();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->store->getId();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setId(string $id)
    {
        return $this->store->setId($id);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->store->getName();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setName(string $name)
    {
        return $this->store->setName($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function invalidate(): bool
    {
        $this->store->invalidate();

        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function migrate(bool $destroy = false): bool
    {
        $this->store->migrate($destroy);

        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        $this->store->save();
    }
    
    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return $this->store->has($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->store->get($name, $default);
    }
    
    /**
     * {@inheritdoc}
     */
    public function set(string $name, mixed $value): void
    {
        $this->store->put($name, $value);
    }
    
    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->store->all();
    }
    
    /**
     * {@inheritdoc}
     */
    public function replace(array $attributes): void
    {
        $this->store->replace($attributes);
    }
    
    /**
     * {@inheritdoc}
     */
    public function remove(string $name): mixed
    {
        return $this->store->remove($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->store->flush();
    }
    
    /**
     * {@inheritdoc}
     */
    public function isStarted(): bool
    {
        return $this->store->isStarted();
    }
}