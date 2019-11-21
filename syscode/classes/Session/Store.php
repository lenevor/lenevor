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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.4.0
 */

namespace Syscode\Session;

use Closure;
use StdClass;
use Syscode\Support\Arr;
use Syscode\Support\Str;
use SessionHandlerInterface;
use Syscode\Contracts\Session\Session;

/**
 * Implementation of Lenevor session container.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Store implements Session
{
    /**
     * The session attributes.
     * 
     * @var array $attributes
     */
    protected $attributes = [];

    /**
     * The session ID.
     * 
     * @var string $id
     */
    protected $id;

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
     * @param  string                    $name
     * @param  \SessionHandlerInterface  $handler
     * @param  string|null               $id
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
        return Str::random(40);
    }

    /**
     * Save the session data to storage.
     * 
     * @return void
     */
    public function save()
    {
        return $this->handler->write($this->getId(), serialize('hola mundo'));
    }

    /**
     * Checks if a key exists.
     * 
     * @param  string|array  $key
     * 
     * @return void
     */
    public function exists($key)
    {
        
    }

    /**
     * Checks if an a key is present and not null.
     * 
     * @param  string|array  $key
     * 
     * @return void
     */
    public function has($key)
    {

    }

    /**
     * Get an key from the session.
     * 
     * @param  string  $key
     * @param  mixed   $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {

    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     * 
     * @param  string|array  $key
     * @param  mixed         $value
     * 
     * @return mixed
     */
    public function put($key, $value = null)
    {

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
        dd();
        return $this->handler->destroy();
    }

    /**
     * Remove all of the keys from the session.
     * 
     * @return void
     */
    public function flush()
    {

    }

    /**
     * Get the CSRF token value.
     * 
     * @return string
     */
    public function token()
    {

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

    }

    /**
     * Determine if the session has been started.
     * 
     * @return bool
     */
    public function isStarted()
    {

    }
}