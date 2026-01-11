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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Auth\Access;

use Syscodes\Components\Contracts\Auth\Access\Gate;
use Syscodes\Components\Support\Str;

use function Syscodes\Components\Support\enum_value;

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
        $ability = enum_value($ability);

        if (is_string($ability) && ! str_contains($ability, '\\')) {
            return [$ability, $arguments];
        }
        
        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
        
        return [$this->normalizeGuessedAbilityName($method), $ability];
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
     * Authorize a resource action based on the incoming request.
     * 
     * @param  string|array  $model
     * @param  string|array|null  $parameter
     * @param  array  $options
     * @param  \Syscodes\Components\Http\Request|null  $request
     * 
     * @return void
     */
    public function authorizeResource($model, $parameter = null, array $options = [], $request = null): void
    {
        $model = is_array($model) ? implode(',', $model) : $model;
        
        $parameter = is_array($parameter) ? implode(',', $parameter) : $parameter;
        
        $parameter = $parameter ?: Str::snake(class_basename($model));
        
        $middleware = [];
        
        foreach ($this->resourceAbilityMap() as $method => $ability) {
            $modelName = in_array($method, $this->resourceMethodsWithoutModels()) ? $model : $parameter;
            
            $middleware["can:{$ability},{$modelName}"][] = $method;
        }
        
        foreach ($middleware as $middlewareName => $methods) {
            $this->middleware($middlewareName, $options)->only($methods);
        }
    }
    
    /**
     * Get the map of resource methods to ability names.
     * 
     * @return array
     */
    protected function resourceAbilityMap(): array
    {
        return [
            'index' => 'viewAny',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];
    }
    
    /**
     * Get the list of resource methods which do not have model parameters.
     * 
     * @return list<string>
     */
    protected function resourceMethodsWithoutModels(): array
    {
        return ['index', 'create', 'store'];
    }
}