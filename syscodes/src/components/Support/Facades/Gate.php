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

namespace Syscodes\Components\Support\Facades;

use Syscodes\Components\Contracts\Auth\Access\Gate as GateContract;

/**
 * Initialize the File class facade.
 * 
 * @method static \Syscodes\Components\Auth\Access\Response authorize(string $ability, array|mixed $arguments = [])
 * @method static \Syscodes\Components\Auth\Access\Response inspect(string $ability, array|mixed $arguments = [])
 * @method static \Syscodes\Components\Contracts\Auth\Access\Gate after(callable $callback)
 * @method static \Syscodes\Components\Contracts\Auth\Access\Gate before(callable $callback)
 * @method static \Syscodes\Components\Contracts\Auth\Access\Gate define(string $ability, callable|string $callback)
 * @method static \Syscodes\Components\Contracts\Auth\Access\Gate resource($name, $class, array $abilities)
 * @method static \Syscodes\Components\Contracts\Auth\Access\Gate forUser(\Syscodes\Components\Contracts\Auth\Authenticatable|mixed $user)
 * @method static \Syscodes\Components\Contracts\Auth\Access\Gate policy(string $class, string $policy)
 * @method static array abilities()
 * @method static bool allows(string $ability, array|mixed $arguments = [])
 * @method static bool any(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool check(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool denies(string $ability, array|mixed $arguments = [])
 * @method static bool has(string $ability)
 * @method static mixed getPolicyFor(object|string $class)
 * @method static mixed raw(string $ability, array|mixed $arguments = [])
 * 
 * @see \Syscodes\Components\Contracts\Auth\Access\Gate
 */
class Gate extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return GateContract::class;
    }
}
