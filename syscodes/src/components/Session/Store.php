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

use SessionHandlerInterface;
use Syscodes\Components\Contracts\Session\Session;
use Syscodes\Components\Session\Handlers\CookieSessionHandler;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Facades\Cache;
use Syscodes\Components\Support\MessageBag;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Support\ViewErrorBag;

use function Syscodes\Components\Support\enum_value;

/**
 * Implementation of Lenevor session container.
 */
class Store implements Session
{
    use Macroable;

    /**
     * The length of session ID strings.
     *
     * @var int
     */
    protected const SESSION_ID_LENGTH = 40;

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
     * @return void
     */
    public function __construct($name, SessionHandlerInterface $handler, $id = null, $serialization = 'php')
    {
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
    public function start()
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
    protected function loadSession()
    {
        $this->items = array_replace($this->items, $this->readToHandler());

        $this->getErrorBag();
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
    protected function getErrorBag()
    {
        if ($this->serialization !== 'json' || $this->missing('errors')) {
            return;
        }
        
        $errorBag = new ViewErrorBag;
        
        foreach ($this->get('errors') as $key => $value) {
            $messageBag = new MessageBag($value['message']);
            
            $errorBag->put($key, $messageBag->setFormat($value['format']));
        }
        
        $this->put('errors', $errorBag);
    }

    /**
     * Get all of the session data.
     * 
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get a subset of the session data.
     * 
     * @param  array  $keys 
     * @return array
     */
    public function only(array $keys)
    {
        return Arr::only($this->items, $keys);
    }
    
    /**
     * Get all the session data except for a specified array of items.
     * 
     * @param  array  $keys 
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the session ID.
     * 
     * @param  string|null  $id 
     * @return void
     */
    public function setId($id)
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
        return is_string($id) && ctype_alnum($id) && strlen($id) === self::SESSION_ID_LENGTH;
    }
    
    /**
     * Get a new, random session ID.
     * 
     * @return string
     */
    protected function generateSessionId(): string
    {
        return Str::random(self::SESSION_ID_LENGTH);
    }

    /**
     * Save the session data to storage.
     * 
     * @return void
     */
    public function save()
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
    public function ageFlashData()
    {
        $this->erase($this->get('_flash.old', []));

        $this->put('_flash.old', $this->get('_flash.new', []));

        $this->put('_flash.new', []);        
    }
    
    /**
     * Prepare the serialized session data for storage.
     * 
     * @param  string  $data 
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
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->items, $key, $default);
    }

    /**
     * Push a value onto a session array.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value 
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }
    
    /**
     * Checks if a key exists.
     * 
     * @param  string|array  $key 
     * @return bool
     */
    public function exists($key)
    {
        $placeholder = new \stdClass;
        
        return (new Collection(is_array($key) ? $key : func_get_args()))->doesntContain(function ($key) use ($placeholder) {
            return $this->get($key, $placeholder) === $placeholder;
        });
    }
    
    /**
     * Determine if the given key is missing from the session data.
     * 
     * @param  string|array  $key 
     * @return bool
     */
    public function missing($key)
    {
        return ! $this->exists($key);
    }
    
    /**
     * Determine if the session contains old input.
     * 
     * @param  string|null  $key 
     * @return bool
     */
    public function hasOldInput($key = null)
    {
        $old = $this->getOldInput($key);
        
        return is_null($key) ? count($old) > 0 : ! is_null($old);
    }
    
    /**
     * Get the requested item from the flashed input array.
     * 
     * @param  string|null  $key
     * @param  mixed  $default 
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
     * @return bool
     */
    public function has($key)
    {
        return (new Collection(is_array($key) ? $key : func_get_args()))->doesntContain(function ($key) {
            return is_null($this->get($key));
        });
    }

    /**
     * Determine if any of the given keys are present and not null.
     *
     * @param  \UnitEnum|string|array  $key
     * @return bool
     */
    public function hasAny($key)
    {
        return (new Collection(is_array($key) ? $key : func_get_args()))->contains(function ($key) {
            return ! is_null($this->get($key));
        });
    }

    /**
     * Get an key from the session, if it doesn´t exists can be use
     * the default value as the second argument to the get method.
     * 
     * @param  string  $key
     * @param  mixed  $default 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, enum_value($key), $default);
    }

    /**
     * Replace the given session attributes entirely.
     * 
     * @param  array  $attributes 
     * @return void
     */
    public function replace(array $attributes)
    {
        $this->put($attributes);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     * 
     * @param  \UnitEnum|string|array  $key
     * @param  mixed  $value 
     * @return void
     */
    public function put($key, $value = null)
    {
        if ( ! is_array($key)) {
            $key = [enum_value($key) => $value];
        }

        foreach ($key as $itemKey => $itemValue) {
            Arr::set($this->items, enum_value($itemKey), $itemValue);
        }
    }

    /**
     * Remove an key from the session.
     * 
     * @param  \UnitEnum|string  $key 
     * @return mixed
     */
    public function remove($key)
    {
        return Arr::pull($this->items, enum_value($key));
    }

    /**
     * Remove one or many items from the session.
     * 
     * @param  \UnitEnum|string|array  $keys 
     * @return void
     */
    public function erase($keys)
    {
        Arr::erase($this->items, (new Collection((array) $keys))->map(fn ($key) => enum_value($key))->all());
    }

    /**
     * Flash a key / value pair to the session.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value 
     * @return void
     */
    public function flash(\BackedEnum|\UnitEnum|string $key, $value = true)
    {
        $key = enum_value($key);

        $this->put($key, $value);

        $this->push('_flash.new', $value);

        $this->removeOldFlashData([$key]);
    }

    /**
     * Flash a key / value pair to the session for immediate use.
     *
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value
     * @return void
     */
    public function now($key, $value)
    {
        $key = enum_value($key);

        $this->put($key, $value);

        $this->push('_flash.old', $key);
    }

    /**
     * Remove the given keys from the old flash data.
     * 
     * @param  array  $keys 
     * @return void
     */
    protected function removeOldFlashData(array $keys)
    {
        $this->put('_flash.old', array_diff($this->get('_flash.old', []), $keys));
    }
    
    /**
     * Flash an input array to the session.
     * 
     * @param  array  $value 
     * @return void
     */
    public function flashInput(array $value)
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
        $this->put('_token', Str::random(self::SESSION_ID_LENGTH));
    }

    /**
     * Generate a new session identifier.
     * 
     * @param  bool  $destroy 
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
    public function previousUrl()
    {
        return $this->get('_previous.url');
    }
    
    /**
     * Set the "previous" URL in the session.
     * 
     * @param  string  $url 
     * @return void
     */
    public function setPreviousUrl($url)
    {
        $this->put('_previous.url', $url);
    }

    /**
     * Get the previous route name from the session.
     *
     * @return string|null
     */
    public function previousRoute()
    {
        return $this->get('_previous.route');
    }

    /**
     * Set the "previous" route name in the session.
     *
     * @param  string|null  $route
     * @return void
     */
    public function setPreviousRoute($route)
    {
        $this->put('_previous.route', $route);
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
     * Set the underlying session handler implementation.
     *
     * @param  \SessionHandlerInterface  $handler
     * @return \SessionHandlerInterface
     */
    public function setHandler(SessionHandlerInterface $handler)
    {
        return $this->handler = $handler;
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
     * @param  \Syscodes\Components\Http\Request $request 
     * @return void
     */
    public function setRequestOnHandler($request): void
    {
        if ($this->handlerNeedsRequest()) {
            $this->handler->setRequest($request);
        }
    }
}