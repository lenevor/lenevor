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

namespace Syscodes\Components\Session;

use stdClass;
use SessionHandlerInterface;
use Syscodes\Components\Contracts\Session\Session;
use Syscodes\Components\Session\Handlers\CookieSessionHandler;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Facades\Cache;
use Syscodes\Components\Support\MessageBag;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\ViewErrorBag;

/**
 * Implementation of Lenevor session container.
 */
class Store implements Session
{
    /**
     * The session ID.
     * 
     * @var string
     */
    protected $id;

    /**
     * The session items.
     * 
     * @var array
     */
    protected $items = [];

    /**
     * The handler session.
     * 
     * @var \SessionHandlerInterface
     */
    protected $handler;

    /**
     * The session name.
     * 
     * @var string
     */
    protected $name;

    /**
     * The session store's serialization.
     * 
     * @var string
     */
    protected $serialization = 'php';

    /**
     * Session store started status.
     * 
     * @var bool
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
        $this->name = $name;
        $this->handler = $handler;
        $this->serialization = $serialization;
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

        $this->getErrorBag();
    }

    /**
     * Read the session data from the handler.
     * 
     * @return array
     */
    protected function readToHandler(): array
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
     * Get the ViewErrorBag when using JSON serialization for sessions.
     * 
     * @return void
     */
    protected function getErrorBag(): void
    {
        if ($this->serialization !== 'json' || ! $this->exists('errors')) {
            return;
        }
        
        $errorBag = new ViewErrorBag;
        
        foreach ($this->get('errors') as $key => $value) {
            $messageBag = new MessageBag($value['messages']);
            
            $errorBag->put($key, $messageBag->setFormat($value['format']));
        }
        
        $this->put('errors', $errorBag);
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
     * Get all the session data except for a specified array of items.
     * 
     * @param  array  $keys
     * 
     * @return array
     */
    public function except(array $keys)
    {
        return Arr::except($this->items, $keys);
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
     * @param  string|null  $id
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
    public function save(): void
    {
        $this->ageFlashData();

        $this->getErrorBagToSerialization();

        $this->handler->write($this->getId(), $this->prepareForStorage(
            $this->serialization === 'json' ? json_encode($this->items) : serialize($this->items)
        ));

        $this->started = false;
    }
    
    /**
     * Get the ViewErrorBag instance for JSON serialization.
     * 
     * @return void
     */
    protected function getErrorBagToSerialization(): void
    {
        if ($this->serialization !== 'json' || $this->missing('errors')) {
            return;
        }
        
        $errors = [];
        
        foreach ($this->items['errors']->getBags() as $key => $value) {
            $errors[$key] = [
                'format' => $value->getFormat(),
                'messages' => $value->getMessages(),
            ];
        }
        
        $this->items['errors'] = $errors;
    }

    /**
     * Age the flash data for the session.
     * 
     * @return void
     */
    public function ageFlashData(): void
    {
        foreach($this->get('_flash.old', []) as $old) {
            $this->erase($old);
        }

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
     * @return mixed
     */
    public function pull($key, $default = null): mixed
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * Push a value onto a session array.
     * 
     * @param  string  $key
     * @param  mixed  $value
     * 
     * @return void
     */
    public function push($key, $value): void
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }
    
    /**
     * Checks if a key exists.
     * 
     * @param  string|array  $key
     * 
     * @return bool
     */
    public function exists($key): bool
    {
        $placeholder = new stdClass;
        
        return ! (new Collection(is_array($key) ? $key : func_get_args()))->contains(function ($key) use ($placeholder) {
            return $this->get($key, $placeholder) === $placeholder;
        });
    }
    
    /**
     * Determine if the given key is missing from the session data.
     * 
     * @param  string|array  $key
     * 
     * @return bool
     */
    public function missing($key): bool
    {
        return ! $this->exists($key);
    }
    
    /**
     * Determine if the session contains old input.
     * 
     * @param  string|null  $key
     * 
     * @return bool
     */
    public function hasOldInput($key = null): bool
    {
        $old = $this->getOldInput($key);
        
        return is_null($key) ? count($old) > 0 : ! is_null($old);
    }
    
    /**
     * Get the requested item from the flashed input array.
     * 
     * @param  string|null  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function getOldInput($key = null, $default = null)
    {
        return Arr::get($this->get('_old_input', []), $key, $default);
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
     * Get the session cache instance.
     * 
     * @return \Syscodes\Components\Contracts\Cache\Repository
     */
    public function cache()
    {
        return Cache::store('session');
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
        return ! (new Collection(is_array($key) ? $key : func_get_args()))->contains(function ($key) {
            return is_null($this->get($key));
        });
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
    public function get($key, $default = null): mixed
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
    public function replace(array $attributes): void
    {
        $this->put($attributes);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     * 
     * @param  string|array  $key
     * @param  mixed  $value
     * 
     * @return void
     */
    public function put($key, $value = null): void
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
    public function remove($key): mixed
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
    public function erase($keys): void
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
    public function flash(string $key, $value = true): void
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
    protected function removeOldFlashData(array $keys): void
    {
        $this->put('_flash.old', array_diff($this->get('_flash.old', []), $keys));
    }
    
    /**
     * Flash an input array to the session.
     * 
     * @param  array  $value
     * 
     * @return void
     */
    public function flashInput(array $value): void
    {
        $this->flash('_old_input', $value);
    }

    /**
     * Remove all of the keys from the session.
     * 
     * @return void
     */
    public function flush(): void
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
    public function token(): string
    {
        return $this->get('_token');
    }

    /**
     * Regenerate the CSRF token value.
     * 
     * @return void
     */
    public function regenerateToken(): void
    {
        $this->put('_token', Str::random(40));
    }

    /**
     * Generate a new session identifier.
     * 
     * @param  bool  $destroy
     * 
     * @return callable
     */
    public function regenerate($destroy = false): callable
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

        return true;
    }

    /**
     * Get the previous URL from the session.
     * 
     * @return string|null
     */
    public function previousUrl(): string|null
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
    public function handlerNeedsRequest(): bool
    {
        return $this->handler instanceof CookieSessionHandler;
    }


    /**
     * Set the request on the handler instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * 
     * @return void
     */
    public function setRequestOnHandler($request): void
    {
        if ($this->handlerNeedsRequest()) {
            $this->handler->setRequest($request);
        }
    }
}