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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 * @since       0.4.2
 */

namespace Syscodes\Session;

use Closure;
use Syscodes\Support\Arr;
use Syscodes\Support\Str;
use SessionHandlerInterface;
use Syscodes\Contracts\Session\Session;

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
     * 
     * @return void
     */
    public function __construct($name, SessionHandlerInterface $handler, $id = null)
    {
        $this->setId($id);

        $this->name    = $name;
        $this->handler = $handler;
    }

    /**
     * Get the name of the session.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Start the session.
     * 
     * @return bool
     */
    public function start()
    {
        $this->loadSession();

        if ( ! $this->has('_token'))
        {
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
        $this->items = array_merge($this->items, $this->readToHandler());
    }

    /**
     * Read the session data from the handler.
     * 
     * @return array
     */
    protected function readToHandler()
    {
        $data = $this->handler->read($this->getId());

        return $data ? @unserialize($data) : [];
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
     * @param  string  $id
     * 
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
    public function isValidId($id)
    {
        return is_string($id) && ctype_alnum($id) && strlen($id) === 40;
    }
    
    /**
     * Get a new, random session ID.
     * 
     * @return string
     */
    protected function generateSessionId()
    {
        return sha1(uniqid('', true).Str::random(40).microtime(true));
    }

    /**
     * Save the session data to storage.
     * 
     * @return void
     */
    public function save()
    {
        $this->handler->write($this->getId(), serialize($this->items));

        $this->started = false;
    }

    /**
     * Remove one or many items from the session.
     * 
     * @param  string|array  $keys
     * 
     * @return void
     */
    public function pull($keys)
    {
        Arr::pull($this->items, $keys);
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
    public function has($key)
    {
        return ! is_null($this->get($key));
    }

    /**
     * Get an key from the session.
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
     * Put a key / value pair or array of key / value pairs in the session.
     * 
     * @param  string|array  $key
     * @param  mixed  $value
     * 
     * @return mixed
     */
    public function put($key, $value = null)
    {
        if ( ! is_array($key))
        {
            $key = [$key => $value];
        }

        foreach ($key as $itemKey => $itemValue)
        {
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
     * Flash a key / value pair to the session.
     * 
     * @param  string  $key
     * @param  mixed  $value  (true by default)
     * 
     * @return void
     */
    public function flash(string $key, $value = true)
    {
        $this->put($key, $value);
        $this->push('_flash.new', $value);
        $this->removeFlashData([$key]);
    }

    /**
     * Remove the given keys from the old flash data.
     * 
     * @param  array  $keys
     * 
     * @return void
     */
    protected function removeFlashData(array $keys)
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
    public function migrate($destroy = false)
    {
        if ($destroy)
        {
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
    public function isStarted()
    {
        return $this->started;
    }
}