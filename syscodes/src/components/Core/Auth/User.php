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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Auth;

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Auth\Concerns\Authenticatable;
use Syscodes\Components\Core\Auth\Access\Authorizable;
use Syscodes\Components\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Syscodes\Components\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * Called the User model when connection to database.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable,
        Authorizable;
}