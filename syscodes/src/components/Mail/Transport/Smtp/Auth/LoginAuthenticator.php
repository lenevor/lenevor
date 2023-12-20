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

namespace Syscodes\components\Mail\Transport\Smtp;

use Syscodes\components\Constracts\Mail\Auth\Authenticator;

/**
 * Handles LOGIN authentication of a user.
 */
class LoginAuthenticator implements Authenticator
{
    /**
     * The authenticate of the user.
     * 
     * @param  EsmpTransport  $client
     * 
     * @return void
     */
    public function authenticate(EsmtpTransport $client): void
    {
        return;
    }

    /**
     * Gets the name of the AUTH mechanism.
     * 
     * @return string
     */
    public function getAuthKeyword(): string
    {
        return 'LOGIN';
    }
}