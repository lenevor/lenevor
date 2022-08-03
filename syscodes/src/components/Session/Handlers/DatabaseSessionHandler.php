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

namespace Syscodes\Components\Session\Handlers;

use SessionHandlerInterface;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Database\Connections\Connection;

/**
 * Session handler using database system for storage.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * @param  \Syscodes\Components\Database\Connections\Connection  $connection
     * @param  string  $table
     * @param  int  $minutes
     * @param  \Syscodes\Components\Contracts\Container\Container|null  $container
     * 
     * @return void
     */
    public function __construct(Connection $connection, string $table, int $minutes, Container $container = null)
    {
        $this->table      = $table;
        $this->minutes    = $minutes;
        $this->container  = $container;
        $this->connection = $connection;
    }    
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        if ( ! $this->exists) {
            $this->read($sessionId);
        }

        return $this->exists = true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $this->getQuery()->where('id', $sessionId)->delete();

        return true;
    }
    
    /**
     * {@inheritdoc}
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
     * @return self
     */
    public function setContainer($container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the existence state for the session.
     * 
     * @param  bool  $value
     * 
     * @return self
     */
    public function setExists(bool $value): self
    {
        $this->exists = $value;

        return $this;
    }
}