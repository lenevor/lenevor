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

namespace Syscodes\Components\Session;

use SessionHandlerInterface;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Contracts\Session\Session;

/**
 * Implementation of Lenevor session container.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Store implements Session
{
    /**
     * The session ID.
     * 
     * @var string $id
     */
    protected $id;

    /**
     * The session items.
     * 
     * @var array $items
     */
    protected $items = [];

    /**
     * The handler session.
     * 
     * @var \SessionHandlerInterface $handler
     */
    protected $handler;

    /**
     * The session name.
     * 
     * @var string $name.
     */
    protected $name;

    /**
     * The session store's serialization.
     * 
     * @var string  $serialization
     */
    protected $serialization = 'php';

    /**
     * Session store started status.
     * 
     * @var bool $started
     */
    protected $started = false;

    /**
     * Constructor. The Store class instance.
     * 
     * @param  string  $name
     * @param  \SessionHandlerInterface  $handler
     * @param  string|null  $id
     * @param  string  $serialization
     * 
     * @return void
     */
    public function __construct(
        $name, 
        SessionHandlerInterface $handler, 
        $id = null, 
        $serialization = 'php'
    ) {
        $this->setId($id);

        $this->name          = $name;
        $this->handler       = $handler;
        $this->serialization = $serialization;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        $this->loadSession();

        if ( ! $this->has('_token')) {
            $this->regenerateToken();
        }

        return $this->started = true;
    }

    /**
     * Load the session data from the handler.
     * 
     * @return void
     */
    protected function loadSession(): void
    {
        $this->items = array_merge($this->items, $this->readToHandler());
    }

    /**
     * Read the session data from the handler.
     * 
     * @return array
     */
    protected function readToHandler()
    {
        if ($data = $this->handler->read($this->getId())) {
            if ($this->serialization === 'json') {
                $data = json_decode($this->prepareForUnserialize($data), true);
            } else {
                $data = @unserialize($this->prepareForUnserialize($data));
            }
            
            if ($data !== false && is_array($data)) {
                return $data;
            }
        }
        
        return [];
    }
    
    /**
     * Prepare the raw string data from the session for unserialization.
     * 
     * @param  string  $data
     * 
     * @return string
     */
    protected function prepareForUnserialize($data): string
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function only(array $keys): array
    {
        return Arr::only($this->items, $keys);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id): void
    {
        $this->id = $this->isValidId($id) ? $id : $this->generateSessionId();
    }
    
    /**
     * {@inheritdoc}
     */
    public function isValidId($id): bool
    {
        return is_string($id) && ctype_alnum($id) && strlen($id) === 40;
    }
    
    /**
     * Get a new, random session ID.
     * 
     * @return string
     */
    protected function generateSessionId(): string
    {
        return Str::random(40);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->ageFlashData();

        $this->handler->write($this->getId(), $this->prepareForStorage(
            $this->serialization === 'json' ? json_encode($this->items) : serialize($this->items)
        ));

        $this->started = false;
    }

    /**
     * {@inheritdoc}
     */
    public function ageFlashData(): void
    {
        $this->erase($this->get('_flash.old', []));
        $this->put('_flash.old', $this->get('_flash.new', []));
        $this->put('_flash.new', []);        
    }
    
    /**
     * Prepare the serialized session data for storage.
     * 
     * @param  string  $data
     * 
     * @return string
     */
    protected function prepareForStorage($data): string
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function pull($key, $default = null)
    {
        Arr::pull($this->items, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return ! is_null($this->get($key));
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $attributes)
    {
        $this->put($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value = null)
    {
        if ( ! is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $itemKey => $itemValue) {
            Arr::set($this->items, $itemKey, $itemValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        return Arr::pull($this->items, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function erase($keys)
    {
        Arr::erase($this->items, $keys);
    }

    /**
     * {@inheritdoc}
     */
    public function flash(string $key, $value = true)
    {
        $this->put($key, $value);
        $this->push('_flash.new', $value);
        $this->removeOldFlashData([$key]);
    }

    /**
     * Remove the given keys from the old flash data.
     * 
     * @param  array  $keys
     * 
     * @return void
     */
    protected function removeOldFlashData(array $keys)
    {
        $this->put('_flash.old', array_diff($this->get('_flash.old', []), $keys));
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->items = [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function invalidate(): bool
    {
        $this->flush();
        
        return $this->migrate(true);
    }

    /**
     * {@inheritdoc}
     */
    public function token()
    {
        return $this->get('_token');
    }

    /**
     * {@inheritdoc}
     */
    public function regenerateToken()
    {
        $this->put('_token', Str::random(40));
    }

    /**
     * {@inheritdoc}
     */
    public function regenerate($destroy = false)
    {
        return take($this->migrate($destroy), function () {
            $this->regenerateToken();
        });
    }

    /**
     * Generate a new session ID for the session.
     * 
     * @param  bool  $destroy
     * 
     * @return bool
     */
    public function migrate(bool $destroy = false): bool
    {
        if ($destroy) {
            $this->handler->destroy($this->getId());
        }

        $this->setId($this->generateSessionId());

        return $this->started = true;
    }

    /**
     * Determine if the session has been started.
     * 
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }
}