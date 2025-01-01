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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Session\Handlers;

use SessionHandlerInterface;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Database\Exceptions\QueryException;
use Syscodes\Components\Database\Connections\ConnectionInterface;

/**
 * Session handler using database system for storage.
 */
class DatabaseSessionHandler implements SessionHandlerInterface
{
    use InteractsWithTime;

    /**
     * The database connection instance.
     *
     * @var \Syscodes\Components\Database\Connections\Connection $connection
     */
    protected $connection;

    /**
     * The name of the session table.
     *
     * @var string $table
     */
    protected $table;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int $minutes
     */
    protected $minutes;

    /**
     * The container instance.
     *
     * @var \Syscodes\Components\Contracts\Container\Container $container
     */
    protected $container;

    /**
     * The existence state of the session.
     *
     * @var bool
     */
    protected $exists;
    
    /**
     * Constructor. The DatabaseSessionHandler class instance.
     * 
     * @param  \Syscodes\Components\Database\Connections\ConnectionInterface  $connection
     * @param  string  $table
     * @param  int  $minutes
     * @param  \Syscodes\Components\Contracts\Container\Container|null  $container
     * 
     * @return void
     */
    public function __construct(
        ConnectionInterface $connection,
        string $table,
        int $minutes,
        Container $container = null
    ) {
        $this->table      = $table;
        $this->minutes    = $minutes;
        $this->container  = $container;
        $this->connection = $connection;
    }    
    
    /**
     * Initialize session.
     * 
     * @param  string  $savePath
     * @param  string  $sessionName
     * 
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    
    /**
     * Close the session.
     * 
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }
    
    /**
     * Reads the session data from the session storage, and returns the results.
     * 
     * @param  string  $sessionId
     * 
     * @return string
     */
    public function read($sessionId): string
    {
        $session = (object) $this->getQuery()->find($sessionId);

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if ($session->payload) {
            $this->exists = true;

            return base64_decode($session->payload);
        }

        return '';
    }

    /**
     * Determine if the session is expired.
     * 
     * @param  \stdClass  $session
     * 
     * @return bool
     */
    protected function expired($session): bool
    {
        return isset($session->last_activity) &&
               $session->last_activity < Chronos::now()->subMinutes($this->minutes)->getTimestamp();
    }
    
    /**
     * Writes the session data to the session storage.
     * 
     * @param  string  $sessionId
     * @param  string  $data
     * 
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $payload = $this->getPayload($data);

        if ( ! $this->exists) {
            $this->read($sessionId);
        }

        if ($this->exists) {
            $this->sessionUpdate($sessionId, $payload);
        } else {
            $this->sessionInsert($sessionId, $payload);
        }

        return $this->exists = true;
    }

    /**
     * Allows an insert operation on the session ID.
     * 
     * @param  string  $sessionId
     * @param  array  $payload
     * 
     * @return bool|null
     */
    protected function sessionInsert($sessionId, $payload)
    {
        try {
            return $this->getQuery()->insert(Arr::set($payload, 'id', $sessionId));
        } catch (QueryException $e) {
            $this->sessionUpdate($sessionId, $payload);
        }
    }

    /**
     * Allows an update operation on the session ID.
     * 
     * @param  string  $sessionId
     * @param  array  $payload
     * 
     * @return bool|null
     */
    protected function sessionUpdate($sessionId, $payload)
    {
        return $this->getQuery()->where('id', $sessionId)->update($payload);
    }

    /**
     * Get the default payload for the session.
     * 
     * @param  string  $data
     * 
     * @return array
     */
    protected function getPayload($data): array
    {
        $payload = [
            'payload' => base64_encode($data),
            'last-activity' => $this->currentTime()
        ];
        
        if ( ! $this->container) {
            return $payload;
        }

        return take($payload, function (&$payload) {
            // Pending calling the user ID using the auth system

            if ($this->container->bound('request')) {
                $payload = array_merge($payload, [
                    'ip_address' => $this->ipAddress(),
                    'user_agent' => $this->userAgent(),
                ]);
            }
        });
    }

    /**
     * Get the IP address from the request.
     * 
     * @return string
     */
    protected function ipAddress(): string
    {
        return $this->container->make('request')->ip();
    }

    /**
     * Get the user agent from the request.
     * 
     * @return string
     */
    protected function userAgent(): string
    {
        return substr((string) $this->container->make('request')->header('User-Agent'), 0, 500);
    }
    
    /**
     * Destroys the current session.
     * 
     * @param  string  $sessionId
     * 
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $this->getQuery()->where('id', $sessionId)->delete();

        return true;
    }
    
    /**
     * Deletes expired sessions.
     * 
     * @param  int  $lifetime
     * 
     * @return int
     */
    public function gc($lifetime): int
    {
        return $this->getQuery()->where('last_activity', '<=', $this->currentTime() - $lifetime)->delete();
    }
    
    /**
     * Get a fresh query builder instance for the table.
     * 
     * @return \Syscodes\Components\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Set the application instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $container
     * 
     * @return static
     */
    public function setContainer($container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the existence state for the session.
     * 
     * @param  bool  $value
     * 
     * @return static
     */
    public function setExists(bool $value): static
    {
        $this->exists = $value;

        return $this;
    }
}