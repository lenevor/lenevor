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

namespace Syscodes\Components\Contracts\Session;

/**
 * Expected behavior of a session container used with Lenevor.
 */
interface Session
{
    /**
     * Get the name of the session.
     * 
     * @return string
     */
    public function getName(): string;
    
    /**
     * Set the name of the session.
     * 
     * @param  string  $name
     * @return void
     */
    public function setName($name): void;

    /**
     * Start the session.
     * 
     * @return bool
     */
    public function start();

    /**
     * Get all of the session data.
     * 
     * @return array
     */
    public function all();
    
    /**
     * Get a subset of the session data.
     * 
     * @param  array  $keys
     * 
     * @return array
     */
    public function only(array $keys);

    /**
     * Get the current session ID.
     * 
     * @return string
     */
    public function getId();

    /**
     * Set the session ID.
     * 
     * @param  string  $id 
     * @return void
     */
    public function setId($id);

    /**
     * Determine if this is a valid session ID.
     * 
     * @param  string  $id 
     * @return bool
     */
    public function isValidId($id): bool;

    /**
     * Save the session data to storage.
     * 
     * @return void
     */
    public function save();
    
    /**
     * Age the flash data for the session.
     * 
     * @return void
     */
    public function ageFlashData();

    /**
     * Remove one or many items from the session.
     * 
     * @param  string  $key
     * @param  mixed  $default 
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Push a value onto a session array.
     * 
     * @param  string  $key
     * @param  mixed  $value 
     * @return void
     */
    public function push($key, $value);

    /**
     * Checks if an a key is present and not null.
     * 
     * @param  string|array  $key 
     * @return bool
     */
    public function has($key);

    /**
     * Get an key from the session, if it doesn´t exists can be use
     * the default value as the second argument to the get method.
     * 
     * @param  string  $key
     * @param  mixed  $default 
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Replace the given session attributes entirely.
     * 
     * @param  array  $attributes 
     * @return void
     */
    public function replace(array $attributes);

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     * 
     * @param  string|array  $key
     * @param  mixed  $value 
     * @return void
     */
    public function put($key, $value = null);

    /**
     * Remove an key from the session.
     * 
     * @param  string  $key 
     * @return mixed
     */
    public function remove($key);
    
    /**
     * Remove one or many items from the session.
     * 
     * @param  string|array  $keys 
     * @return void
     */
    public function erase($keys);

    /**
     * Checks if a key exists.
     * 
     * @param  string|array  $key 
     * @return bool
     */
    public function exists($key);

    /**
     * Flash a key / value pair to the session.
     * 
     * @param  \UnitEnum|string  $key
     * @param  mixed  $value 
     * @return void
     */
    public function flash(\BackedEnum|\UnitEnum|string $key, $value = true);

    /**
     * Remove all of the keys from the session.
     * 
     * @return void
     */
    public function flush(): void;

    /**
     * Flush the session data and regenerate the ID.
     * 
     * @return bool
     */
    public function invalidate(): bool;

    /**
     * Get the CSRF token value.
     * 
     * @return string
     */
    public function token(): string;

    /**
     * Regenerate the CSRF token value.
     * 
     * @return void
     */
    public function regenerateToken(): void;

    /**
     * Generate a new session identifier.
     * 
     * @param  bool  $destroy
     * @return callable
     */
    public function regenerate($destroy = false): callable;

    /**
     * Generate a new session ID for the session.
     * 
     * @param  bool  $destroy 
     * @return bool
     */
    public function migrate(bool $destroy = false): bool;

    /**
     * Determine if the session has been started.
     * 
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * Get the previous URL from the session.
     * 
     * @return string|null
     */
    public function previousUrl();
    
    /**
     * Set the "previous" URL in the session.
     * 
     * @param  string  $url 
     * @return void
     */
    public function setPreviousUrl($url);
    
    /**
     * Get the session handler instance.
     * 
     * @return \SessionHandlerInterface
     */
    public function getHandler();

    /**
     * Set the request on the handler instance.
     *
     * @param  \Syscodes\Components\Http\Request  $request 
     * @return void
     */
    public function setRequestOnHandler($request);
}