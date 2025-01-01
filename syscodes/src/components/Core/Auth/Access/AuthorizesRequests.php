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
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Auth\Access;

use Syscodes\Components\Contracts\Auth\Access\Gate;

/**
 * Get authorize a given actions in a set of arguments.
 */
trait AuthorizesRequests
{
    /**
     * Authorize a given action against a set of arguments.
     * 
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     * 
     * @throws \Syscodes\Components\Auth\Access\Exceptions\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);
        
        return app(Gate::class)->authorize($ability, $arguments);
    }
    
    /**
     * Authorize a given action for a user.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable|mixed  $user
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     * 
     * @throws \Syscodes\Components\Auth\Access\AuthorizationException
     */
    public function authorizeForUser($user, $ability, $arguments = [])
    {
        [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);
        
        return app(Gate::class)->forUser($user)->authorize($ability, $arguments);
    }
    
    /**
     * Guesses the ability's name if it wasn't provided.
     * 
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * 
     * @return array
     */
    protected function parseAbilityAndArguments($ability, $arguments): array
    {
        if (is_string($ability) && (strpos($ability, '\\') === false)) {
            return array($ability, $arguments);
        }
        
        [,, $method] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        return array($this->normalizeGuessedAbilityName($method['function']), $ability);
    }
    
    /**
     * Normalize the ability name that has been guessed from the method name.
     * 
     * @param  string  $ability
     * 
     * @return string
     */
    protected function normalizeGuessedAbilityName($ability): string
    {
        $map = $this->resourceAbilityMap();
        
        return $map[$ability] ?? $ability;
    }
    
    /**
     * Get the map of resource methods to ability names.
     * 
     * @return array
     */
    protected function resourceAbilityMap(): array
    {
        return [
                'index'   => 'lists',
                'show'    => 'view',
                'create'  => 'create',
                'store'   => 'create',
                'edit'    => 'update',
                'update'  => 'update',
                'destroy' => 'delete',
            ];
    }
}