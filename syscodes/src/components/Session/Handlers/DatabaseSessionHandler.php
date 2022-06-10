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
        return '';
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        return true;
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
    public function gc(int $lifetime): int
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
}
