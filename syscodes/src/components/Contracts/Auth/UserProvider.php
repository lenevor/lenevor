<?php

namespace Syscodes\Components\Contracts\Auth;

interface UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     * 
     * @param  mixed  $identifier
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier);
    
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     * 
     * @param  mixed  $identifier
     * @param  string  $token
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, string $token);
    
    /**
     * Update the "remember me" token for the given user in storage.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * 
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, string $token): void;
    
    /**
     * Retrieve a user by the given credentials.
     * 
     * @param  array  $credentials
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials);
    
    /**
     * Validate a user against the given credentials.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * 
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool;
}