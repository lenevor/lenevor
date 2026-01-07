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

namespace Syscodes\Components\Routing\Middleware;

use Closure;
use Syscodes\Components\Routing\Exceptions\InvalidSignatureException;
use Syscodes\Components\Support\Arr;

/**
 * Allows ignored arguments.
 */
class ValidateSignature
{
    /**
     * The names of the parameters that should be ignored.
     *
     * @var array
     */
    protected $ignore = [
        //
    ];
    
    /**
     * The globally ignored parameters.
     * 
     * @var array
     */
    protected static $neverValidate = [];

    /**
     * Handle an incoming request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * @param  array|null  $args
     * 
     * @return \Syscodes\Components\Http\Response
     *
     * @throws \Syscodes\Components\Routing\Exceptions\InvalidSignatureException
     */
    public function handle($request, Closure $next, ...$args)
    {
        [$relative, $ignore] = $this->parseArguments($args);

        if ($request->hasValidSignatureWhileIgnoring($ignore, ! $relative)) {
            return $next($request);
        }

        throw new InvalidSignatureException;
    }

    /**
     * Parse the additional arguments given to the middleware.
     *
     * @param  array  $args
     * 
     * @return array
     */
    protected function parseArguments(array $args): array
    {
        $relative = ! empty($args) && $args[0] === 'relative';

        if ($relative) {
            array_shift($args);
        }

        $ignore = array_merge(
            property_exists($this, 'except') ? $this->except : $this->ignore,
            $args
        );

        return [$relative, array_merge($ignore, static::$neverValidate)];
    }

    /**
     * Indicate that the given parameters should be ignored during signature validation.
     *
     * @param  array|string  $parameters
     * 
     * @return void
     */
    public static function except($parameters): void
    {
        static::$neverValidate = array_values(array_unique(
            array_merge(static::$neverValidate, Arr::wrap($parameters))
        ));
    }
}