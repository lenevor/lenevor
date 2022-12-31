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
use Syscodes\Components\Session\Handlers\CookieSessionHandler;

/**
 * Implementation of Lenevor session container.
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
     * Get the name of the session.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the session.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * Start the session.
     * 
     * @return bool
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
     * Get all of the session data.
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get a subset of the session data.
     * 
     * @param  array  $keys
     * 
     * @return array
     */
    public function only(array $keys): array
    {
        return Arr::only($this->items, $keys);
    }

    /**
     * Get the current session ID.
     * 
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the session ID.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    public function setId($id): void
    {
        $this->id = $this->isValidId($id) ? $id : $this->generateSessionId();
    }
    
    /**
     * Determine if this is a valid session ID.
     * 
     * @param  string  $id
     * 
     * @return bool
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
     * Save the session data to storage.
     * 
     * @return void
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
     * Age the flash data for the session.
     * 
     * @return void
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
     * Remove one or many items from the session.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * 
     * @return void
     */
    public function pull($key, $default = null)
    {
        Arr::pull($this->items, $key, $default);
    }

    /**
     * Push a value onto a session array.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * Checks if an a key is present and not null.
     * 
     * @param  string|array  $key
     * 
     * @return bool
     */
    public function has($key): bool
    {
        return ! is_null($this->get($key));
    }

    /**
     * Get an key from the session, if it doesn´t exists can be use
     * the default value as the second argument to the get method.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Replace the given session attributes entirely.
     * 
     * @param  array  $attributes
     * 
     * @return void
     */
    public function replace(array $attributes)
    {
        $this->put($attributes);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     * 
     * @param  string|array  $key
     * @param  mixed  $value
     * 
     * @return mixed
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
     * Remove an key from the session.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function remove($key)
    {
        return Arr::pull($this->items, $key);
    }

    /**
     * Remove one or many items from the session.
     * 
     * @param  string|array  $keys
     * 
     * @return void
     */
    public function erase($keys)
    {
        Arr::erase($this->items, $keys);
    }

    /**
     * Flash a key / value pair to the session.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return void
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
     * Remove all of the keys from the session.
     * 
     * @return void
     */
    public function flush()
    {
        $this->items = [];
    }
    
    /**
     * Flush the session data and regenerate the ID.
     * 
     * @return bool
     */
    public function invalidate(): bool
    {
        $this->flush();
        
        return $this->migrate(true);
    }

    /**
     * Get the CSRF token value.
     * 
     * @return string
     */
    public function token()
    {
        return $this->get('_token');
    }

    /**
     * Regenerate the CSRF token value.
     * 
     * @return void
     */
    public function regenerateToken()
    {
        $this->put('_token', Str::random(40));
    }

    /**
     * Generate a new session identifier.
     * 
     * @param  bool  $destroy
     * 
     * @return void
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
     * Get the previous URL from the session.
     * 
     * @return string|null
     */
    public function previousUrl()
    {
        return $this->get('_previous.url');
    }
    
    /**
     * Set the "previous" URL in the session.
     * 
     * @param  string  $url
     * 
     * @return void
     */
    public function setPreviousUrl($url): void
    {
        $this->put('_previous.url', $url);
    }

    /**
     * Get the session handler instance.
     * 
     * @return \SessionHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
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

    /**
     * Determine if the session handler needs a request.
     *
     * @return bool
     */
    public function handlerNeedsRequest()
    {
        return $this->handler instanceof CookieSessionHandler;
    }

    /**
     * Set the request on the handler instance.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @return void
     */
    public function setRequestOnHandler($request)
    {
        if ($this->handlerNeedsRequest()) {
            $this->handler->setRequest($request);
        }
    }
}