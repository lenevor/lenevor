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

namespace Syscodes\Components\Auth\Middleware;

use Closure;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Response;
use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Contracts\Auth\Access\Gate;

/**
 * Allows the authorize for specify models and handle incomming request.
 */
class Authorize
{
    /**
     * The gate instance.
     * 
     * @var \Syscodes\Components\Contracts\Auth\Access\Gate $gate
     */
    protected $gate;
    
    /**
     * Create a new middleware instance.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Access\Gate  $gate
     * 
     * @return void
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }
    
    /**
     * Specify the ability and models for the middleware.
     * 
     * @param  string  $ability
     * @param  string  ...$models
     * 
     * @return string
     */
    public static function using($ability, ...$models): string
    {
        return static::class.':'.implode(',', [$ability, ...$models]);
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Request): (\Syscodes\Components\Http\Response)  $next
     * @param  string  $ability
     * @param  array|null  ...$models
     * 
     * @return \Syscodes\Components\Http\Response
     * 
     * @throws \Syscodes\Components\Auth\AuthenticationException
     * @throws \Syscodes\Components\Auth\Access\AuthorizationException
     */
    public function handle($request, Closure $next, $ability, ...$models): Response
    {
        $this->gate->authorize($ability, $this->getGateArguments($request, $models));
        
        return $next($request);
    }
    
    /**
     * Get the arguments parameter for the gate.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  array|null  $models
     * 
     * @return array
     */
    protected function getGateArguments($request, $models): array
    {
        if (is_null($models)) {
            return [];
        }
        
        return collect($models)->map(function ($model) use ($request) {
            return $model instanceof Model ? $model : $this->getModel($request, $model);
        })->all();
    }
    
    /**
     * Get the model to authorize.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  string  $model
     * 
     * @return \Syscodes\Components\Database\Erostrine\Model|string
     */
    protected function getModel($request, $model)
    {
        if ($this->isClassName($model)) {
            return trim($model);
        }
        
        return $request->route($model, null) ??
            ((preg_match("/^['\"](.*)['\"]$/", trim($model), $matches)) ? $matches[1] : null);
    }
    
    /**
     * Checks if the given string looks like a fully qualified class name.
     * 
     * @param  string  $value
     * 
     * @return bool
     */
    protected function isClassName($value): bool
    {
        return Str::contains($value, '\\');
    }
}