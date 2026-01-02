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

/**
 * Get the entity for a given ability or not.
 */
trait Authorizable
{
    /**
     * Determine if the entity has a given ability.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * 
     * @return bool
     */
    public function can($ability, $arguments = []): bool
    {
        $gate = app(Gate::class)->forUser($this);
        
        return $gate->check($ability, $arguments);
    }
    
    /**
     * Determine if the entity does not have a given ability.
     * 
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * 
     * @return bool
     */
    public function cannot($ability, $arguments = []): bool
    {
        return ! $this->can($ability, $arguments);
    }
}