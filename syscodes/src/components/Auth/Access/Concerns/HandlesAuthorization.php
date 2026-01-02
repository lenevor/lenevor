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

namespace Syscodes\Components\Auth\Access\Concerns;

use Syscodes\Components\Auth\Access\Response;

/**
 * Allows and denied the authorizations to show a message HTTP status code.
 */
trait HandlesAuthorization
{
    /**
     * Create a new access response.
     * 
     * @param  string|null  $message
     * @param  mixed  $code
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    protected function allow(?string $message = null, mixed $code = null)
    {
        return Response::allow($message, $code);
    }
    
    /**
     * Throws an unauthorized exception.
     * 
     * @param  string|null  $message
     * @param  mixed  $code
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    protected function deny(?string $message = null, mixed $code = null)
    {
        return Response::deny($message, $code);
    }
}