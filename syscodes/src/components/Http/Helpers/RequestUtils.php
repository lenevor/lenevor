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

namespace Syscodes\Components\Http\Helpers;

/**
 * Allows to use static methods referring normalized HTTP requests.
 */
class RequestUtils
{    
    /**
     * Normalizes a query string.
     * 
     * @param  string  $query
     * 
     * @return string
     */
    public static function normalizedQueryString(?string $query): string
    {
        if ('' === ($query ?? '')) {
            return '';
        }

        ksort([$query]);

        return http_build_query([$query], '', '&', \PHP_QUERY_RFC3986);
    }
}