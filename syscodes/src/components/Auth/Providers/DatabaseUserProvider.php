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

namespace Syscodes\Components\Auth\Providers;

use Syscodes\Components\Auth\GenericUser;
use Syscodes\Components\Contracts\Auth\UserProvider;
use Syscodes\Components\Contracts\Auth\Authenticatable;
use Syscodes\Components\Database\Connections\ConnectionInterface;
use Syscodes\Components\Contracts\Hashing\Hasher as HasherContract;

/**
 * 
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class DatabaseUserProvider implements UserProvider
{
    /**
     * The active database connection.
     * 
     * @var \Syscodes\Components\Database\Connections\ConnectionInterface $connection
     */
    protected $connection;

    /**
     * The hasher implementation.
     * 
     * @var \Syscodes\Components\Contracts\Hashing\Hasher $hasher
     */
    protected $hasher;

    /**
     * Get the table containing the users.
     * 
     * @var string $table
     */
    protected $table;

    /**
     * Constructor. Create a new DatabaseUserProvider class instance.
     * 
     * @param  \Syscodes\Components\Database\Connections\ConnectionInterface  $connection
     * @param  \Syscodes\Components\Contracts\Hashing\Hasher  $hasher
     * @param  string  $table
     * 
     * @return void
     */
    public function __construct(ConnectionInterface $connection, HasherContract $hasher, string $table)
    {
        $this->table      = $table;
        $this->hasher     = $hasher;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveById($identifier)
    {
        $user = $this->connection->table($this->table)->find($identifier);
        
        return $this->getGenericUser($user);
    }
    
    /**
     * {@inheritdoc}
     */
    public function retrieveByToken($identifier, string $token)
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function updateRememberToken(Authenticatable $user, string $token): void
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function retrieveByCredentials(array $credentials)
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $this->hasher->check(
            $credentials['password'], $user->getAuthPassword()
        );
    }

    /**
     * Get the generic user.
     * 
     * @param  mixed  $user
     * 
     * @return \Syscodes\Components\Auth\GenericUser
     */
    private function getGenericUser($user)
    {
        if ( ! is_null($user)) {
            return new GenericUser((array) $user);
        }
    }
}