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

namespace Syscodes\Components\Core\Http;

use Syscodes\Components\Support\Chronos;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Allows get the maintenance mode bypass cookie.
 */
class MaintenanceModeBypassCookie
{
    /**
     * Create a new maintenance mode bypass cookie.
     *
     * @param  string  $key
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public static function create(string $key)
    {
        $expiresAt = Chronos::now()->addHours(12);

        return new Cookie('lenevor_maintenance', base64_encode(json_encode([
            'expires_at' => $expiresAt->getTimestamp(),
            'mac' => hash_hmac('sha256', $expiresAt->getTimestamp(), $key),
        ])), $expiresAt, config('session.path'), config('session.domain'));
    }

    /**
     * Determine if the given maintenance mode bypass cookie is valid.
     *
     * @param  string  $cookie
     * @param  string  $key
     * @return bool
     */
    public static function isValid(string $cookie, string $key): bool
    {
        $payload = json_decode(base64_decode($cookie), true);

        return is_array($payload) &&
            is_numeric($payload['expires_at'] ?? null) &&
            is_string($payload['mac'] ?? null) &&
            hash_equals(hash_hmac('sha256', $payload['expires_at'], $key), $payload['mac']) &&
            (int) $payload['expires_at'] >= Chronos::now()->getTimestamp();
    }
}