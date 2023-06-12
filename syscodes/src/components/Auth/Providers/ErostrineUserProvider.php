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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth\Providers;

use Closure;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Contracts\Auth\UserProvider;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Hashing\Hasher as HasherContract;
use Syscodes\Components\Contracts\Auth\Authenticatable as UserContract;

/**
 * Allows the validation of credentials using the connection with the ORM Erostrine.
 */
class ErostrineUserProvider implements UserProvider
{
    /**
     * The hasher implementation.
     * 
     * @var \Syscodes\Components\Contracts\Hashing\Hasher $hasher
     */
    protected $hasher;

    /**
     * The Erostrine user model.
     * 
     * @var string $model
     */
    protected $model;

    /**
     * Constructor. Create a new DatabaseUserProvider class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     * 
     * @return void
     */
    public function __construct(HasherContract $hasher, $model)
    {
        $this->model  = $model;
        $this->hasher = $hasher;
    }

    /**
     * Retrieve a user by their unique identifier.
     * 
     * @param  mixed  $identifier
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById(mixed $identifier)
    {
        $model = $this->createModel();
        
        return $this->newModelQuery($model)
                    ->where($model->getAuthIdentifierName(), $identifier)
                    ->first();
    }
    
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     * 
     * @param  mixed  $identifier
     * @param  string  $token
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken(mixed $identifier, string $token)
    {
        $model = $this->createModel();
        
        $queryModel = $this->newModelQuery($model)->where(
            $model->getAuthIdentifierName(), $identifier
        )->first();
        
        if ( ! $queryModel) {
            return;
        }
        
        $rememberToken = $queryModel->getRememberToken();
        
        return $rememberToken && hash_equals($rememberToken, $token)
                        ? $queryModel
                        : null;
    }
    
    /**
     * Update the "remember me" token for the given user in storage.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * 
     * @return void
     */
    public function updateRememberToken(UserContract $user, string $token): void
    {
        $user->setRememberToken($token);
        
        $timestamps = $user->timestamps;
        
        $user->timestamps = false;
        
        $user->save();
        
        $user->timestamps = $timestamps;
    }
    
    /**
     * Retrieve a user by the given credentials.
     * 
     * @param  array  $credentials
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $credentials = array_filter($credentials, 
            fn ($key) => ! Str::contains($key, 'password'),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($credentials)) {
            return;
        }

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } elseif ($value instanceof Closure) {
                $value($query);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
    
    /**
     * Validate a user against the given credentials.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * 
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials): bool
    {
        return $this->hasher->check(
            $credentials['password'], $user->getAuthPassword()
        );
    }
    
    /**
     * Get a new query builder for the model instance.
     * 
     * @param  \Syscodes\Components\Database\Erostrine\Model|null  $model
     * 
     * @return \Syscodes\Components\Database\Erostrine\Builder
     */
    protected function newModelQuery($model = null)
    {
        return is_null($model)
                ? $this->createModel()->newQuery()
                : $model->newQuery();
    }
    
    /**
     * Create a new instance of the model.
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');
        
        return new $class;
    }
    
    /**
     * Gets the hasher implementation.
     * 
     * @return \Syscodes\Components\Contracts\Hashing\Hasher
     */
    public function getHasher()
    {
        return $this->hasher;
    }
    
    /**
     * Sets the hasher implementation.
     * 
     * @param  \Syscodes\Components\Contracts\Hashing\Hasher  $hasher
     * 
     * @return static
     */
    public function setHasher(HasherContract $hasher): static
    {
        $this->hasher = $hasher;
        
        return $this;
    }
    
    /**
     * Gets the name of the Erostrine user model.
     * 
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }
    
    /**
     * Sets the name of the Erostrine user model.
     * 
     * @param  string  $model
     * 
     * @return static
     */
    public function setModel($model): static
    {
        $this->model = $model;
        
        return $this;
    }
}